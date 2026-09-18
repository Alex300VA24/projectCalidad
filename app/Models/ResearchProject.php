<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'student_id',
        'advisor_id',
        'research_line_id',
        'title',
        'project_status',
        'sustentation_act_number',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function researchLine(): BelongsTo
    {
        return $this->belongsTo(ResearchLine::class);
    }
}
