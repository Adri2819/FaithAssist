<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuleRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['name', 'description'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $moduleId = $this->route('modulo')?->id;

        return [
            'name'        => ['required', 'string', 'max:150', Rule::unique('modules', 'name')->ignore($moduleId)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'key'         => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('modules', 'key')->ignore($moduleId)->whereNull('deleted_at')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'Nombre del modulo',
            'description' => 'Descripcion',
            'key'         => 'Clave del modulo',
        ];
    }
}
