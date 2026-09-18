<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\EducationalObjectiveEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducationalObjectiveEvaluationController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.educational-objective-evaluations.index', [
            'evaluations' => EducationalObjectiveEvaluation::query()->latest('id')->get(),
            'editing' => $request->integer('edit') ? EducationalObjectiveEvaluation::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        EducationalObjectiveEvaluation::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('educational-objective-evaluations.index')->with('success', 'Evaluación de objetivos educacionales registrada correctamente.');
    }

    public function update(Request $request, EducationalObjectiveEvaluation $educationalObjectiveEvaluation): RedirectResponse
    {
        $educationalObjectiveEvaluation->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('educational-objective-evaluations.index')->with('success', 'Evaluación de objetivos educacionales actualizada correctamente.');
    }

    public function destroy(EducationalObjectiveEvaluation $educationalObjectiveEvaluation): RedirectResponse
    {
        $educationalObjectiveEvaluation->delete();

        return back()->with('success', 'Evaluación de objetivos educacionales eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'academic_period' => ['required', 'string', 'max:20'],
            'stakeholders_survey_data' => ['nullable', 'json'],
            'competency_level_report' => ['nullable', 'json'],
            'curriculum_feedback_actions' => ['nullable', 'json'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['stakeholders_survey_data', 'competency_level_report', 'curriculum_feedback_actions'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
