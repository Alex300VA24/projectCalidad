<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\TeachingLoadRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingLoadRequirementController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.teaching-load-requirements.index', [
            'requirements' => TeachingLoadRequirement::query()->latest('id')->get(),
            'editing' => $request->integer('edit') ? TeachingLoadRequirement::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TeachingLoadRequirement::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('teaching-load-requirements.index')->with('success', 'Requerimiento de carga lectiva registrado correctamente.');
    }

    public function update(Request $request, TeachingLoadRequirement $teachingLoadRequirement): RedirectResponse
    {
        $teachingLoadRequirement->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('teaching-load-requirements.index')->with('success', 'Requerimiento de carga lectiva actualizado correctamente.');
    }

    public function destroy(TeachingLoadRequirement $teachingLoadRequirement): RedirectResponse
    {
        $teachingLoadRequirement->delete();

        return back()->with('success', 'Requerimiento de carga lectiva eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'academic_period' => ['required', 'string', 'max:20'],
            'course_profile_demands' => ['nullable', 'json'],
            'department_proposal' => ['nullable', 'json'],
            'director_conformity' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['course_profile_demands', 'department_proposal'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }
        $validated['director_conformity'] = (bool) ($validated['director_conformity'] ?? false);

        return $validated;
    }
}
