<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'whatsapp_country_code' => ['required', 'string', Rule::exists('ladas', 'code')->where('status', 'active')],
            'whatsapp_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9\s\-\(\)]{7,15}$/'],
        ];
    }
}
