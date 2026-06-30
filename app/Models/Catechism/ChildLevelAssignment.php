<?php

namespace App\Models\Catechism;

use App\Models\Concerns\LogsActivityTrail;
use App\Models\Operation\Level;
use App\Models\Operation\Period;
use App\Models\Operation\PeriodMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'child_id',
    'level_id',
    'period_id',
    'period_movement_id',
    'status',
    'assigned_at',
    'ended_at',
    'notes',
    'created_by',
    'updated_by',
    'deleted_by',
])]
#[Hidden([
    'deleted_at',
])]
class ChildLevelAssignment extends Model
{
    use HasFactory, LogsActivityTrail, SoftDeletes;

    protected $table = 'child_level_assignments';

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date:Y-m-d',
            'ended_at' => 'date:Y-m-d',
            'status' => 'string',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class, 'child_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id');
    }

    public function periodMovement(): BelongsTo
    {
        return $this->belongsTo(PeriodMovement::class, 'period_movement_id');
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
}
