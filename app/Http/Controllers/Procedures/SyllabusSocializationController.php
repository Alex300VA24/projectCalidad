<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Syllabus;
use App\Models\SyllabusSocialization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyllabusSocializationController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.syllabus-socializations.index', [
            'socializations' => SyllabusSocialization::query()->with(['syllabus.course', 'teacher'])->latest('id')->get(),
            'syllabi' => Syllabus::query()->with('course')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? SyllabusSocialization::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SyllabusSocialization::create($request->validate($this->rules()));

        return redirect()->route('syllabus-socializations.index')->with('success', 'Socialización de sílabo registrada correctamente.');
    }

    public function update(Request $request, SyllabusSocialization $syllabusSocialization): RedirectResponse
    {
        $syllabusSocialization->update($request->validate($this->rules()));

        return redirect()->route('syllabus-socializations.index')->with('success', 'Socialización de sílabo actualizada correctamente.');
    }

    public function destroy(SyllabusSocialization $syllabusSocialization): RedirectResponse
    {
        $syllabusSocialization->delete();

        return back()->with('success', 'Socialización de sílabo eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'syllabus_id' => ['required', 'integer', 'exists:syllabi,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'socialization_date' => ['required', 'date'],
            'student_signatures_count' => ['required', 'integer', 'min:0'],
            'act_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
