<?php

namespace App\Models;

use Database\Factories\IndicadorMedicionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndicadorMedicion extends Model
{
    /** @use HasFactory<IndicadorMedicionFactory> */
    use HasFactory;

    protected $table = 'indicadores_mediciones';

    protected $fillable = [
        'indicador_id',
        'programa_estudio_id',
        'periodo_academico_id',
        'periodo_academico',
        'valor_medido',
        'meta_programada',
        'estado_cumplimiento',
        'analisis_causas',
        'acciones_mejora',
        'registrado_por',
        'consolidada_en',
        'datos_fuente',
    ];

    protected function casts(): array
    {
        return [
            'valor_medido' => 'decimal:2',
            'meta_programada' => 'decimal:2',
            'consolidada_en' => 'datetime',
            'datos_fuente' => 'array',
        ];
    }

    public function indicador(): BelongsTo
    {
        return $this->belongsTo(IndicadorMaestro::class, 'indicador_id');
    }

    public function programaEstudio(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function accionesMejora(): HasMany
    {
        return $this->hasMany(AccionMejoraIndicador::class, 'indicador_medicion_id');
    }
}
