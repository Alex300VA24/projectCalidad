<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'procedure_id',
        'user_id',
        'activity_code',
        'from_state',
        'to_state',
        'comments',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
