<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\AdmissionAnalysis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionAnalysisController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.admission-analyses.index', [
            'analyses' => AdmissionAnalysis::query()->latest('id')->get(),
            'editing' => $request->integer('edit') ? AdmissionAnalysis::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AdmissionAnalysis::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('admission-analyses.index')->with('success', 'Análisis de admisión registrado correctamente.');
    }

    public function update(Request $request, AdmissionAnalysis $admissionAnalysis): RedirectResponse
    {
        $admissionAnalysis->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('admission-analyses.index')->with('success', 'Análisis de admisión actualizado correctamente.');
    }

    public function destroy(AdmissionAnalysis $admissionAnalysis): RedirectResponse
    {
        $admissionAnalysis->delete();

        return back()->with('success', 'Análisis de admisión eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'academic_period' => ['required', 'string', 'max:20'],
            'admission_results_data' => ['nullable', 'json'],
            'entry_profile_eval' => ['nullable', 'json'],
            'leveling_needs' => ['nullable', 'json'],
            'final_report_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['admission_results_data', 'entry_profile_eval', 'leveling_needs'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
