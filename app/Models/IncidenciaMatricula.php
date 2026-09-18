<?php

namespace App\Models;

use Database\Factories\IncidenciaMatriculaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaMatricula extends Model
{
    /** @use HasFactory<IncidenciaMatriculaFactory> */
    use HasFactory;

    protected $table = 'incidencias_matricula';

    protected $fillable = [
        'programa_estudio_id',
        'periodo_academico_id',
        'periodo_academico',
        'descripcion',
        'estado',
        'resuelta_en',
    ];

    protected function casts(): array
    {
        return ['resuelta_en' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $incidencia): void {
            if ($incidencia->periodo_academico !== null) {
                $incidencia->periodo_academico_id = PeriodoAcademico::query()->where('codigo', $incidencia->periodo_academico)->value('id');
            }
        });
    }

    public function programaEstudio(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }
}
