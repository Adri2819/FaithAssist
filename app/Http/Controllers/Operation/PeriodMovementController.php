<?php

namespace App\Http\Controllers\Operation;

use App\Globals\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\PeriodMovementRequest;
use App\Models\Operation\Period;
use App\Models\Operation\PeriodMovement;
use App\Models\Operation\PeriodMovementType;
use App\Services\UserScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodMovementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PeriodMovement::class, 'movimiento');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $scope = new UserScopeService($request->user());

        $movements = PeriodMovement::query()
            ->with([
                'period:id,diocese_id,name,years',
                'period.diocese:id,name',
                'periodMovementType:id,name,status',
            ])
            ->when(! $scope->isGlobal(), fn ($q) => $q->whereHas(
                'period',
                fn ($p) => $p->whereIn('diocese_id', $scope->dioceseIds())
            ))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('status', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('periodMovementType', fn ($movementTypeQuery) => $movementTypeQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('period', fn ($periodQuery) => $periodQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('years', 'like', "%{$search}%")
                            ->orWhereHas('diocese', fn ($dioceseQuery) => $dioceseQuery->where('name', 'like', "%{$search}%")));
                });
            })
            ->orderByDesc('start_date')
            ->paginate(15, ['id', 'period_id', 'period_movement_type_id', 'status', 'start_date', 'end_date', 'notes'])
            ->withQueryString();

        $periods = Period::query()
            ->with('diocese:id,name')
            ->when(! $scope->isGlobal(), fn ($q) => $q->whereIn('diocese_id', $scope->dioceseIds()))
            ->orderByDesc('start_date')
            ->get(['id', 'diocese_id', 'name', 'years']);

        $movementTypes = PeriodMovementType::query()
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'description']);

        return Inertia::render('Operation/PeriodMovements/Index', [
            'movements' => $movements,
            'periods' => $periods->map(fn (Period $period): array => [
                'id' => $period->id,
                'name' => $period->name,
                'years' => $period->years,
                'diocese_name' => $period->diocese?->name,
            ])->values(),
            'movementTypes' => $movementTypes->map(fn (PeriodMovementType $movementType): array => [
                'id' => $movementType->id,
                'name' => $movementType->name,
                'status' => $movementType->status,
                'description' => $movementType->description,
            ])->values(),
            'search' => $search,
            'statusOptions' => [
                ['value' => Status::PENDING, 'label' => 'Pendiente'],
                ['value' => Status::IN_PROGRESS, 'label' => 'En proceso'],
                ['value' => Status::COMPLETED, 'label' => 'Completado'],
            ],
        ]);
    }

    public function store(PeriodMovementRequest $request): JsonResponse
    {
        $movement = PeriodMovement::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $movement->only(['id', 'period_id', 'period_movement_type_id', 'status', 'start_date', 'end_date', 'notes']),
            'message' => 'Movimiento creado correctamente.',
        ], 201);
    }

    public function update(PeriodMovementRequest $request, PeriodMovement $movimiento): JsonResponse
    {
        $movimiento->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $movimiento->fresh()->only(['id', 'period_id', 'period_movement_type_id', 'status', 'start_date', 'end_date', 'notes']),
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