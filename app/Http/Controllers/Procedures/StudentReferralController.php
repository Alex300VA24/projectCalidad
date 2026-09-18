<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\StudentReferral;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentReferralController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.student-referrals.index', [
            'referrals' => StudentReferral::query()->with(['student', 'referrer'])->latest('id')->get(),
            'programas' => ProgramaEstudio::query()->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => PeriodoAcademico::query()->where('activo', true)->orderByDesc('codigo')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? StudentReferral::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        StudentReferral::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('student-referrals.index')->with('success', 'Derivación registrada correctamente.');
    }

    public function update(Request $request, StudentReferral $studentReferral): RedirectResponse
    {
        $studentReferral->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('student-referrals.index')->with('success', 'Derivación actualizada correctamente.');
    }

    public function destroy(StudentReferral $studentReferral): RedirectResponse
    {
        $studentReferral->delete();

        return back()->with('success', 'Derivación eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'referrer_id' => ['required', 'integer', 'exists:users,id'],
            'referred_to' => ['required', Rule::in([
                StudentReferral::REFERRED_TO_BIENESTAR,
                StudentReferral::REFERRED_TO_PSICOLOGIA,
                StudentReferral::REFERRED_TO_SOCIAL,
            ])],
            'referral_sheet' => ['nullable', 'json'],
            'status' => ['required', Rule::in(['derivado', 'en_atencion', 'contrarreferido', 'cerrado'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['referral_sheet'] = ! empty($validated['referral_sheet']) ? json_decode($validated['referral_sheet'], true) : null;

        return $validated;
    }
}
