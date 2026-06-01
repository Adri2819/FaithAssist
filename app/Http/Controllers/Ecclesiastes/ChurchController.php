<?php

namespace App\Http\Controllers\Ecclesiastes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecclesiastes\ChurchRequest;
use App\Models\Ecclesiastes\Church;
use App\Models\Ecclesiastes\Deanery;
use App\Models\Regions\Municipality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChurchController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Church::class, 'parroquia');
    }

        public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $churches = Church::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15, ['id', 'municipality_id', 'deanery_id', 'name', 'alias', 'email', 'phone', 'address', 'status'])
            ->withQueryString();

        $municipalities = Municipality::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $deaneries = Deanery::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ecclesiastes/Churches/Index', [
            'churches'       => $churches,
            'municipalities' => $municipalities,
            'deaneries'      => $deaneries,
            'search'         => $search,
        ]);
    }

    public function store(ChurchRequest $request): JsonResponse
    {
        $church = Church::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $church->only(['id', 'municipality_id', 'deanery_id', 'name', 'alias', 'email', 'phone', 'address', 'status']),
            'message' => 'Parroquia creada correctamente.',
        ], 201);
    }

    public function update(ChurchRequest $request, Church $parroquia): JsonResponse
    {
        $parroquia->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $parroquia->fresh()->only(['id', 'municipality_id', 'deanery_id', 'name', 'alias', 'email', 'phone', 'address', 'status']),
            'message' => 'Parroquia actualizada correctamente.',
        ]);
    }

    public function destroy(Church $parroquia): JsonResponse
    {
        $parroquia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Parroquia eliminada correctamente.',
        ]);
    }
}
