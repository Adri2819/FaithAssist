<?php

namespace App\Http\Requests\Regions;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MunicipalityRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['name'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $municipalityId = $this->route('municipio')?->id;

        return [
            'state_id'   => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'diocese_id' => ['nullable', 'integer', Rule::exists('dioceses', 'id')->whereNull('deleted_at')],
            'name'       => ['required', 'string', 'max:150', Rule::unique('municipalities', 'name')->ignore($municipalityId)->whereNull('deleted_at')],
            'status'     => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

}
