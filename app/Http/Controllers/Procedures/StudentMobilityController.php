<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\StudentMobility;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentMobilityController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.student-mobilities.index', [
            'mobilities' => StudentMobility::query()->with('student')->latest('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? StudentMobility::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        StudentMobility::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('student-mobilities.index')->with('success', 'Movilidad estudiantil registrada correctamente.');
    }

    public function update(Request $request, StudentMobility $studentMobility): RedirectResponse
    {
        $studentMobility->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('student-mobilities.index')->with('success', 'Movilidad estudiantil actualizada correctamente.');
    }

    public function destroy(StudentMobility $studentMobility): RedirectResponse
    {
        $studentMobility->delete();

        return back()->with('success', 'Movilidad estudiantil eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'destination_university' => ['required', 'string', 'max:180'],
            'call_type' => ['required', 'string', 'max:100'],
            'is_interareas_view' => ['nullable', 'boolean'],
            'orni_registration_code' => ['nullable', 'string', 'max:80'],
            'fee_exemption' => ['nullable', 'boolean'],
            'subvention_status' => ['nullable', 'string', 'max:80'],
            'convalidation_resolution_number' => ['nullable', 'string', 'max:80'],
            'equivalent_grades_data' => ['nullable', 'json'],
            'status' => ['required', Rule::in(['postulado', 'seleccionado', 'en_movilidad', 'convalidado', 'cerrado'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['is_interareas_view'] = (bool) ($validated['is_interareas_view'] ?? false);
        $validated['fee_exemption'] = (bool) ($validated['fee_exemption'] ?? false);
        $validated['equivalent_grades_data'] = ! empty($validated['equivalent_grades_data']) ? json_decode($validated['equivalent_grades_data'], true) : null;

        return $validated;
    }
}
