<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FormSubmissionController extends Controller
{
    /**
     * Form 10 için CSV dışa aktarma sayfasını göster
     */
    public function exportForm10()
    {
        return view('form-submissions.export-form10');
    }

    /**
     * Form 10 kayıtlarını CSV olarak dışa aktar
     */
    public function downloadForm10CSV()
    {
        // Form ID'si 10 olan kayıtları al
        $submissions = FormSubmission::where('form_id', 10)->get();
        
        if ($submissions->isEmpty()) {
            return back()->with('error', 'Form ID 10 için kayıt bulunamadı.');
        }

        // CSV başlıkları
        $headers = [
            'ID',
            'Form ID',
            'Ad Soyad (DB)',
            'Email (DB)',
            'Cevaplandı',
            'Oluşturulma Tarihi',
            'Güncellenme Tarihi',
            'Ad',
            'Soyad',
            'Email',
            'Telefon',
            'İl',
            'İlçe',
            'Adres',
            'Gaz Kontrol',
            'Arızalı Ürün',
            'Garanti',
            'Kabul',
            'Mesaj Notu'
        ];

        // CSV içeriği
        $csvData = [];
        $csvData[] = $this->formatCsvRow($headers);

        foreach ($submissions as $submission) {
            $data = $submission->data ?? [];
            
            // Kabul alanını string'e çevir
            $kabul = '';
            if (isset($data['kabul']) && is_array($data['kabul'])) {
                $kabul = implode(', ', $data['kabul']);
            } elseif (isset($data['kabul'])) {
                $kabul = $data['kabul'];
            }
            
            // Gaz kontrol alanı için kontrol
            $gazKontrol = $data['gaz-kontrol'] ?? '';
            
            // Arızalı ürün alanı için kontrol
            $arizaliUrun = $data['arizali-urun'] ?? '';
            
            // Garanti alanı için kontrol
            $garanti = $data['garanti'] ?? '';
            
            // Mesaj alanını kontrol et
            $mesaj = $data['mesaj'] ?? '';

            $row = [
                $submission->id,
                $submission->form_id,
                $submission->name,
                $submission->email,
                $submission->answered ? 'Evet' : 'Hayır',
                $submission->created_at,
                $submission->updated_at,
                $data['ad'] ?? '',
                $data['soyad'] ?? '',
                $data['email'] ?? '',
                $data['telefon'] ?? '',
                $data['il'] ?? '',
                $data['ilce'] ?? '',
                $data['adres'] ?? '',
                $gazKontrol,
                $arizaliUrun,
                $garanti,
                $kabul,
                $mesaj
            ];

            $csvData[] = $this->formatCsvRow($row);
        }

        $csvContent = implode("\n", $csvData);
        $filename = 'form_10_submissions_' . date('Y-m-d_His') . '.csv';

        // CSV dosyasını indir (UTF-8 BOM ile Türkçe karakter sorununu çözmek için)
        $bom = "\xEF\xBB\xBF";
        $csvContent = $bom . $csvContent;
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Form 10 kayıtlarını göster
     */
    public function showForm10()
    {
        $submissions = FormSubmission::where('form_id', 10)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('form-submissions.show-form10', compact('submissions'));
    }

    /**
     * Form 10 kaydı detayını göster
     */
    public function showForm10Detail($id)
    {
        $submission = FormSubmission::where('form_id', 10)
            ->where('id', $id)
            ->firstOrFail();

        return view('form-submissions.detail-form10', compact('submission'));
    }

    /**
     * Form 10 için istatistikler
     */
    public function form10Stats()
    {
        $total = FormSubmission::where('form_id', 10)->count();
        $answered = FormSubmission::where('form_id', 10)->where('answered', true)->count();
        $notAnswered = FormSubmission::where('form_id', 10)->where('answered', false)->count();
        
        // Son 7 günlük veriler
        $last7Days = FormSubmission::where('form_id', 10)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Ürün tipine göre dağılım
        $urunDagilim = FormSubmission::where('form_id', 10)
            ->get()
            ->groupBy(function ($item) {
                return $item->data['arizali-urun'] ?? 'Belirtilmemiş';
            })
            ->map(function ($group) {
                return $group->count();
            });

        // İllere göre dağılım
        $ilDagilim = FormSubmission::where('form_id', 10)
            ->get()
            ->groupBy(function ($item) {
                return $item->data['il'] ?? 'Belirtilmemiş';
            })
            ->map(function ($group) {
                return $group->count();
            });

        return view('form-submissions.stats-form10', compact(
            'total', 
            'answered', 
            'notAnswered', 
            'last7Days',
            'urunDagilim',
            'ilDagilim'
        ));
    }

    /**
     * CSV satırını formatla (Türkçe karakterler ve virgül için)
     */
    private function formatCsvRow(array $row)
    {
        return implode(',', array_map(function ($item) {
            // Null değerleri boş string yap
            $item = is_null($item) ? '' : $item;
            // Tırnakları çift tırnak yap ve tırnak içine al
            return '"' . str_replace('"', '""', (string) $item) . '"';
        }, $row));
    }

    /**
     * Form 10 kayıtlarını JSON olarak dışa aktar (API için)
     */
    public function exportForm10Json()
    {
        $submissions = FormSubmission::where('form_id', 10)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedData = $submissions->map(function ($submission) {
            $data = $submission->data ?? [];
            
            return [
                'id' => $submission->id,
                'form_id' => $submission->form_id,
                'name' => $submission->name,
                'email' => $submission->email,
                'answered' => $submission->answered,
                'created_at' => $submission->created_at,
                'updated_at' => $submission->updated_at,
                'form_data' => [
                    'ad' => $data['ad'] ?? '',
                    'soyad' => $data['soyad'] ?? '',
                    'email' => $data['email'] ?? '',
                    'telefon' => $data['telefon'] ?? '',
                    'il' => $data['il'] ?? '',
                    'ilce' => $data['ilce'] ?? '',
                    'adres' => $data['adres'] ?? '',
                    'gaz_kontrol' => $data['gaz-kontrol'] ?? '',
                    'arizali_urun' => $data['arizali-urun'] ?? '',
                    'garanti' => $data['garanti'] ?? '',
                    'kabul' => $data['kabul'] ?? [],
                    'mesaj' => $data['mesaj'] ?? ''
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $formattedData->count(),
            'data' => $formattedData
        ]);
    }
}
