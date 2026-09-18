<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduateRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'programa_estudio_id',
        'periodo_academico_id',
        'apt_list_number',
        'titulado',
        'condicion_laboral',
        'labora_especialidad',
        'update_form_data',
        'database_record',
        'annual_stats_report',
    ];

    protected function casts(): array
    {
        return [
            'update_form_data' => 'array',
            'database_record' => 'array',
            'annual_stats_report' => 'array',
            'titulado' => 'boolean',
            'labora_especialidad' => 'boolean',
        ];
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
}
