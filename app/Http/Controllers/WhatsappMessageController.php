<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class WhatsappMessageController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', WhatsappMessage::class);

        return Inertia::render('Whatsapp/Index');
    }

    public function send(Request $request, WhatsappService $whatsappService)
    {
        $this->authorize('create', WhatsappMessage::class);
        $validated = $request->validate([
            'to_phone' => ['required', 'string', 'regex:/^\d{10,15}$/'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:20480'],
        ]);

        $path = $request->file('pdf')->store('whatsapp/gafetes');

        $message = WhatsappMessage::create([
            'to_phone' => $validated['to_phone'],
            'message_type' => 'document',
            'pdf_path' => $path,
            'status' => 'pending',
        ]);

        try {
            $result = $whatsappService->uploadAndSendPdf(
                toPhone: $validated['to_phone'],
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
                'data' => $message,
            ]);
        } catch (Throwable $e) {
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo enviar el PDF por WhatsApp.',
                'error' => $e->getMessage(),
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
            ->get();
    }
}
