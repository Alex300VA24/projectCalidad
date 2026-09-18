<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPerformanceEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period',
        'teacher_id',
        'student_survey_results',
        'matrix_consolidation',
        'improvement_plan',
    ];

    protected function casts(): array
    {
        return [
            'student_survey_results' => 'array',
            'matrix_consolidation' => 'array',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
