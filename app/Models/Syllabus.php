<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syllabus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'syllabi';

    protected $fillable = [
        'procedure_id',
        'programa_estudio_id',
        'course_id',
        'teacher_id',
        'periodo_academico_id',
        'academic_period',
        'status',
        'review_checklist',
        'library_requirement_data',
        'tipo_silabo',
        'modalidad',
        'seccion',
        'fundamentacion',
        'aprendizajes_esperados',
        'unidades',
        'sesiones_no_presenciales',
        'guias_aprendizaje',
        'material_trabajo_distancia',
        'tutoria_dia',
        'tutoria_medio',
        'tutoria_horario',
        'bibliografia',
    ];

    protected function casts(): array
    {
        return [
            'review_checklist' => 'array',
            'library_requirement_data' => 'array',
            'unidades' => 'array',
            'sesiones_no_presenciales' => 'array',
        ];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function programaEstudio(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function socializations(): HasMany
    {
        return $this->hasMany(SyllabusSocialization::class);
    }
}
