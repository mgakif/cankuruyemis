<?php

namespace App\ViewComposers;

use App\Models\Faq;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\Schemas\FaqSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FaqSchemaComposer
{
    public function compose(View $view): void
    {
        if (! Schema::hasTable('faqs')) {
            $view->with('faqSchema', '');

            return;
        }

        // Aktif FAQ'ları cache'den çek
        $faqs = Cache::rememberForever('home_faqs', function () {
            return Faq::query()
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        });

        // FAQ'lar varsa schema oluştur
        if ($faqs->isNotEmpty()) {
            $faqData = $faqs->map(function ($faq) {
                return [
                    'question' => $faq->question,
                    'answer' => strip_tags($faq->answer),
                ];
            })->toArray();

            $faqSchema = new FaqSchema($faqData);
            $schemaBuilder = SchemaBuilder::make()->setSchema($faqSchema);
            $faqSchemaHtml = $schemaBuilder->render();

            $view->with('faqSchema', $faqSchemaHtml);
        } else {
            $view->with('faqSchema', '');
        }
    }
}
