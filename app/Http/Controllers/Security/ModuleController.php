<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\ModuleRequest;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Module::class, 'modulo');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $modules = Module::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('key', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15, ['id', 'name', 'description', 'key'])
            ->withQueryString();

        return Inertia::render('Security/Modules/Index', [
            'modules' => $modules,
            'search'  => $search,
        ]);
    }

    public function store(ModuleRequest $request): JsonResponse
    {
        $module = Module::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $module->only(['id', 'name', 'description', 'key']),
            'message' => 'Modulo creado correctamente.',
        ], 201);
    }

    public function update(ModuleRequest $request, Module $modulo): JsonResponse
    {
        $modulo->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $modulo->fresh()->only(['id', 'name', 'description', 'key']),
            'message' => 'Modulo actualizado correctamente.',
        ]);
    }

    public function destroy(Module $modulo): JsonResponse
    {
        $modulo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Modulo eliminado correctamente.',
        ]);
    }
}
