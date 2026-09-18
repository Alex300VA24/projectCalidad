<?php

namespace App\Models;

use Database\Factories\PeriodoAcademicoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoAcademico extends Model
{
    /** @use HasFactory<PeriodoAcademicoFactory> */
    use HasFactory;

    protected $table = 'periodos_academicos';

    protected $fillable = ['codigo', 'fecha_inicio', 'fecha_fin', 'activo'];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
