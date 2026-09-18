<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingLoadRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_id',
        'academic_period',
        'course_profile_demands',
        'department_proposal',
        'director_conformity',
    ];

    protected function casts(): array
    {
        return [
            'course_profile_demands' => 'array',
            'department_proposal' => 'array',
            'director_conformity' => 'boolean',
        ];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }
}
