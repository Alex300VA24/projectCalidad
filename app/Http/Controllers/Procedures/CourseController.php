<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.courses.index', [
            'courses' => Course::query()->with('curriculum')->latest('id')->get(),
            'curricula' => Curriculum::query()->orderBy('name')->get(),
            'editing' => $request->integer('edit') ? Course::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Course::create($request->validate([
            'curriculum_id' => ['nullable', 'integer', 'exists:curricula,id'],
            'code' => ['required', 'string', 'max:30', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:180'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:255'],
        ]));

        return redirect()->route('courses.index')->with('success', 'Curso registrado correctamente.');
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course->update($request->validate([
            'curriculum_id' => ['nullable', 'integer', 'exists:curricula,id'],
            'code' => ['required', 'string', 'max:30', Rule::unique('courses', 'code')->ignore($course->id)],
            'name' => ['required', 'string', 'max:180'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:255'],
        ]));

        return redirect()->route('courses.index')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return back()->with('success', 'Curso eliminado.');
    }
}
