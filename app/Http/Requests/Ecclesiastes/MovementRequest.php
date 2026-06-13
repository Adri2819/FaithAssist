<?php

namespace App\Http\Requests\Ecclesiastes;

use App\Globals\MovStatus;
use App\Globals\Status;
use App\Models\Ecclesiastes\Period;
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
            'effective_date' => ['required', 'date'],
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

            $effectiveDate = (string) $this->input('effective_date');
            $startDate = $period->start_date?->format('Y-m-d');
            $endDate = $period->end_date?->format('Y-m-d');

            if ($startDate !== null && $effectiveDate < $startDate) {
                $validator->errors()->add('effective_date', 'La fecha efectiva debe estar dentro del rango del periodo.');
            }

            if ($endDate !== null && $effectiveDate > $endDate) {
                $validator->errors()->add('effective_date', 'La fecha efectiva debe estar dentro del rango del periodo.');
            }
        });
    }
}
