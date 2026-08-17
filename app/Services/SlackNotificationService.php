<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SlackNotificationService
{
    private string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.slack.notifications.webhook_url');

        if (empty($this->webhookUrl)) {
            throw new RuntimeException('Slack webhook URL is not configured. Please set SLACK_WEBHOOK_URL in your .env file.');
        }
    }

    public function sendContactFormNotification(array $data): void
    {
        foreach ($data['fields'] ?? [] as $key => $value) {
            // Checkbox gibi çoklu seçimleri stringe çevir
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            // Alanı oluştur ve boş değilse ekle
            $field = $this->field(ucwords(str_replace('_', ' ', $key)), $value);
            if ($field) {
                $fields[] = $field;
            }
        }
        $payload = [
            'text' => '📨 Yeni Online Umre Teklifi Geldi!',
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => ['type' => 'mrkdwn', 'text' => "*Gönderen:* {$data['name']}\n*E-posta:* {$data['email']}"],
                ],
                [
                    'type' => 'section',
                    'fields' => $fields,
                ],
                ['type' => 'divider'],
                [
                    'type' => 'actions',
                    'elements' => [
                        [
                            'type' => 'button',
                            'text' => ['type' => 'plain_text', 'text' => 'E-posta Gönder'],
                            'url' => $this->mailtoUrl($data),
                            'style' => 'primary',
                        ],
                        $this->viewDetailsButton($data),
                        $this->modalOlustur($data),
                    ],
                ],
            ],
        ];

        Http::post($this->webhookUrl, $payload);
    }

    private function modalOlustur(array $data): array
    {
        return [
            'type' => 'button',
            'text' => ['type' => 'plain_text', 'text' => '✉️ Cevapla'],
            'action_id' => 'open_reply_modal',
            'value' => json_encode([
                'id' => $data['id'] ?? null,
                'email' => $data['email'] ?? null,
                'name' => $data['name'] ?? null,
            ]),
        ];
    }

    private function mailtoUrl(array $data): string
    {
        $subject = 'Re: Online Umre Teklifi';
        $body = "Merhaba {$data['name']},\n\n";

        return "mailto:{$data['email']}?subject=".urlencode($subject).'&body='.urlencode($body);
    }

    private function viewDetailsButton(array $data): array
    {
        if (! isset($data['id'])) {
            return [
                'type' => 'button',
                'text' => ['type' => 'plain_text', 'text' => 'Detay Yok'],
                'url' => url('/admin/form-submissions'),
                'style' => 'danger',
            ];
        }

        return [
            'type' => 'button',
            'text' => ['type' => 'plain_text', 'text' => 'Detayları Görüntüle'],
            'url' => url("/admin/form-submissions/{$data['id']}"),
        ];
    }

    private function field(string $title, ?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        return [
            'type' => 'mrkdwn',
            'text' => "*{$title}:*\n{$value}",
        ];
    }
}
