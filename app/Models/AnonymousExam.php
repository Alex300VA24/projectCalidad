<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnonymousExam extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PREPARADO = 'preparado';

    public const STATUS_APLICADO = 'aplicado';

    public const STATUS_DESGLOSADO_LACRADO = 'desglosado_lacrado';

    public const STATUS_CALIFICADO = 'calificado';

    public const STATUS_CONSOLIDADO = 'consolidado';

    protected $fillable = [
        'procedure_id',
        'course_id',
        'teacher_id',
        'exam_date',
        'format_code',
        'sealed_envelope_code',
        'desglosables_count',
        'grades_data',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'grades_data' => 'array',
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

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
