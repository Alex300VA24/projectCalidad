<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\GradeCorrection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GradeCorrectionController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.grade-corrections.index', [
            'corrections' => GradeCorrection::query()->with(['student', 'course', 'teacher'])->latest('id')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? GradeCorrection::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        GradeCorrection::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('grade-corrections.index')->with('success', 'Corrección de nota registrada correctamente.');
    }

    public function update(Request $request, GradeCorrection $gradeCorrection): RedirectResponse
    {
        $gradeCorrection->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('grade-corrections.index')->with('success', 'Corrección de nota actualizada correctamente.');
    }

    public function destroy(GradeCorrection $gradeCorrection): RedirectResponse
    {
        $gradeCorrection->delete();

        return back()->with('success', 'Corrección de nota eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'fut_number' => ['required', 'string', 'max:80'],
            'request_reason' => ['required', 'string', 'max:1000'],
            'academic_report' => ['nullable', 'string', 'max:2000'],
            'elevated_to_dean' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['solicitado', 'en_evaluacion', 'aprobado', 'rechazado', 'elevado_decanato'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['elevated_to_dean'] = (bool) ($validated['elevated_to_dean'] ?? false);

        return $validated;
    }
}
