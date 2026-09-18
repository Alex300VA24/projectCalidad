<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraduateFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'student_id',
        'stu_registration_code',
        'egresado_condition_validated',
        'approval_constancy',
        'expedito_constancy',
        'no_adeudo_constancy',
        'sunedu_data_payload',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'egresado_condition_validated' => 'boolean',
            'approval_constancy' => 'boolean',
            'expedito_constancy' => 'boolean',
            'no_adeudo_constancy' => 'boolean',
            'sunedu_data_payload' => 'array',
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
