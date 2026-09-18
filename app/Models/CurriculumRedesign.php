<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumRedesign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'curriculum_id',
        'work_plan_data',
        'structure_format_data',
        'validation_stakeholders_data',
        'state',
    ];

    protected function casts(): array
    {
        return [
            'work_plan_data' => 'array',
            'structure_format_data' => 'array',
            'validation_stakeholders_data' => 'array',
        ];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }
}
