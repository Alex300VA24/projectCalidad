<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;

class PrepareAnonymousExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('anonymous_exam.apply') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'exam_date' => ['required', 'date'],
            'student_count' => ['required', 'integer', 'min:1'],
            'procedure_id' => ['nullable', 'integer', 'exists:procedures,id'],
        ];
    }
}
