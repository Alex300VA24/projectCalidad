<?php

namespace App\Models;

use App\Services\EvaluadorCumplimientoIndicadorService;
use Database\Factories\IndicadorMaestroFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndicadorMaestro extends Model
{
    /** @use HasFactory<IndicadorMaestroFactory> */
    use HasFactory;

    public const SENTIDO_MAYOR_IGUAL = 'MAYOR_IGUAL';

    public const SENTIDO_MENOR_IGUAL = 'MENOR_IGUAL';

    protected $table = 'indicadores_maestros';

    protected $fillable = [
        'codigo',
        'nombre',
        'proceso',
        'finalidad',
        'formula_texto',
        'unidad_medida',
        'meta_institucional',
        'nivel_advertencia',
        'nivel_critico',
        'sentido_meta',
        'regla_cumplimiento',
        'configuracion',
        'frecuencia',
        'responsable',
    ];

    protected function casts(): array
    {
        return [
            'meta_institucional' => 'decimal:2',
            'nivel_advertencia' => 'decimal:2',
            'nivel_critico' => 'decimal:2',
            'configuracion' => 'array',
        ];
    }

    public function mediciones(): HasMany
    {
        return $this->hasMany(IndicadorMedicion::class, 'indicador_id');
    }

    public function clasificar(float $valor): string
    {
        return app(EvaluadorCumplimientoIndicadorService::class)->evaluar($this, $valor);
    }
}
