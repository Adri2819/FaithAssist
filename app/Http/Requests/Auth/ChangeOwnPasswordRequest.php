<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOwnPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:web'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-ZÁÉÍÓÚÑ]/u',
                'regex:/[a-záéíóúñ]/u',
                'regex:/[0-9]/',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Ingresa tu contrasena actual.',
            'current_password.current_password' => 'La contrasena actual no es correcta.',
            'password.required' => 'Ingresa una nueva contrasena.',
            'password.min' => 'La nueva contrasena debe tener al menos 8 caracteres.',
            'password.regex' => 'La nueva contrasena debe incluir mayuscula, minuscula y numero.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'contrasena actual',
            'password' => 'nueva contrasena',
            'password_confirmation' => 'confirmacion de contrasena',
        ];
    }
}
