<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhysicalAcademicHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'student_id',
        'entry_year',
        'source_acts_references',
        'physical_history_data',
        'certificate_printed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'source_acts_references' => 'array',
            'physical_history_data' => 'array',
            'certificate_printed_at' => 'datetime',
        ];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
