<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\PhysicalAcademicHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhysicalAcademicHistoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.physical-academic-histories.index', [
            'histories' => PhysicalAcademicHistory::query()->with('student')->latest('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? PhysicalAcademicHistory::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PhysicalAcademicHistory::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('physical-academic-histories.index')->with('success', 'Historial académico físico registrado correctamente.');
    }

    public function update(Request $request, PhysicalAcademicHistory $physicalAcademicHistory): RedirectResponse
    {
        $physicalAcademicHistory->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('physical-academic-histories.index')->with('success', 'Historial académico físico actualizado correctamente.');
    }

    public function destroy(PhysicalAcademicHistory $physicalAcademicHistory): RedirectResponse
    {
        $physicalAcademicHistory->delete();

        return back()->with('success', 'Historial académico físico eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'entry_year' => ['required', 'integer', 'max:2007'],
            'source_acts_references' => ['nullable', 'json'],
            'physical_history_data' => ['nullable', 'json'],
            'status' => ['required', Rule::in(['elaborado', 'verificado', 'enviado_registros'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        foreach (['source_acts_references', 'physical_history_data'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
