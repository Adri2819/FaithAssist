<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\LevelRequest;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Operation\Level;
use App\Services\UserScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LevelController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Level::class, 'nivel');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $scope = new UserScopeService($request->user());

        $levels = Level::query()
            ->with('diocese:id,name')
            ->when(! $scope->isGlobal(), fn ($q) => $q->whereIn('diocese_id', $scope->dioceseIds()))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhereHas('diocese', fn ($dioceseQuery) => $dioceseQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(15, ['id', 'diocese_id', 'name', 'description', 'status'])
            ->withQueryString();

        $dioceses = Diocese::query()
            ->when(! $scope->isGlobal(), fn ($q) => $q->whereIn('id', $scope->dioceseIds()))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Operation/Levels/Index', [
            'levels' => $levels,
            'dioceses' => $dioceses,
            'search' => $search,
        ]);
    }

    public function store(LevelRequest $request): JsonResponse
    {
        $data = $request->validated();

        $level = Level::create($data);

        return response()->json([
            'success' => true,
            'data' => $level->only(['id', 'diocese_id', 'name', 'description', 'status']),
            'message' => 'Nivel creado exitosamente.',
        ], 201);
    }

    public function update(LevelRequest $request, Level $nivel): JsonResponse
    {
        $data = $request->validated();

        $nivel->update($data);

        return response()->json([
            'success' => true,
            'data' => $nivel->only(['id', 'diocese_id', 'name', 'description', 'status']),
            'message' => 'Nivel actualizado exitosamente.',
        ]);
    }

    public function destroy(Level $nivel): JsonResponse
    {
        $nivel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nivel eliminado exitosamente.',
        ]);
    }
}