<?php

namespace App\Http\Requests\Ecclesiastes;

use App\Globals\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DioceseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dioceseId = $this->route('diocesis')?->id;

        return [
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'name'     => ['required', 'string', 'max:150', Rule::unique('dioceses', 'name')->ignore($dioceseId)->whereNull('deleted_at')],
            'bishop'   => ['nullable', 'string', 'max:150'],
            'status'   => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

    public function attributes(): array
    {
        return [
            'state_id' => 'Estado',
            'name'     => 'Nombre de la diocesis',
            'bishop'   => 'Obispo',
            'status'   => 'Estatus',
        ];
    }
}
