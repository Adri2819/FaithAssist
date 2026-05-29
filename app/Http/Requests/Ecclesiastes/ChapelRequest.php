<?php

namespace App\Http\Requests\Ecclesiastes;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChapelRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['name', 'address'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $chapelId = $this->route('capilla')?->id;

        return [
            'community_id' => ['required', 'integer', Rule::exists('communities', 'id')->whereNull('deleted_at')],
            'church_id'    => ['nullable', 'integer', Rule::exists('churches', 'id')->whereNull('deleted_at')],
            'name'         => ['required', 'string', 'max:255', Rule::unique('chapels', 'name')->ignore($chapelId)->whereNull('deleted_at')],
            'address'      => ['nullable', 'string', 'max:255'],
            'status'       => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

    public function attributes(): array
    {
        return [
            'community_id' => 'Comunidad',
            'church_id'    => 'Parroquia',
            'name'         => 'Nombre de la capilla',
            'address'      => 'Direccion',
            'status'       => 'Estatus',
        ];
    }
}
