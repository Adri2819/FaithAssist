<?php

namespace App\Models;

use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['user_id', 'scope_type', 'scope_id'])]
class Scope extends Pivot
{
    protected $table = 'scopes';

    public $incrementing = true;

    public const TYPE_MUNICIPALITY = 'municipality';

    public const TYPE_CHURCH = 'church';

    public const TYPE_COMMUNITY = 'community';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'scope_id');
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'scope_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'scope_id');
    }
}