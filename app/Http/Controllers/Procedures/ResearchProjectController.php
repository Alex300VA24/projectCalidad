<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\ResearchLine;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResearchProjectController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.research-projects.index', [
            'projects' => ResearchProject::query()->with(['student', 'advisor', 'researchLine'])->latest('id')->get(),
            'researchLines' => ResearchLine::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? ResearchProject::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ResearchProject::create($request->validate($this->rules()));

        return redirect()->route('research-projects.index')->with('success', 'Proyecto de investigación registrado correctamente.');
    }

    public function update(Request $request, ResearchProject $researchProject): RedirectResponse
    {
        $researchProject->update($request->validate($this->rules()));

        return redirect()->route('research-projects.index')->with('success', 'Proyecto de investigación actualizado correctamente.');
    }

    public function destroy(ResearchProject $researchProject): RedirectResponse
    {
        $researchProject->delete();

        return back()->with('success', 'Proyecto de investigación eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'advisor_id' => ['required', 'integer', 'exists:users,id'],
            'research_line_id' => ['required', 'integer', 'exists:research_lines,id'],
            'title' => ['required', 'string', 'max:255'],
            'project_status' => ['required', Rule::in(['en_proceso', 'sustentado', 'observado', 'aprobado'])],
            'sustentation_act_number' => ['nullable', 'string', 'max:80'],
        ];
    }
}
