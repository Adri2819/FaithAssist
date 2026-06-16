<?php

namespace App\Http\Requests\Operation;

use App\Globals\MovStatus;
use App\Globals\Status;
use App\Models\Operation\Period;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_id' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::in(MovStatus::values())],
            'status' => ['required', Rule::in([
                Status::PENDING,
                Status::IN_PROGRESS,
                Status::COMPLETED,
            ])],
            'starde_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:starde_date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'period_id' => 'Periodo',
            'type' => 'Movimiento',
            'status' => 'Estatus',
            'effective_date' => 'Fecha efectiva',
            'notes' => 'Notas',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $period = Period::query()->find($this->input('period_id'));

            if (! $period) {
                return;
            }

            $startDate = (string) $this->input('starde_date');
            $endDate = (string) $this->input('end_date');
            $periodStartDate = $period->start_date?->format('Y-m-d');
            $periodEndDate = $period->end_date?->format('Y-m-d');

            if ($periodStartDate !== null && $startDate < $periodStartDate) {
                $validator->errors()->add('starde_date', 'La fecha de inicio debe estar dentro del rango del periodo.');
            }

            if ($periodEndDate !== null && $endDate > $periodEndDate) {
                $validator->errors()->add('end_date', 'La fecha de fin debe estar dentro del rango del periodo.');
            }
        });
    }
}
