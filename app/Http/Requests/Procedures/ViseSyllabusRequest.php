<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;

class ViseSyllabusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('syllabus.vise') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'checklist_005' => ['required', 'array'],
        ];
    }
}
