<?php

namespace App\Http\Controllers\Ecclesiastes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecclesiastes\DioceseRequest;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Regions\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DioceseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Diocese::class, 'diocesis');
    }

        public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $dioceses = Diocese::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15, ['id', 'state_id', 'name', 'bishop', 'status'])
            ->withQueryString();

        $states = State::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'short_name']);

        return Inertia::render('Ecclesiastes/Dioceses/Index', [
            'dioceses' => $dioceses,
            'states'   => $states,
            'search'   => $search,
        ]);
    }

    public function store(DioceseRequest $request): JsonResponse
    {
        $diocese = Diocese::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $diocese->only(['id', 'state_id', 'name', 'bishop', 'status']),
            'message' => 'Diocesis creada correctamente.',
        ], 201);
    }

    public function update(DioceseRequest $request, Diocese $diocesis): JsonResponse
    {
        $diocesis->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $diocesis->fresh()->only(['id', 'state_id', 'name', 'bishop', 'status']),
            'message' => 'Diocesis actualizada correctamente.',
        ]);
    }

    public function destroy(Diocese $diocesis): JsonResponse
    {
        $diocesis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Diocesis eliminada correctamente.',
        ]);
    }
}
