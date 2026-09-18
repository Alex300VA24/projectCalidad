<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\TutoringSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TutoringSessionController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.tutoring-sessions.index', [
            'sessions' => TutoringSession::query()->with(['student', 'tutor'])->latest('id')->get(),
            'programas' => ProgramaEstudio::query()->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => PeriodoAcademico::query()->where('activo', true)->orderByDesc('codigo')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? TutoringSession::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TutoringSession::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('tutoring-sessions.index')->with('success', 'Sesión de tutoría registrada correctamente.');
    }

    public function update(Request $request, TutoringSession $tutoringSession): RedirectResponse
    {
        $tutoringSession->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('tutoring-sessions.index')->with('success', 'Sesión de tutoría actualizada correctamente.');
    }

    public function destroy(TutoringSession $tutoringSession): RedirectResponse
    {
        $tutoringSession->delete();

        return back()->with('success', 'Sesión de tutoría eliminada.');
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
            'tutor_id' => ['required', 'integer', 'exists:users,id'],
            'plan_id' => ['nullable', 'string', 'max:100'],
            'session_date' => ['required', 'date'],
            'attention_registry' => ['nullable', 'json'],
            'status' => ['required', Rule::in(['programada', 'realizada', 'cancelada'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['attention_registry'] = ! empty($validated['attention_registry']) ? json_decode($validated['attention_registry'], true) : null;

        return $validated;
    }
}
