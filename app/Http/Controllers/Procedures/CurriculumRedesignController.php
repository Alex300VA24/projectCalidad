<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\CurriculumRedesign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurriculumRedesignController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.curriculum-redesigns.index', [
            'redesigns' => CurriculumRedesign::query()->with('curriculum')->latest('id')->get(),
            'curricula' => Curriculum::query()->orderBy('name')->get(),
            'editing' => $request->integer('edit') ? CurriculumRedesign::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CurriculumRedesign::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('curriculum-redesigns.index')->with('success', 'Rediseño curricular registrado correctamente.');
    }

    public function update(Request $request, CurriculumRedesign $curriculumRedesign): RedirectResponse
    {
        $curriculumRedesign->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('curriculum-redesigns.index')->with('success', 'Rediseño curricular actualizado correctamente.');
    }

    public function destroy(CurriculumRedesign $curriculumRedesign): RedirectResponse
    {
        $curriculumRedesign->delete();

        return back()->with('success', 'Rediseño curricular eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'curriculum_id' => ['required', 'integer', 'exists:curricula,id'],
            'work_plan_data' => ['nullable', 'json'],
            'structure_format_data' => ['nullable', 'json'],
            'validation_stakeholders_data' => ['nullable', 'json'],
            'state' => ['required', Rule::in(['en_elaboracion', 'en_validacion', 'aprobado', 'completado'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['work_plan_data', 'structure_format_data', 'validation_stakeholders_data'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
