<?php

namespace App\Http\Controllers;

use App\Mail\FormSubmissionMail;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Rules\ReCaptcha;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\Schemas\BreadcrumbSchema;
use App\Services\Seo\SeoHelper;
use App\Services\SlackNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /* protected $slackService;

    private $auth_token = 'qzR697m9xkrIh8KX2qRNjnFshuZV5v32c98KuVeqohTNncbkeG4bVRMkG3q3JTlLBs228wqUzNIfjMGs4P3GkBWRT2I1kkGGL9PCuasMmUnr9zygmOcGZGKmP5msQEHs';


    public function __construct(SlackNotificationService $slackService)
    {
        $this->slackService = $slackService;
    } */

    public function show(Form $form)
    {
        $title = $form->name;
        $description = $form->brief;
        $slider_resmi = setting('form-baslik-resmi');
        $metaTags = SeoHelper::meta($form->name, $form->brief, asset(setting('ana-sayfa-resm,')));
        $breadcrumbs = $bread = [
            ['title' => 'Anasayfa', 'url' => route('home')],
            ['title' => 'Form', 'url' => null],
        ];

        $breadcrumbSchema = SchemaBuilder::make()
            ->setSchema(new BreadcrumbSchema($breadcrumbs))
            ->render();
        $seoVars = \App\Services\Seo\SeoHelper::vars([
            'breadcrumbs' => $breadcrumbSchema,
        ]);

        return view('forms.show', array_merge([
            'form' => $form,
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'metaTags' => $metaTags,
            'slider_resmi' => $slider_resmi,
            'description' => $description,

        ], $seoVars));
    }

    public function submit(Request $request, $slug)
    {
        $data = $request->except(['_token', 'g-recaptcha-response']);
        $form = Form::where('slug', $slug)->with('fields')->firstOrFail();
        
        // Token kontrolü - form submit için de geçerli
        if ($form->requires_token && $form->access_token) {
            $providedToken = $request->query('form');
            
            if (!$providedToken || $providedToken !== $form->access_token) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Geçersiz erişim.',
                    ], 403);
                }
                abort(403);
            }
        }
        $definedFields = $form->fields->pluck('name')->toArray();
        $definedData = array_intersect_key($data, array_flip($definedFields));
        $hiddenData = array_diff_key($data, $definedData);

        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            $name = $field->name;
            $rule = [];

            if ($field->required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            $rule[] = match ($field->type) {
                'email' => 'email',
                'tel' => 'string',
                'file' => 'file',
                'select', 'checkbox', 'radio' => 'present',
                default => 'string',
            };

            if ($field->type === 'file' && ! empty($field->attributes['accepted_files'])) {
                $accepted = implode(',', array_map('trim', explode(',', $field->attributes['accepted_files'])));
                $rule[] = 'mimes:'.$accepted;
            }

            $rules[$name] = implode('|', $rule);
            $attributes[$name] = $field->label ?? Str::title(str_replace('_', ' ', $field->name));
        }

        // reCAPTCHA validation
        if ($form->recaptcha && config('services.recaptcha.secret_key')) {
            $rules['g-recaptcha-response'] = ['required', new ReCaptcha()];
            $attributes['g-recaptcha-response'] = 'reCAPTCHA';
            
            // Debug: reCAPTCHA değerini kontrol et
            $recaptchaValue = $request->input('g-recaptcha-response');
            \Log::info('FormController received reCAPTCHA', [
                'has_value' => !empty($recaptchaValue),
                'value_length' => $recaptchaValue ? strlen($recaptchaValue) : 0,
                'value_preview' => $recaptchaValue ? substr($recaptchaValue, 0, 50) . '...' : 'NULL',
            ]);
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Hidden data'yı ekle
        $formData = [];
        foreach ($hiddenData as $key => $value) {
            $formData[$key] = $value;
        }

        // Form field'larını işle
        foreach ($form->fields as $field) {
            $fieldName = $field->name;
            $value = null;

            if ($field->type === 'file' && $request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                if (is_array($file)) {
                    $paths = [];
                    foreach ($file as $f) {
                        $paths[] = $f->store('uploads/forms', 'public');
                    }
                    $value = $paths;
                } else {
                    $value = $file->store('uploads/forms', 'public');
                }
            } elseif ($field->type === 'checkbox') {
                $value = $request->input($fieldName, []);
            } else {
                $value = $request->input($fieldName);
            }

            // Boş değerleri kaydetme
            if ($value !== null && $value !== '') {
                $formData[$fieldName] = $value;
            }
        }

        // Name ve email alanlarını name_key ve email_key'e göre belirle
        $name = null;
        $email = null;

        if ($form->name_key) {
            $name = '';
            preg_match_all('/\{([^}]+)\}/', $form->name_key, $matches);
            foreach ($matches[1] as $key) {
                if (isset($formData[$key])) {
                    $name .= $formData[$key].' ';
                }
            }
            $name = trim($name);
        }

        if ($form->email_key) {
            preg_match_all('/\{([^}]+)\}/', $form->email_key, $matches);
            foreach ($matches[1] as $key) {
                if (isset($formData[$key]) && filter_var($formData[$key], FILTER_VALIDATE_EMAIL)) {
                    $email = $formData[$key];
                    break;
                }
            }
        }

        // Form submission oluştur
        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'data' => $formData,
            'name' => $name,
            'email' => $email,
        ]);

        // Form mailini kuyruğa ekle, eğer email varsa
        // if ($email) {
        //     Mail::to(config('mail.from.address'))
        //         ->queue(new FormSubmissionMail($submission));
        // }

        // Eğer $form->email mevcutsa, bir kopyasını bu adrese gönder
        // if ($form->email) {
        //     Mail::to($form->email)
        //         ->queue((new FormSubmissionMail($submission))->subject("Yeni Bir {$form->name} Başvurusu Var"));
        // }

        // Trello kartı oluştur
        if ($form->trello_id) {
            $il = null;
            $telefon = null;
            $desc = '';
            
            // Form verilerini işle
            foreach ($form->fields as $field) {
                $fieldName = $field->name;
                $fieldValue = $formData[$fieldName] ?? null;
                
                if ($fieldValue === null || $fieldValue === '') {
                    continue;
                }
                
                // İl bilgisini al
                if ($fieldName == 'il') {
                    $il = is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;
                }
                
                // Telefon bilgisini al
                if ($fieldName == 'telefon') {
                    $telefon = is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;
                }
                
                // Açıklama oluştur
                if ($field->type === 'file' || $field->type === 'image') {
                    // Dosya yolu
                    if (is_array($fieldValue)) {
                        $fileLinks = [];
                        foreach ($fieldValue as $filePath) {
                            $fileLinks[] = asset('storage/' . $filePath);
                        }
                        $desc .= "**{$field->label}:** " . implode(', ', $fileLinks) . "\n";
                    } else {
                        $desc .= "**{$field->label}:** " . asset('storage/' . $fieldValue) . "\n";
                    }
                } elseif ($field->type === 'checkbox') {
                    // Checkbox için array değerleri
                    $value = is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;
                    $desc .= "**{$field->label}:** {$value}\n";
                } else {
                    // Normal değerler
                    $value = is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;
                    $desc .= "**{$field->label}:** {$value}\n";
                }
            }
            
            // Kart başlığı oluştur
            $cardName = '';
            if ($il) {
                $cardName .= $il . ' - ';
            }
            $cardName .= now()->format('d.m.Y H:i') . ' - ';
            $cardName .= $name ?: 'İsimsiz';
            if ($telefon) {
                $cardName .= ' - ' . $telefon;
            }
            
            // Trello API'ye istek gönder
            try {
                Http::asForm()->post('https://api.trello.com/1/cards', [
                    'key' => setting('trello_key'),
                    'token' => setting('trello_token'),
                    'idList' => $form->trello_id,
                    'name' => $cardName,
                    'desc' => $desc,
                ]);
            } catch (\Exception $e) {
                // Trello hatası form gönderimini engellemez
                \Log::error('Trello kart oluşturma hatası: ' . $e->getMessage());
            }
        }

        // Slack bildirimi gönder
        if ($form->slug === 'rezervasyon-formu' || $form->slug === 'online-umre-teklifi') {
            $this->slackService->sendContactFormNotification([
                'id' => $submission->id,
                'name' => $name,
                'email' => $email,
                'fields' => $formData,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $form->success_message ?? 'Form başarıyla gönderildi.',
            ]);
        }

        return redirect()->back()->with('success', $form->success_message ?? 'Form başarıyla gönderildi.');
    }
}
