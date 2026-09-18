<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentReferral extends Model
{
    use HasFactory, SoftDeletes;

    public const REFERRED_TO_BIENESTAR = 'Bienestar';

    public const REFERRED_TO_PSICOLOGIA = 'Psicologia';

    public const REFERRED_TO_SOCIAL = 'Social';

    protected $fillable = [
        'procedure_id',
        'programa_estudio_id',
        'student_id',
        'referrer_id',
        'periodo_academico_id',
        'referred_to',
        'referral_sheet',
        'status',
    ];

    protected function casts(): array
    {
        return ['referral_sheet' => 'array'];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function programaEstudio(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
