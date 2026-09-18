<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchCompetencyMatrix extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_id',
        'matrix_data',
        'is_validated',
        'validation_date',
    ];

    protected function casts(): array
    {
        return [
            'matrix_data' => 'array',
            'is_validated' => 'boolean',
            'validation_date' => 'datetime',
        ];
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }
}
