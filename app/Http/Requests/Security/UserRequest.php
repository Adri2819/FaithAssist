<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => ['required', 'string', 'max:120'],
            'paterno' => ['required', 'string', 'max:120'],
            'materno' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'diocese_id' => ['nullable', 'integer', Rule::exists('dioceses', 'id')],
            'deanery_id' => [
                'nullable',
                'integer',
                Rule::exists('deaneries', 'id'),
                Rule::when(
                    filled($this->input('deanery_id')) && filled($this->input('diocese_id')),
                    Rule::exists('deaneries', 'id')->where('diocese_id', $this->input('diocese_id'))
                ),
            ],
            'church_id' => [
                'nullable',
                'integer',
                Rule::exists('churches', 'id'),
                Rule::when(
                    filled($this->input('church_id')) && filled($this->input('deanery_id')),
                    Rule::exists('churches', 'id')->where('deanery_id', $this->input('deanery_id'))
                ),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'password' => $isUpdate
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $editor = $this->user();
                if (! $editor) {
                    return;
                }

                $editorPermissionIds = $editor->getAllPermissions()->pluck('id')->toArray();

                // Validate submitted permissions are within the editor's own set
                $submittedIds = array_filter((array) $this->input('permissions', []));
                foreach ($submittedIds as $permId) {
                    if (! in_array((int) $permId, $editorPermissionIds)) {
                        $validator->errors()->add(
                            'permissions',
                            'No puedes asignar permisos que no posees.'
                        );

                        return;
                    }
                }

                // Validate the submitted role only has permissions the editor can delegate
                $roleId = $this->input('role_id');
                if ($roleId) {
                    $role = Role::with('permissions:id')->find($roleId);
                    if ($role) {
                        $hasUnallowed = $role->permissions->contains(
                            fn ($p) => ! in_array($p->id, $editorPermissionIds)
                        );

                        if ($hasUnallowed) {
                            $validator->errors()->add(
                                'role_id',
                                'No puedes asignar un rol que contiene permisos que no posees.'
                            );
                        }
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'deanery_id.exists' => 'El decanato seleccionado no pertenece a la diócesis asignada.',
            'church_id.exists' => 'La parroquia seleccionada no pertenece al decanato asignado.',
        ];
    }
}
