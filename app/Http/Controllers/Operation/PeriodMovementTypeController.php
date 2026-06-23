<?php

namespace App\Http\Controllers\Operation;

use App\Globals\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\PeriodMovementTypeRequest;
use App\Models\Operation\PeriodMovementType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodMovementTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PeriodMovementType::class, 'tipo_movimiento_periodo');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $movementTypes = PeriodMovementType::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15, ['id', 'name', 'description', 'status'])
            ->withQueryString();

        return Inertia::render('Operation/PeriodMovementTypes/Index', [
            'movementTypes' => $movementTypes,
            'search' => $search,
            'statusOptions' => [
                ['value' => Status::ACTIVE, 'label' => 'Activo'],
                ['value' => Status::INACTIVE, 'label' => 'Inactivo'],
            ],
        ]);
    }

    public function store(PeriodMovementTypeRequest $request): JsonResponse
    {
        $movementType = PeriodMovementType::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $movementType->only(['id', 'name', 'description', 'status']),
            'message' => 'Tipo de movimiento creado correctamente.',
        ], 201);
    }

    public function update(PeriodMovementTypeRequest $request, PeriodMovementType $tipoMovimientoPeriodo): JsonResponse
    {
        $tipoMovimientoPeriodo->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $tipoMovimientoPeriodo->fresh()->only(['id', 'name', 'description', 'status']),
            'message' => 'Tipo de movimiento actualizado correctamente.',
        ]);
    }

    public function destroy(PeriodMovementType $tipoMovimientoPeriodo): JsonResponse
    {
        $tipoMovimientoPeriodo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de movimiento eliminado correctamente.',
        ]);
    }
}
