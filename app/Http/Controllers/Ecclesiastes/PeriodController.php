<?php

namespace App\Http\Controllers\Ecclesiastes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecclesiastes\PeriodRequest;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Ecclesiastes\Period;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Period::class, 'periodo');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $periods = Period::query()
            ->with('diocese:id,name')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('years', 'like', "%{$search}%")
                        ->orWhereHas('diocese', fn ($dioceseQuery) => $dioceseQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('start_date')
            ->paginate(15, ['id', 'diocese_id', 'name', 'start_date', 'end_date', 'years', 'status'])
            ->withQueryString();

        $dioceses = Diocese::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ecclesiastes/Periods/Index', [
            'periods' => $periods,
            'dioceses' => $dioceses,
            'search' => $search,
        ]);
    }

    public function store(PeriodRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['years'] = $this->resolveYears($data['start_date'], $data['end_date']);

        $period = Period::create($data);

        return response()->json([
            'success' => true,
            'data' => $period->only(['id', 'diocese_id', 'name', 'start_date', 'end_date', 'years', 'status']),
            'message' => 'Periodo creado correctamente.',
        ], 201);
    }

    public function update(PeriodRequest $request, Period $periodo): JsonResponse
    {
        $data = $request->validated();
        $data['years'] = $this->resolveYears($data['start_date'], $data['end_date']);

        $periodo->update($data);

        return response()->json([
            'success' => true,
            'data' => $periodo->fresh()->only(['id', 'diocese_id', 'name', 'start_date', 'end_date', 'years', 'status']),
            'message' => 'Periodo actualizado correctamente.',
        ]);
    }

    public function destroy(Period $periodo): JsonResponse
    {
        $periodo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Periodo eliminado correctamente.',
        ]);
    }

    private function resolveYears(string $startDate, string $endDate): string
    {
        $startYear = (int) date('Y', strtotime($startDate));
        $endYear = (int) date('Y', strtotime($endDate));

        return $startYear === $endYear
            ? (string) $startYear
            : "{$startYear}-{$endYear}";
    }
}
