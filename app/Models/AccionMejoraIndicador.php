<?php

namespace App\Models;

use Database\Factories\AccionMejoraIndicadorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccionMejoraIndicador extends Model
{
    /** @use HasFactory<AccionMejoraIndicadorFactory> */
    use HasFactory;

    protected $table = 'acciones_mejora_indicadores';

    protected $fillable = [
        'indicador_medicion_id',
        'descripcion',
        'responsable_id',
        'fecha_limite',
        'estado',
        'seguimiento',
        'evidencia_url',
        'verificada_por',
        'cerrada_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'cerrada_en' => 'datetime',
        ];
    }

    public function medicion(): BelongsTo
    {
        return $this->belongsTo(IndicadorMedicion::class, 'indicador_medicion_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function verificadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificada_por');
    }
}
