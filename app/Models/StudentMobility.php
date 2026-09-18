<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMobility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'student_id',
        'destination_university',
        'call_type',
        'is_interareas_view',
        'orni_registration_code',
        'fee_exemption',
        'subvention_status',
        'convalidation_resolution_number',
        'equivalent_grades_data',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_interareas_view' => 'boolean',
            'fee_exemption' => 'boolean',
            'equivalent_grades_data' => 'array',
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
