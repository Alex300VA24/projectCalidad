<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalObjectiveEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period',
        'stakeholders_survey_data',
        'competency_level_report',
        'curriculum_feedback_actions',
    ];

    protected function casts(): array
    {
        return [
            'stakeholders_survey_data' => 'array',
            'competency_level_report' => 'array',
            'curriculum_feedback_actions' => 'array',
        ];
    }
}
