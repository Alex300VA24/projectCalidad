<?php

namespace App\Http\Requests\Procedures;

use Illuminate\Foundation\Http\FormRequest;

class CompleteStuFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sunedu_data.register') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approval_constancy' => ['required', 'boolean'],
            'expedito_constancy' => ['required', 'boolean'],
            'no_adeudo_constancy' => ['required', 'boolean'],
        ];
    }
}
