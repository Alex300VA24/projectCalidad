<?php

namespace App\Models;

use Database\Factories\MatriculaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    /** @use HasFactory<MatriculaFactory> */
    use HasFactory;

    protected $fillable = [
        'programa_estudio_id',
        'estudiante_id',
        'curso_id',
        'periodo_academico_id',
        'periodo_academico',
        'ciclo_academico',
        'numero_matricula',
        'estado_matricula',
        'estado_resultado',
    ];

    protected function casts(): array
    {
        return [
            'ciclo_academico' => 'integer',
            'numero_matricula' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $matricula): void {
            if ($matricula->periodo_academico !== null) {
                $matricula->periodo_academico_id = PeriodoAcademico::query()->where('codigo', $matricula->periodo_academico)->value('id');
            }
        });
    }

    public function programaEstudio(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'curso_id');
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }
}
