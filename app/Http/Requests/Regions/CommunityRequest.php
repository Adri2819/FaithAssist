<?php

namespace App\Http\Requests\Regions;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunityRequest extends FormRequest
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
        $communityId = $this->route('comunidad')?->id;

        return [
            'municipality_id' => ['required', 'integer', Rule::exists('municipalities', 'id')->whereNull('deleted_at')],
            'name'            => ['required', 'string', 'max:150', Rule::unique('communities', 'name')->ignore($communityId)->whereNull('deleted_at')],
            'status'          => ['required', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];
    }

}
