<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyllabusSocialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'course_id',
        'teacher_id',
        'socialization_date',
        'student_signatures_count',
        'act_path',
    ];

    protected function casts(): array
    {
        return ['socialization_date' => 'date'];
    }

    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class);
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
