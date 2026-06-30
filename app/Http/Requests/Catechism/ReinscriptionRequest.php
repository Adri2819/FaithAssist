<?php

namespace App\Http\Requests\Catechism;

use App\Globals\Status;
use App\Models\Catechism\Child;
use App\Models\Catechism\ChildReinscription;
use App\Models\Operation\Level;
use App\Services\CatechismPeriodMovementService;
use App\Services\UserScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReinscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reinscripciones.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'child_id' => ['required', 'integer', Rule::exists('children', 'id')->whereNull('deleted_at')],
            'to_level_ids' => ['required', 'array', 'min:1'],
            'to_level_ids.*' => ['integer', Rule::exists('levels', 'id')->whereNull('deleted_at')],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $child = Child::query()
                ->with(['church.deanery:id,diocese_id', 'activeLevelAssignments.level:id,diocese_id,name'])
                ->find($this->integer('child_id'));

            if (! $child) {
                return;
            }

            $scope = new UserScopeService($this->user());
            if (! $scope->isGlobal() && ! $scope->churchIds()->contains($child->church_id) && ! $scope->communityIds()->contains($child->community_id)) {
                $validator->errors()->add('child_id', 'El niño seleccionado está fuera del alcance del usuario.');

                return;
            }

            $dioceseId = $child->church?->deanery?->diocese_id;
            $currentLevelIds = $child->activeLevelAssignments->pluck('level_id')->unique()->values();
            $toLevelIds = collect($this->input('to_level_ids', []))->filter()->unique()->values();

            if ($currentLevelIds->isEmpty()) {
                $validator->errors()->add('child_id', 'El niño seleccionado no tiene niveles activos para reinscribir.');

                return;
            }

            $validDestinationCount = Level::query()
                ->whereIn('id', $toLevelIds)
                ->where('diocese_id', $dioceseId)
                ->where('status', Status::ACTIVE)
                ->count();

            if (! $dioceseId || $validDestinationCount !== $toLevelIds->count()) {
                $validator->errors()->add('to_level_ids', 'Los niveles destino deben pertenecer a la diócesis de la parroquia del niño.');

                return;
            }

            $movement = app(CatechismPeriodMovementService::class)
                ->activeMovementForChurch($child->church, CatechismPeriodMovementService::REINSCRIPTIONS);

            if (! $movement) {
                $validator->errors()->add('child_id', 'No hay un movimiento de reinscripciones activo para la parroquia del niño.');

                return;
            }

            $alreadyReinscribed = ChildReinscription::query()
                ->where('child_id', $child->id)
                ->exists();

            if ($alreadyReinscribed) {
                $validator->errors()->add('child_id', 'Este niño ya fue reinscrito.');
            }

            if ($currentLevelIds->sort()->values()->all() === $toLevelIds->sort()->values()->all()) {
                $validator->errors()->add('to_level_ids', 'Selecciona al menos un nivel destino diferente a los niveles actuales.');
            }
        });
    }
}
