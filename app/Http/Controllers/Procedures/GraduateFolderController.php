<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\GraduateFolder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GraduateFolderController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.graduate-folders.index', [
            'folders' => GraduateFolder::query()->with('student')->latest('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? GraduateFolder::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        GraduateFolder::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('graduate-folders.index')->with('success', 'Carpeta de graduación registrada correctamente.');
    }

    public function update(Request $request, GraduateFolder $graduateFolder): RedirectResponse
    {
        $graduateFolder->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('graduate-folders.index')->with('success', 'Carpeta de graduación actualizada correctamente.');
    }

    public function destroy(GraduateFolder $graduateFolder): RedirectResponse
    {
        $graduateFolder->delete();

        return back()->with('success', 'Carpeta de graduación eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'stu_registration_code' => ['nullable', 'string', 'max:80'],
            'egresado_condition_validated' => ['nullable', 'boolean'],
            'approval_constancy' => ['nullable', 'boolean'],
            'expedito_constancy' => ['nullable', 'boolean'],
            'no_adeudo_constancy' => ['nullable', 'boolean'],
            'sunedu_data_payload' => ['nullable', 'json'],
            'status' => ['required', Rule::in(['en_verificacion', 'observado', 'completado', 'enviado_sunedu'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['egresado_condition_validated', 'approval_constancy', 'expedito_constancy', 'no_adeudo_constancy'] as $field) {
            $validated[$field] = (bool) ($validated[$field] ?? false);
        }
        $validated['sunedu_data_payload'] = ! empty($validated['sunedu_data_payload']) ? json_decode($validated['sunedu_data_payload'], true) : null;

        return $validated;
    }
}
