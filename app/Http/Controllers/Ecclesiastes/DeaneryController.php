<?php

namespace App\Http\Controllers\Ecclesiastes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecclesiastes\DeaneryRequest;
use App\Models\Ecclesiastes\Deanery;
use App\Models\Ecclesiastes\Diocese;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeaneryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Deanery::class, 'decanato');
    }

        public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $deaneries = Deanery::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15, ['id', 'diocese_id', 'name', 'status'])
            ->withQueryString();

        $dioceses = Diocese::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ecclesiastes/Deaneries/Index', [
            'deaneries' => $deaneries,
            'dioceses'  => $dioceses,
            'search'    => $search,
        ]);
    }

    public function store(DeaneryRequest $request): JsonResponse
    {
        $deanery = Deanery::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $deanery->only(['id', 'diocese_id', 'name', 'status']),
            'message' => 'Decanato creado correctamente.',
        ], 201);
    }

    public function update(DeaneryRequest $request, Deanery $decanato): JsonResponse
    {
        $decanato->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $decanato->fresh()->only(['id', 'diocese_id', 'name', 'status']),
            'message' => 'Decanato actualizado correctamente.',
        ]);
    }

    public function destroy(Deanery $decanato): JsonResponse
    {
        $decanato->delete();

        return response()->json([
            'success' => true,
            'message' => 'Decanato eliminado correctamente.',
        ]);
    }
}
