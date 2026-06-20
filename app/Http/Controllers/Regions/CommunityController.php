<?php

namespace App\Http\Controllers\Regions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Regions\CommunityRequest;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommunityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Community::class, 'comunidad');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $user = $request->user();
        $hasFullScope = $user->hasModuleFullScope('comunidades');
        $allowedMunicipalityIds = $user->allowedMunicipalityIds();

        $communityQuery = Community::query()
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('municipality_id', $allowedMunicipalityIds))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name');

        $communities = $communityQuery
            ->paginate(15, ['id', 'municipality_id', 'name', 'status'])
            ->withQueryString();

        $municipalities = Municipality::query()
            ->where('status', 'active')
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('id', $allowedMunicipalityIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Regions/Communities/Index', [
            'communities' => $communities,
            'municipalities' => $municipalities,
            'search' => $search,
        ]);
    }

    public function store(CommunityRequest $request): JsonResponse
    {
        $community = Community::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $community->only(['id', 'municipality_id', 'name', 'status']),
            'message' => 'Comunidad creada correctamente.',
        ], 201);
    }

    public function update(CommunityRequest $request, Community $comunidad): JsonResponse
    {
        $comunidad->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $comunidad->fresh()->only(['id', 'municipality_id', 'name', 'status']),
            'message' => 'Comunidad actualizada correctamente.',
        ]);
    }

    public function destroy(Community $comunidad): JsonResponse
    {
        $comunidad->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comunidad eliminada correctamente.',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Community::class);

        $search = $request->input('search', '');
        $user = $request->user();
        $hasFullScope = $user->hasModuleFullScope('comunidades');
        $allowedMunicipalityIds = $user->allowedMunicipalityIds();

        $communities = Community::query()
            ->with(['municipality:id,name'])
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('municipality_id', $allowedMunicipalityIds))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->select(['id', 'municipality_id', 'name', 'status'])
            ->lazy();

        $fileName = 'comunidades_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($communities) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM para que Excel respete acentos al abrir CSV.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Municipio', 'Nombre', 'Estatus']);

            foreach ($communities as $community) {
                fputcsv($output, [
                    $community->municipality?->name ?? '',
                    $community->name,
                    $community->status === 'active' ? 'Activo' : 'Inactivo',
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
