<?php

namespace App\Http\Controllers\Masses;

use App\Globals\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Masses\WeekendRequest;
use App\Models\Ecclesiastes\Church;
use App\Models\Masses\Weekend;
use App\Services\UserScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeekendController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Weekend::class, 'weekend');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $scope = new UserScopeService($request->user());

        $weekends = $scope->applyWeekendScope(
            Weekend::query()
                ->with('church:id,name')
                ->when($search, function ($query) use ($search): void {
                    $query->where(function ($builder) use ($search): void {
                        $builder->where('name', 'like', "%{$search}%")
                            ->orWhereHas('church', fn ($church) => $church->where('name', 'like', "%{$search}%"));
                    });
                })
                ->orderByDesc('starts_at')
        )
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Weekend $weekend): array => $this->serializeWeekend($weekend));

        return Inertia::render('Masses/Weekends/Index', [
            'weekends' => $weekends,
            'churches' => $this->churchOptions($request),
            'search' => $search,
        ]);
    }

    public function store(WeekendRequest $request): JsonResponse
    {
        $weekend = Weekend::query()->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $this->serializeWeekend($weekend->load('church:id,name')),
            'message' => 'Fin de semana creado correctamente.',
        ], 201);
    }

    public function update(WeekendRequest $request, Weekend $weekend): JsonResponse
    {
        $weekend->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $this->serializeWeekend($weekend->fresh('church:id,name')),
            'message' => 'Fin de semana actualizado correctamente.',
        ]);
    }

    public function destroy(Weekend $weekend): JsonResponse
    {
        $weekend->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fin de semana eliminado correctamente.',
        ]);
    }

    private function churchOptions(Request $request): array
    {
        $scope = new UserScopeService($request->user());

        return Church::query()
            ->when(! $scope->isGlobal(), fn ($query) => $query->whereIn('id', $scope->churchIds()))
            ->where('status', Status::ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    private function serializeWeekend(Weekend $weekend): array
    {
        return [
            'id' => $weekend->id,
            'church_id' => $weekend->church_id,
            'church' => $weekend->church?->name,
            'name' => $weekend->name,
            'starts_at' => $weekend->starts_at?->format('Y-m-d'),
            'ends_at' => $weekend->ends_at?->format('Y-m-d'),
            'status' => $weekend->status,
        ];
    }
}
