<?php

namespace App\Http\Requests\Regions;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['name', 'short_name'];
    }

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

}