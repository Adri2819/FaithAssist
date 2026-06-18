<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => ['required', 'string', 'max:120'],
            'paterno' => ['required', 'string', 'max:120'],
            'materno' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'municipality_ids' => ['nullable', 'array'],
            'municipality_ids.*' => ['integer', 'exists:municipalities,id'],
            'church_ids' => ['nullable', 'array'],
            'church_ids.*' => ['integer', 'exists:churches,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'password' => $isUpdate
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
        ];
    }

}
