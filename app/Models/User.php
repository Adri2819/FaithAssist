<?php

namespace App\Models;

use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'profile_photo_path', 'municipality_id', 'church_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * null FK = acceso total al módulo; valor = restringido a ese único registro.
     */
    public function hasModuleFullScope(string $module): bool
    {
        return match ($module) {
            'municipios', 'comunidades' => $this->municipality_id === null,
            'parroquias'                => $this->church_id === null,
            'capillas'                  => $this->municipality_id === null && $this->church_id === null,
            default                     => $this->can("{$module}.scope.all"),
        };
    }

    public function canAccessMunicipalityId(?int $municipalityId): bool
    {
        if ($this->municipality_id === null) {
            return true;
        }

        return $municipalityId !== null && $this->municipality_id === $municipalityId;
    }

    public function canAccessCommunityId(?int $communityId): bool
    {
        if ($this->municipality_id === null) {
            return true;
        }

        if ($communityId === null) {
            return false;
        }

        return Community::where('id', $communityId)
            ->where('municipality_id', $this->municipality_id)
            ->exists();
    }

    public function canAccessChurchId(?int $churchId): bool
    {
        if ($this->church_id === null) {
            return true;
        }

        return $churchId !== null && $this->church_id === $churchId;
    }

    public function canAccessChapel(Chapel $chapel): bool
    {
        $communityOk = $this->municipality_id === null
            || ($chapel->community_id !== null && Community::where('id', $chapel->community_id)
                ->where('municipality_id', $this->municipality_id)
                ->exists());

        $churchOk = $this->church_id === null
            || $chapel->church_id === $this->church_id;

        return $communityOk || $churchOk;
    }

    /**
     * Retorna la colección de IDs permitidos para usar en whereIn.
     * Vacía cuando municipality_id es null (la capa superior omite el filtro vía hasModuleFullScope).
     */
    public function allowedMunicipalityIds(): \Illuminate\Support\Collection
    {
        if ($this->municipality_id === null) {
            return collect();
        }

        return collect([$this->municipality_id]);
    }

    /**
     * Vacía cuando church_id es null (la capa superior omite el filtro vía hasModuleFullScope).
     */
    public function allowedChurchIds(): \Illuminate\Support\Collection
    {
        if ($this->church_id === null) {
            return collect();
        }

        return collect([$this->church_id]);
    }

    /**
     * Comunidades del municipio asignado, o vacío si no hay restricción.
     */
    public function allowedCommunityIds(): \Illuminate\Support\Collection
    {
        if ($this->municipality_id === null) {
            return collect();
        }

        return Community::where('municipality_id', $this->municipality_id)->pluck('id');
    }
}
