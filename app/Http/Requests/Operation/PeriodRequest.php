<?php

namespace App\Http\Requests\Operation;

use App\Globals\Status;
use App\Http\Requests\Concerns\UppercasesFields;
use App\Models\Operation\Period;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PeriodRequest extends FormRequest
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
        $periodId = $this->route('periodo')?->id;
        $dioceseId = (int) $this->input('diocese_id');

        return [
            'diocese_id' => ['required', 'integer', Rule::exists('dioceses', 'id')->whereNull('deleted_at')],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('periods', 'name')
                    ->ignore($periodId)
                    ->where(fn ($query) => $query
                        ->where('diocese_id', $dioceseId)
                        ->whereNull('deleted_at')),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in([
                Status::UPCOMING,
                Status::IN_PROGRESS,
                Status::COMPLETED,
            ])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $periodId = $this->route('periodo')?->id;
            $dioceseId = (int) $this->input('diocese_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            $overlappingPeriodExists = Period::query()
                ->where('diocese_id', $dioceseId)
                ->whereNull('deleted_at')
                ->when($periodId, fn ($query) => $query->whereKeyNot($periodId))
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate)
                ->exists();

            if ($overlappingPeriodExists) {
                $message = 'Ya existe un periodo de esta diocesis que se cruza con ese rango de fechas.';
                $validator->errors()->add('start_date', $message);
                $validator->errors()->add('end_date', $message);
            }

            if ($this->input('status') !== Status::IN_PROGRESS) {
                return;
            }

            $activePeriodExists = Period::query()
                ->where('diocese_id', $dioceseId)
                ->where('status', Status::IN_PROGRESS)
                ->whereNull('deleted_at')
                ->when($periodId, fn ($query) => $query->whereKeyNot($periodId))
                ->exists();

            if ($activePeriodExists) {
                $validator->errors()->add('status', 'Solo puede existir un periodo en curso por diocesis.');
            }
        });
    }
}
