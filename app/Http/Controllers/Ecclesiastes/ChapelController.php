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

        $chapels = Chapel::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15, ['id', 'community_id', 'church_id', 'name', 'address', 'status'])
            ->withQueryString();

        $communities = Community::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $churches = Church::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ecclesiastes/Chapels/Index', [
            'chapels'     => $chapels,
            'communities' => $communities,
            'churches'    => $churches,
            'search'      => $search,
        ]);
    }

    public function store(ChapelRequest $request): JsonResponse
    {
        $chapel = Chapel::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $chapel->only(['id', 'community_id', 'church_id', 'name', 'address', 'status']),
            'message' => 'Capilla creada correctamente.',
        ], 201);
    }

    public function update(ChapelRequest $request, Chapel $capilla): JsonResponse
    {
        $capilla->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $capilla->fresh()->only(['id', 'community_id', 'church_id', 'name', 'address', 'status']),
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
