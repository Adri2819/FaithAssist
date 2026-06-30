<?php

namespace App\Models\Catechism;

use App\Models\Concerns\LogsActivityTrail;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'church_id',
    'community_id',
    'name',
    'paterno',
    'materno',
    'code',
    'birthdate',
    'sex',
    'email',
    'phone_lada',
    'phone',
    'emergency_phone_lada',
    'emergency_phone',
    'blood_type',
    'observations',
    'privacy_terms',
    'status',
    'created_by',
    'updated_by',
    'deleted_by',
])]
#[Hidden([
    'deleted_at',
])]
class Child extends Model
{
    use HasFactory, LogsActivityTrail, SoftDeletes;

    protected $table = 'children';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'privacy_terms' => 'boolean',
            'status' => 'string',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'community_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
