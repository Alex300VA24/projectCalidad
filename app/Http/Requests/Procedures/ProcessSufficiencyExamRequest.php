<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;

class ProcessSufficiencyExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('jury_resolution.issue') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'jury_members' => ['required', 'array', 'min:1'],
            'jury_members.*' => ['required', 'string'],
            'procedure_id' => ['nullable', 'integer', 'exists:procedures,id'],
        ];
    }
}
