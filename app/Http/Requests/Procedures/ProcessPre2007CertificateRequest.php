<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPre2007CertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic_history.elaborate') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'entry_year' => ['required', 'integer', 'max:2007'],
            'source_acts_references' => ['required', 'array'],
            'physical_history_data' => ['required', 'array'],
            'procedure_id' => ['nullable', 'integer', 'exists:procedures,id'],
        ];
    }
}
