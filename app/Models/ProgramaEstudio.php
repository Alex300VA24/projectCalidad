<?php

namespace App\Models;

use Database\Factories\ProgramaEstudioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramaEstudio extends Model
{
    /** @use HasFactory<ProgramaEstudioFactory> */
    use HasFactory;

    protected $table = 'programas_estudio';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function mediciones(): HasMany
    {
        return $this->hasMany(IndicadorMedicion::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function incidenciasMatricula(): HasMany
    {
        return $this->hasMany(IncidenciaMatricula::class);
    }
}
