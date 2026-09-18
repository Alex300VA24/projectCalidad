<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternshipController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.internships.index', [
            'internships' => Internship::query()->with('student')->latest('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? Internship::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Internship::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('internships.index')->with('success', 'Práctica preprofesional registrada correctamente.');
    }

    public function update(Request $request, Internship $internship): RedirectResponse
    {
        $internship->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('internships.index')->with('success', 'Práctica preprofesional actualizada correctamente.');
    }

    public function destroy(Internship $internship): RedirectResponse
    {
        $internship->delete();

        return back()->with('success', 'Práctica preprofesional eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'company_name' => ['required', 'string', 'max:180'],
            'agreement_number' => ['nullable', 'string', 'max:80'],
            'monitoring_plan' => ['nullable', 'json'],
            'final_report_path' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['en_curso', 'concluida', 'observada'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['monitoring_plan'] = ! empty($validated['monitoring_plan']) ? json_decode($validated['monitoring_plan'], true) : null;

        return $validated;
    }
}
