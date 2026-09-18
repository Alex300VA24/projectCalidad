<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\TeacherPerformanceEvaluation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherPerformanceEvaluationController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.teacher-performance-evaluations.index', [
            'evaluations' => TeacherPerformanceEvaluation::query()->with('teacher')->latest('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? TeacherPerformanceEvaluation::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TeacherPerformanceEvaluation::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('teacher-performance-evaluations.index')->with('success', 'Evaluación docente registrada correctamente.');
    }

    public function update(Request $request, TeacherPerformanceEvaluation $teacherPerformanceEvaluation): RedirectResponse
    {
        $teacherPerformanceEvaluation->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('teacher-performance-evaluations.index')->with('success', 'Evaluación docente actualizada correctamente.');
    }

    public function destroy(TeacherPerformanceEvaluation $teacherPerformanceEvaluation): RedirectResponse
    {
        $teacherPerformanceEvaluation->delete();

        return back()->with('success', 'Evaluación docente eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'academic_period' => ['required', 'string', 'max:20'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'student_survey_results' => ['nullable', 'json'],
            'matrix_consolidation' => ['nullable', 'json'],
            'improvement_plan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['student_survey_results', 'matrix_consolidation'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
