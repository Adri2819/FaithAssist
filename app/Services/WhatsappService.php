<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WhatsappService
{
    private ?string $token;
    private ?string $phoneNumberId;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->token = config('meta.whatsapp.token');
        $this->phoneNumberId = config('meta.whatsapp.phone_number_id');
        $this->apiVersion = (string) config('meta.whatsapp.api_version', 'v25.0');
        $this->baseUrl = (string) config('meta.whatsapp.base_url', 'https://graph.facebook.com');
    }

    public function uploadPdf(string $storagePath): string
    {
        if (!Storage::exists($storagePath)) {
            throw new RuntimeException("No existe el archivo PDF: {$storagePath}");
        }

        $absolutePath = Storage::path($storagePath);

        $response = Http::withToken($this->token)
            ->attach(
                'file',
                file_get_contents($absolutePath),
                basename($absolutePath)
            )
            ->post($this->endpoint("/{$this->phoneNumberId}/media"), [
                'messaging_product' => 'whatsapp',
                'type' => 'application/pdf',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Error al subir PDF a Meta: ' . $response->body());
        }

        return $response->json('id');
    }

    public function sendPdfDocument(
        string $toPhone,
        string $mediaId,
        string $filename = 'gafete.pdf',
        ?string $caption = null
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($toPhone),
            'type' => 'document',
            'document' => [
                'id' => $mediaId,
                'filename' => $filename,
            ],
        ];

        if ($caption) {
            $payload['document']['caption'] = $caption;
        }

        $response = Http::withToken($this->token)
            ->asJson()
            ->post($this->endpoint("/{$this->phoneNumberId}/messages"), $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Error al enviar WhatsApp: ' . $response->body());
        }

        return [
            'payload' => $payload,
            'response' => $response->json(),
        ];
    }

    public function uploadAndSendPdf(
        string $toPhone,
        string $storagePath,
        string $filename = 'gafete.pdf',
        ?string $caption = null
    ): array {
        $mediaId = $this->uploadPdf($storagePath);

        $sendResult = $this->sendPdfDocument(
            toPhone: $toPhone,
            mediaId: $mediaId,
            filename: $filename,
            caption: $caption
        );

        return [
            'media_id' => $mediaId,
            'payload' => $sendResult['payload'],
            'response' => $sendResult['response'],
        ];
    }

    private function endpoint(string $path): string
    {
        return "{$this->baseUrl}/{$this->apiVersion}{$path}";
    }

    private function normalizePhone(string $phone): string
{
    $phone = preg_replace('/\D+/', '', $phone);

    // Si el usuario captura 10 dígitos mexicanos, agregamos lada de México.
    // Ejemplo: 7224978399 -> 527224978399
    if (strlen($phone) === 10) {
        return '52' . $phone;
    }

    // Si ya viene con 52, se respeta.
    return $phone;
}
}
