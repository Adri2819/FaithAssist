<?php

namespace App\Http\Requests\Ecclesiastes;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeaneryRequest extends FormRequest
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
        $deaneryId = $this->route('decanato')?->id;

        return [
            'diocese_id' => ['required', 'integer', Rule::exists('dioceses', 'id')->whereNull('deleted_at')],
            'name'       => ['required', 'string', 'max:150', Rule::unique('deaneries', 'name')->ignore($deaneryId)->whereNull('deleted_at')],
            'status'     => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

}