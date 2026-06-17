<?php

namespace App\Http\Requests\Operation;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelRequest extends FormRequest
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
        $levelId = $this->route('nivel')?->id;
        $dioceseId = (int) $this->input('diocese_id');

        return [
            'diocese_id' => ['required', 'integer', Rule::exists('dioceses', 'id')->whereNull('deleted_at')],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('levels', 'name')
                    ->ignore($levelId)
                    ->where(fn ($query) => $query
                        ->where('diocese_id', $dioceseId)
                        ->whereNull('deleted_at')),
            ],
            'description' => ['nullable', 'string', 'regex:/^[\pL\s]+$/u', 'max:255'],
            'status' => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

}