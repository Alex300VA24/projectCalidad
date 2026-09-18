<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\GraduateCompetencyEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GraduateCompetencyEvaluationController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.graduate-competency-evaluations.index', [
            'evaluations' => GraduateCompetencyEvaluation::query()->latest('id')->get(),
            'editing' => $request->integer('edit') ? GraduateCompetencyEvaluation::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        GraduateCompetencyEvaluation::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('graduate-competency-evaluations.index')->with('success', 'Evaluación de competencias registrada correctamente.');
    }

    public function update(Request $request, GraduateCompetencyEvaluation $graduateCompetencyEvaluation): RedirectResponse
    {
        $graduateCompetencyEvaluation->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('graduate-competency-evaluations.index')->with('success', 'Evaluación de competencias actualizada correctamente.');
    }

    public function destroy(GraduateCompetencyEvaluation $graduateCompetencyEvaluation): RedirectResponse
    {
        $graduateCompetencyEvaluation->delete();

        return back()->with('success', 'Evaluación de competencias eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'academic_period' => ['required', 'string', 'max:20'],
            'committee_members' => ['required', 'json'],
            'methodology_plan' => ['nullable', 'json'],
            'evaluation_report' => ['nullable', 'json'],
            'improvement_actions' => ['nullable', 'json'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['committee_members'] = json_decode($validated['committee_members'], true);

        foreach (['methodology_plan', 'evaluation_report', 'improvement_actions'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
