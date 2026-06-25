<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaWhatsAppService
{
    public function sendPasswordResetCode(string $to, string $appName, string $code): void
    {
        if ((bool) config('services.whatsapp.use_template')) {
            $this->sendTemplate($to, [
                [
                    'type' => 'text',
                    'text' => $code,
                ],
                [
                    'type' => 'text',
                    'text' => $appName,
                ],
            ]);

            return;
        }

        $this->sendText(
            $to,
            "{$appName}: tu codigo para restablecer contrasena es {$code}. Expira en 10 minutos. Si no lo solicitaste, ignora este mensaje."
        );
    }

    public function sendText(string $to, string $message): void
    {
        $this->sendPayload($to, [
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ]);
    }

    public function sendTemplate(string $to, array $bodyParameters = []): void
    {
        $templateName = (string) config('services.whatsapp.template_name');
        $language = (string) config('services.whatsapp.template_language', 'es_MX');

        if ($templateName === '') {
            throw new RuntimeException('META_WHATSAPP_TEMPLATE_NAME no esta configurado.');
        }

        $payload = [
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language,
                ],
            ],
        ];

        if ($bodyParameters !== []) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $bodyParameters,
                ],
            ];
        }

        $this->sendPayload($to, $payload);
    }

    private function sendPayload(string $to, array $payload): void
    {
        $config = $this->resolveConfig();

        Http::asJson()
            ->timeout(10)
            ->withToken($config['token'])
            ->post("{$config['base_url']}/{$config['api_version']}/{$config['phone_number_id']}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                ...$payload,
            ])
            ->throw();
    }

    private function resolveConfig(): array
    {
        $enabled = (bool) config('services.whatsapp.enabled');

        if (! $enabled) {
            throw new RuntimeException('El servicio de WhatsApp esta deshabilitado en la configuracion.');
        }

        $token = (string) config('services.whatsapp.token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $apiVersion = (string) config('services.whatsapp.api_version', 'v25.0');
        $baseUrl = rtrim((string) config('services.whatsapp.base_url', 'https://graph.facebook.com'), '/');

        if ($token === '' || $phoneNumberId === '') {
            throw new RuntimeException('Falta configurar credenciales de Meta WhatsApp.');
        }

        return [
            'token' => $token,
            'phone_number_id' => $phoneNumberId,
            'api_version' => $apiVersion,
            'base_url' => $baseUrl,
        ];
    }
}
