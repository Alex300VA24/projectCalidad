<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_id',
        'academic_period',
        'admission_results_data',
        'entry_profile_eval',
        'leveling_needs',
        'final_report_path',
    ];

    protected function casts(): array
    {
        return [
            'admission_results_data' => 'array',
            'entry_profile_eval' => 'array',
            'leveling_needs' => 'array',
        ];
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }
}
