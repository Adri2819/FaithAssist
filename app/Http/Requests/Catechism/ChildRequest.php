<?php

namespace App\Http\Requests\Catechism;

use App\Globals\BloodType;
use App\Globals\Sex;
use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use App\Services\UserScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChildRequest extends FormRequest
{
    use UppercasesFields;

    protected function textFields(): array
    {
        return ['name', 'paterno', 'materno', 'observations'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'church_id' => ['required', 'integer', Rule::exists('churches', 'id')->whereNull('deleted_at')],
            'community_id' => ['required', 'integer', Rule::exists('communities', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'paterno' => ['required', 'string', 'max:150'],
            'materno' => ['nullable', 'string', 'max:150'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', Rule::in(Sex::values())],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_lada' => ['nullable', 'string', 'max:8', Rule::exists('ladas', 'code')->where('status', 'active')],
            'phone' => ['nullable', 'string', 'max:30'],
            'emergency_phone_lada' => ['nullable', 'string', 'max:8', Rule::exists('ladas', 'code')->where('status', 'active')],
            'emergency_phone' => ['nullable', 'string', 'max:30'],
            'blood_type' => ['required', Rule::in(BloodType::values())],
            'observations' => ['nullable', 'string', 'max:2000'],
            'privacy_terms' => ['accepted'],
            'status' => ['required', Rule::in([
                Status::ACTIVE,
                Status::INACTIVE,
                Status::COMPLETED,
                Status::WITHDRAW,
                Status::SUSPENDED,
            ])],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $user = $this->user();

                if (! $user) {
                    return;
                }

                $scope = new UserScopeService($user);

                if ($scope->isGlobal()) {
                    return;
                }

                $churchId = $this->integer('church_id') ?: null;
                $communityId = $this->integer('community_id') ?: null;

                $churchOk = $churchId && $scope->churchIds()->contains($churchId);
                $communityOk = $communityId && $scope->communityIds()->contains($communityId);

                if ($churchOk || $communityOk) {
                    return;
                }

                    'El niño debe pertenecer a una iglesia o comunidad dentro del alcance del usuario.'
            },
        ];
    }
}
