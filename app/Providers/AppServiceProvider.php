<?php

namespace App\Providers;

use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Ecclesiastes\Deanery;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Operation\PeriodMovement;
use App\Models\Operation\Period;
use App\Models\Module;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\Regions\State;
use App\Policies\ChapelPolicy;
use App\Policies\ChurchPolicy;
use App\Policies\CommunityPolicy;
use App\Policies\DeaneryPolicy;
use App\Policies\DiocesePolicy;
use App\Policies\ModulePolicy;
use App\Policies\MovementPolicy;
use App\Policies\MunicipalityPolicy;
use App\Policies\PeriodPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\StatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(State::class, StatePolicy::class);
        Gate::policy(Municipality::class, MunicipalityPolicy::class);
        Gate::policy(Community::class, CommunityPolicy::class);
        Gate::policy(Diocese::class, DiocesePolicy::class);
        Gate::policy(Deanery::class, DeaneryPolicy::class);
        Gate::policy(Church::class, ChurchPolicy::class);
        Gate::policy(Chapel::class, ChapelPolicy::class);
        Gate::policy(Period::class, PeriodPolicy::class);
        Gate::policy(PeriodMovement::class, MovementPolicy::class);
    }
}
