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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedMunicipalities(): BelongsToMany
    {
        return $this->belongsToMany(Municipality::class, 'scopes', 'user_id', 'scope_id')
            ->using(Scope::class)
            ->wherePivot('scope_type', Scope::TYPE_MUNICIPALITY)
            ->withPivotValue('scope_type', Scope::TYPE_MUNICIPALITY)
            ->withTimestamps();
    }

    public function assignedChurches(): BelongsToMany
    {
        return $this->belongsToMany(Church::class, 'scopes', 'user_id', 'scope_id')
            ->using(Scope::class)
            ->wherePivot('scope_type', Scope::TYPE_CHURCH)
            ->withPivotValue('scope_type', Scope::TYPE_CHURCH)
            ->withTimestamps();
    }

    public function assignedCommunities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'scopes', 'user_id', 'scope_id')
            ->using(Scope::class)
            ->wherePivot('scope_type', Scope::TYPE_COMMUNITY)
            ->withPivotValue('scope_type', Scope::TYPE_COMMUNITY)
            ->withTimestamps();
    }

    public function allowedCommunityIds(): Collection
    {
        $municipalityIds = $this->allowedMunicipalityIds();

        $communityIds = $this->relationLoaded('assignedCommunities')
            ? $this->assignedCommunities->pluck('id')
            : $this->assignedCommunities()->pluck('communities.id');

        if ($municipalityIds->isNotEmpty()) {
            $communityIds = $communityIds->merge(
                Community::query()
                    ->whereIn('municipality_id', $municipalityIds)
                    ->pluck('id')
            );
        }

        return $this->normalizeIdCollection($communityIds);
    }

    public function allowedChurchIds(): Collection
    {
        if ($this->relationLoaded('assignedChurches')) {
            return $this->normalizeIdCollection($this->assignedChurches->pluck('id'));
        }

        return $this->normalizeIdCollection(
            $this->assignedChurches()->pluck('churches.id')
        );
    }

    public function hasModuleFullScope(string $module): bool
    {
        return $this->can("{$module}.scope.all");
    }

    public function allowedMunicipalityIds(): Collection
    {
        if ($this->relationLoaded('assignedMunicipalities')) {
            return $this->normalizeIdCollection($this->assignedMunicipalities->pluck('id'));
        }

        return $this->normalizeIdCollection(
            $this->assignedMunicipalities()->pluck('municipalities.id')
        );
    }

    public function canAccessMunicipalityId(?int $municipalityId): bool
    {
        return $municipalityId !== null && $this->allowedMunicipalityIds()->contains($municipalityId);
    }

    public function canAccessCommunityId(?int $communityId): bool
    {
        return $communityId !== null && $this->allowedCommunityIds()->contains($communityId);
    }

    public function canAccessChurchId(?int $churchId): bool
    {
        return $churchId !== null && $this->allowedChurchIds()->contains($churchId);
    }

    public function canAccessChapel(Chapel $chapel): bool
    {
        return $this->hasModuleFullScope('capillas')
            || $this->canAccessCommunityId($chapel->community_id)
            || $this->canAccessChurchId($chapel->church_id);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    private function normalizeIdCollection(Collection $ids): Collection
    {
        return $ids
            ->filter(fn (mixed $id): bool => $id !== null)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
    }
}
