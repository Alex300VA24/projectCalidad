<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'curriculum_id',
        'coteccu_user_id',
        'review_checklist_data',
        'technical_report_path',
        'decision',
        'state',
    ];

    protected function casts(): array
    {
        return ['review_checklist_data' => 'array'];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function coteccuUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coteccu_user_id');
    }
}
