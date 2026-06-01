<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['description'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permiso')?->id;

        return [
            'name'       => [
                'required',
                'string',
                'max:125',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($permissionId),
            ],
            'description' => ['required', 'string', 'max:255'],
            'module_key'  => ['required', 'string', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'Nombre del permiso',
            'description' => 'Descripcion',
            'module_key'  => 'Modulo',
        ];
    }
}
