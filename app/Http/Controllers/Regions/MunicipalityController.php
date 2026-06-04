<?php

namespace App\Http\Controllers\Regions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Regions\MunicipalityRequest;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Regions\Municipality;
use App\Models\Regions\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MunicipalityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Municipality::class, 'municipio');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $user = $request->user();
        $hasFullScope = $user->hasModuleFullScope('municipios');
        $allowedMunicipalityIds = $user->allowedMunicipalityIds();

        $municipalities = Municipality::query()
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('id', $allowedMunicipalityIds))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15, ['id', 'state_id', 'diocese_id', 'name', 'status'])
            ->withQueryString();

        $stateIds = $hasFullScope
            ? null
            : $municipalities->getCollection()
                ->pluck('state_id')
                ->filter()
                ->map(fn (mixed $id): int => (int) $id)
                ->unique()
                ->values();

        $dioceseIds = $hasFullScope
            ? null
            : $municipalities->getCollection()
                ->pluck('diocese_id')
                ->filter()
                ->map(fn (mixed $id): int => (int) $id)
                ->unique()
                ->values();

        $states = State::query()
            ->where('status', 'active')
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('id', $stateIds ?? []))
            ->orderBy('name')
            ->get(['id', 'name', 'short_name']);

        $dioceses = Diocese::query()
            ->where('status', 'active')
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('id', $dioceseIds ?? []))
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Regions/Municipalities/Index', [
            'municipalities' => $municipalities,
            'states' => $states,
            'dioceses' => $dioceses,
            'search' => $search,
        ]);
    }

    public function store(MunicipalityRequest $request): JsonResponse
    {
        $municipality = Municipality::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $municipality->only(['id', 'state_id', 'diocese_id', 'name', 'status']),
            'message' => 'Municipio creado correctamente.',
        ], 201);
    }

    public function update(MunicipalityRequest $request, Municipality $municipio): JsonResponse
    {
        $municipio->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $municipio->fresh()->only(['id', 'state_id', 'diocese_id', 'name', 'status']),
            'message' => 'Municipio actualizado correctamente.',
        ]);
    }

    public function destroy(Municipality $municipio): JsonResponse
    {
        $municipio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Municipio eliminado correctamente.',
        ]);
    }
}
