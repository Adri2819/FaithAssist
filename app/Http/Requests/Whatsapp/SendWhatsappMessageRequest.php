<?php

namespace App\Http\Requests\Whatsapp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendWhatsappMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_country_code' => ['required', 'string', Rule::exists('ladas', 'code')->where('status', 'active')],
            'to_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9\s\-\(\)]{7,15}$/'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:20480'],
        ];
    }
}
