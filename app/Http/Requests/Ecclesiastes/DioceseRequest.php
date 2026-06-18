<?php

namespace App\Http\Requests\Ecclesiastes;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DioceseRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['name', 'bishop'];
    }

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

}