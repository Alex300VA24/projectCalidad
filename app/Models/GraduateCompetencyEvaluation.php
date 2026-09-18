<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraduateCompetencyEvaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'academic_period',
        'committee_members',
        'methodology_plan',
        'evaluation_report',
        'improvement_actions',
    ];

    protected function casts(): array
    {
        return [
            'committee_members' => 'array',
            'methodology_plan' => 'array',
            'evaluation_report' => 'array',
            'improvement_actions' => 'array',
        ];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }
}
