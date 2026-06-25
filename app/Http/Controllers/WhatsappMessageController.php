<?php

namespace App\Http\Controllers;

use App\Models\Lada;
use App\Models\WhatsappMessage;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Throwable;

class WhatsappMessageController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', WhatsappMessage::class);

        return Inertia::render('Whatsapp/Index', [
            'countryCodes' => Lada::options(),
            'selectedCountryCode' => Lada::defaultCode(),
        ]);
    }

    public function send(Request $request, WhatsappService $whatsappService)
    {
        $this->authorize('create', WhatsappMessage::class);
        $validated = $request->validate([
            'to_country_code' => ['required', 'string', Rule::exists('ladas', 'code')->where('status', 'active')],
            'to_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9\s\-\(\)]{7,15}$/'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:20480'],
        ]);

        $normalizedPhone = Lada::normalizeLocal(
            (string) $validated['to_phone'],
            (string) $validated['to_country_code']
        );

        if (! $normalizedPhone) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo validar el número de teléfono.',
                'errors' => [
                    'to_phone' => ['El número de teléfono no es válido.'],
                ],
            ], 422);
        }

        $path = $request->file('pdf')->store('whatsapp/gafetes');

        $message = WhatsappMessage::create([
            'to_phone' => $normalizedPhone,
            'country_code' => (string) $validated['to_country_code'],
            'message_type' => 'document',
            'pdf_path' => $path,
            'status' => 'pending',
        ]);

        try {
            $result = $whatsappService->uploadAndSendPdf(
                toPhone: $normalizedPhone,
                storagePath: $path,
                filename: $request->file('pdf')->getClientOriginalName(),
                caption: $validated['caption'] ?? 'Te compartimos el gafete en PDF.'
            );

            $metaMessageId = $result['response']['messages'][0]['id'] ?? null;

            $message->update([
                'media_id' => $result['media_id'],
                'meta_message_id' => $metaMessageId,
                'status' => 'sent',
                'request_payload' => $result['payload'],
                'response_payload' => $result['response'],
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'PDF enviado correctamente por WhatsApp.',
                'data' => $message->only(['id', 'to_phone', 'status', 'created_at']),
            ]);
        } catch (Throwable $e) {
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo enviar el PDF por WhatsApp.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function history()
    {
        $this->authorize('viewAny', WhatsappMessage::class);

        return redirect()->route('whatsapp.index');
    }

    public function historyJson()
    {
        $this->authorize('viewAny', WhatsappMessage::class);

        return WhatsappMessage::latest()
            ->limit(10)
            ->get(['id', 'to_phone', 'status', 'error_message', 'created_at']);
    }
}
