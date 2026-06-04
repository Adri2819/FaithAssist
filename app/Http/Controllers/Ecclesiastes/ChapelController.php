<?php

namespace App\Http\Controllers\Ecclesiastes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecclesiastes\ChapelRequest;
use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChapelController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Chapel::class, 'capilla');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $user = $request->user();
        $hasFullScope = $user->hasModuleFullScope('capillas');
        $allowedCommunityIds = $user->allowedCommunityIds();
        $allowedChurchIds = $user->allowedChurchIds();

        $chapelQuery = Chapel::query()
            ->when(! $hasFullScope, function ($query) use ($allowedCommunityIds, $allowedChurchIds) {
                $query->where(function ($scope) use ($allowedCommunityIds, $allowedChurchIds) {
                    if ($allowedCommunityIds->isNotEmpty()) {
                        $scope->whereIn('community_id', $allowedCommunityIds);
                    }

                    if ($allowedChurchIds->isNotEmpty()) {
                        $method = $allowedCommunityIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                        $scope->{$method}('church_id', $allowedChurchIds);
                    }

                    if ($allowedCommunityIds->isEmpty() && $allowedChurchIds->isEmpty()) {
                        $scope->whereRaw('1 = 0');
                    }
                });
            })
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name');

        $chapels = $chapelQuery
            ->paginate(15, ['id', 'community_id', 'church_id', 'name', 'address', 'status'])
            ->withQueryString();

        $communityOptionIds = $hasFullScope
            ? null
            : $allowedCommunityIds
                ->merge($chapels->getCollection()->pluck('community_id'))
                ->filter()
                ->map(fn (mixed $id): int => (int) $id)
                ->unique()
                ->values();

        $churchOptionIds = $hasFullScope
            ? null
            : $allowedChurchIds
                ->merge($chapels->getCollection()->pluck('church_id'))
                ->filter()
                ->map(fn (mixed $id): int => (int) $id)
                ->unique()
                ->values();

        $communities = Community::query()
            ->where('status', 'active')
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('id', $communityOptionIds ?? []))
            ->orderBy('name')
            ->get(['id', 'name']);

        $churches = Church::query()
            ->where('status', 'active')
            ->when(! $hasFullScope, fn ($query) => $query->whereIn('id', $churchOptionIds ?? []))
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ecclesiastes/Chapels/Index', [
            'chapels' => $chapels,
            'communities' => $communities,
            'churches' => $churches,
            'search' => $search,
        ]);
    }

    public function store(ChapelRequest $request): JsonResponse
    {
        $chapel = Chapel::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $chapel->only(['id', 'community_id', 'church_id', 'name', 'address', 'status']),
            'message' => 'Capilla creada correctamente.',
        ], 201);
    }

    public function update(ChapelRequest $request, Chapel $capilla): JsonResponse
    {
        $capilla->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $capilla->fresh()->only(['id', 'community_id', 'church_id', 'name', 'address', 'status']),
            'message' => 'Capilla actualizada correctamente.',
        ]);
    }

    public function destroy(Chapel $capilla): JsonResponse
    {
        $capilla->delete();

        return response()->json([
            'success' => true,
            'message' => 'Capilla eliminada correctamente.',
        ]);
    }
}
