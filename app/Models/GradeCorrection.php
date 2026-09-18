<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeCorrection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'student_id',
        'course_id',
        'teacher_id',
        'fut_number',
        'request_reason',
        'academic_report',
        'elevated_to_dean',
        'status',
    ];

    protected function casts(): array
    {
        return ['elevated_to_dean' => 'boolean'];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
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
