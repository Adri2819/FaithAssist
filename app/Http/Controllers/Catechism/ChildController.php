<?php

namespace App\Http\Controllers\Catechism;

use App\Globals\BloodType;
use App\Globals\Sex;
use App\Globals\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catechism\ChildRequest;
use App\Models\Catechism\Child;
use App\Models\Ecclesiastes\Church;
use App\Models\Lada;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Services\ChildCodeGenerator;
use App\Services\UserScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ChildController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Child::class, 'child');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $churchId = $request->integer('church_id') ?: null;
        $municipalityId = $request->integer('municipality_id') ?: null;
        $scope = new UserScopeService($request->user());

        $query = Child::query()
            ->when($search !== '', function ($query) use ($search) {
                    $builder->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('paterno', 'like', "%{$search}%")
                        ->orWhere('materno', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('church', fn ($church) => $church->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('community', fn ($community) => $community->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($churchId, fn ($query) => $query->where('church_id', $churchId))
            ->when($municipalityId, function ($query) use ($municipalityId) {
                $query->where(function ($builder) use ($municipalityId) {
                    $builder->whereHas('church', fn ($church) => $church->where('municipality_id', $municipalityId))
                        ->orWhereHas('community', fn ($community) => $community->where('municipality_id', $municipalityId));
                });
            })
            ->orderBy('paterno')
            ->orderBy('materno')
            ->orderBy('name');

        $children = ($scope->isGlobal() ? $query : $scope->applyChildScope($query))
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Child $child) => $this->serializeChild($child));

        $filterOptions = $this->filterOptions($request);

        return Inertia::render('Catechism/Children/Index', [
            'children' => $children,
            'search' => $search,
            'filters' => [
                'church_id' => $churchId,
                'municipality_id' => $municipalityId,
            ],
            'churches' => $filterOptions['churches'],
            'municipalities' => $filterOptions['municipalities'],
            'statusLabels' => $this->statusLabels(),
            'sexLabels' => $this->sexLabels(),
            'bloodTypeLabels' => $this->bloodTypeLabels(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Catechism/Children/Form', [
            'child' => null,
            ...$this->formOptions($request),
        ]);
    }

    public function store(ChildRequest $request, ChildCodeGenerator $codeGenerator): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use (&$data, $codeGenerator): void {
            $data['code'] = $codeGenerator->generate($data);
            Child::create($data);
        });

        return redirect()->route('children.index')
            ->with('success', 'Niño creado correctamente.');
    }

    public function edit(Request $request, Child $child): Response
    {
        $child->loadMissing(['church:id,name', 'community:id,name']);

        return Inertia::render('Catechism/Children/Form', [
            'child' => $this->serializeChild($child),
            ...$this->formOptions($request),
        ]);
    }

    public function update(ChildRequest $request, Child $child): RedirectResponse
    {
        $child->update(Arr::only($request->validated(), [
            'church_id',
            'community_id',
            'email',
            'phone_lada',
            'phone',
            'emergency_phone_lada',
            'emergency_phone',
            'observations',
            'privacy_terms',
            'status',
        ]));

        return redirect()->route('children.index')
            ->with('success', 'Niño actualizado correctamente.');
    }

    public function destroy(Child $child): RedirectResponse
    {
        $child->delete();

        return redirect()->route('children.index')
            ->with('success', 'Niño eliminado correctamente.');
    }

    private function formOptions(Request $request): array
    {
        $filterOptions = $this->filterOptions($request);
        $scope = new UserScopeService($request->user());

        return [
            'churches' => $filterOptions['churches'],
            'municipalities' => $filterOptions['municipalities'],
            'communities' => Community::query()
                ->when(! $scope->isGlobal(), fn ($q) => $q->whereIn('id', $scope->communityIds()))
                ->where('status', Status::ACTIVE)
                ->orderBy('name')
                ->get(['id', 'municipality_id', 'name']),
            'countryCodes' => Lada::options(),
            'defaultCountryCode' => Lada::defaultCode(),
            'statuses' => $this->options($this->statusLabels()),
            'sexes' => $this->options($this->sexLabels()),
            'bloodTypes' => $this->options($this->bloodTypeLabels()),
        ];
    }

    private function serializeChild(Child $child): array
    {
        return [
            'id' => $child->id,
            'church_id' => $child->church_id,
            'community_id' => $child->community_id,
            'name' => $child->name,
            'paterno' => $child->paterno,
            'materno' => $child->materno,
            'full_name' => trim(collect([$child->name, $child->paterno, $child->materno])->filter()->implode(' ')),
            'code' => $child->code,
            'birthdate' => $child->birthdate?->format('Y-m-d'),
            'sex' => $child->sex,
            'email' => $child->email,
            'phone_lada' => $child->phone_lada,
            'phone' => $child->phone,
            'emergency_phone_lada' => $child->emergency_phone_lada,
            'emergency_phone' => $child->emergency_phone,
            'blood_type' => $child->blood_type,
            'observations' => $child->observations,
            'privacy_terms' => $child->privacy_terms,
            'status' => $child->status,
            'church' => $child->church?->name,
            'community' => $child->community?->name,
            'created_at' => $child->created_at?->format('d/m/Y'),
        ];
    }

    private function filterOptions(Request $request): array
    {
        $scope = new UserScopeService($request->user());

        return [
            'churches' => Church::query()
                ->when(! $scope->isGlobal(), fn ($q) => $q->whereIn('id', $scope->churchIds()))
                ->where('status', Status::ACTIVE)
                ->orderBy('name')
                ->get(['id', 'municipality_id', 'name']),
            'municipalities' => Municipality::query()
                ->when(! $scope->isGlobal(), fn ($q) => $q->whereIn('id', $scope->municipalityIds()))
                ->where('status', Status::ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    private function statusLabels(): array
    {
        return [
            Status::ACTIVE => 'Activo',
            Status::INACTIVE => 'Inactivo',
            Status::COMPLETED => 'Completado',
            Status::WITHDRAW => 'Retirado',
            Status::SUSPENDED => 'Suspendido',
        ];
    }

    private function sexLabels(): array
    {
        return [
            Sex::MALE => 'Masculino',
            Sex::FEMALE => 'Femenino',
        ];
    }

    private function bloodTypeLabels(): array
    {
        return [
            BloodType::A_POSITIVE => 'A+',
            BloodType::A_NEGATIVE => 'A-',
            BloodType::B_POSITIVE => 'B+',
            BloodType::B_NEGATIVE => 'B-',
            BloodType::AB_POSITIVE => 'AB+',
            BloodType::AB_NEGATIVE => 'AB-',
            BloodType::O_POSITIVE => 'O+',
            BloodType::O_NEGATIVE => 'O-',
            BloodType::UNKNOWN => 'Desconocido',
        ];
    }

    private function options(array $labels): array
    {
        return collect($labels)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
