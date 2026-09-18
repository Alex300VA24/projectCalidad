<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCurriculumReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('curriculum.evaluate') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'curriculum_id' => ['required', 'integer', 'exists:curricula,id'],
            'coteccu_user_id' => ['required', 'integer', 'exists:users,id'],
            'checklist_data' => ['required', 'array'],
            'decision' => ['required', Rule::in(['revalidar', 'ajustar', 'redisenar'])],
            'procedure_id' => ['nullable', 'integer', 'exists:procedures,id'],
        ];
    }
}
