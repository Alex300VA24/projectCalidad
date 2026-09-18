<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;

class GradeAnonymousExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grades.register') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'blind_grades' => ['required', 'array', 'min:1'],
            'blind_grades.*.desglosable_code' => ['required', 'string'],
            'blind_grades.*.grade' => ['required', 'numeric', 'min:0', 'max:20'],
        ];
    }
}
