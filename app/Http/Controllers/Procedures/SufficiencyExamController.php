<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SufficiencyExam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SufficiencyExamController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.sufficiency-exams.index', [
            'exams' => SufficiencyExam::query()->with(['student', 'course'])->latest('id')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? SufficiencyExam::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SufficiencyExam::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('sufficiency-exams.index')->with('success', 'Examen de suficiencia registrado correctamente.');
    }

    public function update(Request $request, SufficiencyExam $sufficiencyExam): RedirectResponse
    {
        $sufficiencyExam->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('sufficiency-exams.index')->with('success', 'Examen de suficiencia actualizado correctamente.');
    }

    public function destroy(SufficiencyExam $sufficiencyExam): RedirectResponse
    {
        $sufficiencyExam->delete();

        return back()->with('success', 'Examen de suficiencia eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'director_approval' => ['nullable', 'boolean'],
            'jury_members' => ['nullable', 'json'],
            'resolution_number' => ['nullable', 'string', 'max:80'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'act_number' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['solicitado', 'aprobado_director', 'jurado_designado', 'rendido', 'resuelto'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['director_approval'] = (bool) ($validated['director_approval'] ?? false);
        $validated['jury_members'] = ! empty($validated['jury_members']) ? json_decode($validated['jury_members'], true) : null;

        return $validated;
    }
}
