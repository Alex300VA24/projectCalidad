<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\ResearchCompetencyMatrix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchCompetencyMatrixController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.research-competency-matrices.index', [
            'matrices' => ResearchCompetencyMatrix::query()->with('curriculum')->latest('id')->get(),
            'curricula' => Curriculum::query()->orderBy('name')->get(),
            'editing' => $request->integer('edit') ? ResearchCompetencyMatrix::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ResearchCompetencyMatrix::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('research-competency-matrices.index')->with('success', 'Matriz de competencias registrada correctamente.');
    }

    public function update(Request $request, ResearchCompetencyMatrix $researchCompetencyMatrix): RedirectResponse
    {
        $researchCompetencyMatrix->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('research-competency-matrices.index')->with('success', 'Matriz de competencias actualizada correctamente.');
    }

    public function destroy(ResearchCompetencyMatrix $researchCompetencyMatrix): RedirectResponse
    {
        $researchCompetencyMatrix->delete();

        return back()->with('success', 'Matriz de competencias eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'curriculum_id' => ['required', 'integer', 'exists:curricula,id'],
            'matrix_data' => ['nullable', 'json'],
            'is_validated' => ['nullable', 'boolean'],
            'validation_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['matrix_data'] = ! empty($validated['matrix_data']) ? json_decode($validated['matrix_data'], true) : null;
        $validated['is_validated'] = (bool) ($validated['is_validated'] ?? false);

        return $validated;
    }
}
