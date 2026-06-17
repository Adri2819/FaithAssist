<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\MovementRequest;
use App\Models\Operation\PeriodMovement;
use App\Models\Operation\Period;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MovementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PeriodMovement::class, 'movimiento');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $movements = PeriodMovement::query()
            ->with(['period:id,diocese_id,name,years', 'period.diocese:id,name'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('period', fn ($periodQuery) => $periodQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('years', 'like', "%{$search}%")
                            ->orWhereHas('diocese', fn ($dioceseQuery) => $dioceseQuery->where('name', 'like', "%{$search}%")));
                });
            })
            ->orderByDesc('start_date')
            ->paginate(15, ['id', 'period_id', 'type', 'status', 'start_date', 'end_date', 'notes'])
            ->withQueryString();

        $periods = Period::query()
            ->with('diocese:id,name')
            ->orderByDesc('start_date')
            ->get(['id', 'diocese_id', 'name', 'years']);

        return Inertia::render('Operation/Movements/Index', [
            'movements' => $movements,
            'periods' => $periods->map(fn (Period $period): array => [
                'id' => $period->id,
                'name' => $period->name,
                'years' => $period->years,
                'diocese_name' => $period->diocese?->name,
            ])->values(),
            'search' => $search,
        ]);
    }

    public function store(MovementRequest $request): JsonResponse
    {
        $movement = PeriodMovement::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $movement->only(['id', 'period_id', 'type', 'status', 'starde_date', 'end_date', 'notes']),
            'message' => 'Movimiento creado correctamente.',
        ], 201);
    }

    public function update(MovementRequest $request, PeriodMovement $movimiento): JsonResponse
    {
        $movimiento->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $movimiento->fresh()->only(['id', 'period_id', 'type', 'status', 'starde_date', 'end_date', 'notes']),
            'message' => 'Movimiento actualizado correctamente.',
        ]);
    }

    public function destroy(PeriodMovement $movimiento): JsonResponse
    {
        $movimiento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Movimiento eliminado correctamente.',
        ]);
    }
}
