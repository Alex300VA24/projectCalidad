<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\AnonymousExam;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnonymousExamController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.anonymous-exams.index', [
            'exams' => AnonymousExam::query()->with(['course', 'teacher'])->latest('id')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? AnonymousExam::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AnonymousExam::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('anonymous-exams.index')->with('success', 'Examen anónimo registrado correctamente.');
    }

    public function update(Request $request, AnonymousExam $anonymousExam): RedirectResponse
    {
        $anonymousExam->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('anonymous-exams.index')->with('success', 'Examen anónimo actualizado correctamente.');
    }

    public function destroy(AnonymousExam $anonymousExam): RedirectResponse
    {
        $anonymousExam->delete();

        return back()->with('success', 'Examen anónimo eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'exam_date' => ['required', 'date'],
            'format_code' => ['required', 'string', 'max:80'],
            'sealed_envelope_code' => ['nullable', 'string', 'max:80'],
            'desglosables_count' => ['required', 'integer', 'min:0'],
            'grades_data' => ['nullable', 'json'],
            'status' => ['required', Rule::in([
                AnonymousExam::STATUS_PREPARADO,
                AnonymousExam::STATUS_APLICADO,
                AnonymousExam::STATUS_DESGLOSADO_LACRADO,
                AnonymousExam::STATUS_CALIFICADO,
                AnonymousExam::STATUS_CONSOLIDADO,
            ])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['grades_data'] = ! empty($validated['grades_data']) ? json_decode($validated['grades_data'], true) : null;

        return $validated;
    }
}
