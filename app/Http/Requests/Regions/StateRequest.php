<?php

namespace App\Http\Requests\Regions;

use App\Globals\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stateId = $this->route('estado')?->id;

        return [
            'name'       => ['required', 'string', 'max:150', Rule::unique('states', 'name')->ignore($stateId)->whereNull('deleted_at')],
            'short_name' => ['nullable', 'string', 'max:10'],
            'status'     => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'El nombre del estado es obligatorio.',
            'name.unique'     => 'Ya existe un estado con ese nombre.',
            'name.max'        => 'El nombre no puede exceder 150 caracteres.',
            'short_name.max'  => 'La abreviatura no puede exceder 10 caracteres.',
            'status.required' => 'El estatus es obligatorio.',
            'status.in'       => 'El estatus no es valido.',
        ];
    }
}