<?php

namespace App\Models;

use App\States\Procedure\ProcedureState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Procedure extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'domain_enum',
        'current_state',
        'applicant_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'current_state' => ProcedureState::class,
            'metadata' => 'array',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProcedureLog::class);
    }
}
