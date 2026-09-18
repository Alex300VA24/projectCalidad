<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\IncidenciaMatricula;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\IndicadoresCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatriculaController extends Controller
{
    public function __construct(
        private IndicadoresCacheService $cacheService,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'matriculas')->toString();
        $periodoSeleccionado = $request->string('periodo', '')->trim()->toString();

        $matriculasQuery = Matricula::query()->with(['estudiante', 'curso', 'programaEstudio'])->latest('id');
        $incidenciasQuery = IncidenciaMatricula::query()->with(['programaEstudio'])->latest('id');

        if ($periodoSeleccionado !== '') {
            $matriculasQuery->where('periodo_academico', $periodoSeleccionado);
            $incidenciasQuery->where('periodo_academico', $periodoSeleccionado);
        }

        $editingMatricula = $request->integer('edit_matricula') ? Matricula::find($request->integer('edit_matricula')) : null;
        $editingIncidencia = $request->integer('edit_incidencia') ? IncidenciaMatricula::find($request->integer('edit_incidencia')) : null;

        return view('tramites.matriculas.index', [
            'tab' => $tab,
            'periodoSeleccionado' => $periodoSeleccionado,
            'matriculas' => $matriculasQuery->paginate(20, ['*'], 'mat_page')->withQueryString(),
            'incidencias' => $incidenciasQuery->paginate(20, ['*'], 'inc_page')->withQueryString(),
            'editingMatricula' => $editingMatricula,
            'editingIncidencia' => $editingIncidencia,
            'programas' => ProgramaEstudio::query()->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => PeriodoAcademico::query()->orderByDesc('codigo')->get(),
            'cursos' => Course::query()->orderBy('name')->get(['id', 'name', 'code']),
            'estudiantes' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'conteoMatriculas' => Matricula::count(),
            'conteoIncidencias' => IncidenciaMatricula::count(),
        ]);
    }

    public function storeMatricula(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'estudiante_id' => ['required', 'integer', 'exists:users,id'],
            'curso_id' => ['required', 'integer', 'exists:courses,id'],
            'periodo_academico' => ['required', 'string', 'max:10', 'regex:/^\d{4}-(I|II)$/'],
            'ciclo_academico' => ['required', 'integer', 'between:1,12'],
            'numero_matricula' => ['required', 'integer', 'min:1'],
            'estado_matricula' => ['required', Rule::in(['CONFIRMADA', 'PENDIENTE', 'ANULADA'])],
            'estado_resultado' => ['required', Rule::in(['CURSANDO', 'APROBADO', 'DESAPROBADO', 'INHABILITADO'])],
        ]);

        $matricula = Matricula::create($validated);
        $this->cacheService->clear($matricula->programa_estudio_id, $matricula->periodo_academico);

        return redirect()->route('matriculas.index', ['tab' => 'matriculas', 'periodo' => $matricula->periodo_academico])
            ->with('success', 'Matrícula registrada correctamente y reflejada en indicadores.');
    }

    public function updateMatricula(Request $request, Matricula $matricula): RedirectResponse
    {
        $validated = $request->validate([
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'estudiante_id' => ['required', 'integer', 'exists:users,id'],
            'curso_id' => ['required', 'integer', 'exists:courses,id'],
            'periodo_academico' => ['required', 'string', 'max:10', 'regex:/^\d{4}-(I|II)$/'],
            'ciclo_academico' => ['required', 'integer', 'between:1,12'],
            'numero_matricula' => ['required', 'integer', 'min:1'],
            'estado_matricula' => ['required', Rule::in(['CONFIRMADA', 'PENDIENTE', 'ANULADA'])],
            'estado_resultado' => ['required', Rule::in(['CURSANDO', 'APROBADO', 'DESAPROBADO', 'INHABILITADO'])],
        ]);

        $matricula->update($validated);
        $this->cacheService->clear($matricula->programa_estudio_id, $matricula->periodo_academico);

        return redirect()->route('matriculas.index', ['tab' => 'matriculas', 'periodo' => $matricula->periodo_academico])
            ->with('success', 'Matrícula actualizada correctamente.');
    }

    public function destroyMatricula(Matricula $matricula): RedirectResponse
    {
        $programaId = $matricula->programa_estudio_id;
        $periodo = $matricula->periodo_academico;
        $matricula->delete();
        $this->cacheService->clear($programaId, $periodo);

        return back()->with('success', 'Registro de matrícula eliminado.');
    }

    public function storeIncidencia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'periodo_academico' => ['required', 'string', 'max:10', 'regex:/^\d{4}-(I|II)$/'],
            'descripcion' => ['required', 'string', 'min:5', 'max:1000'],
            'estado' => ['required', Rule::in(['REPORTADA', 'EN_ATENCION', 'RESUELTA', 'CERRADA'])],
        ]);

        if (in_array($validated['estado'], ['RESUELTA', 'CERRADA'], true)) {
            $validated['resuelta_en'] = now();
        }

        $incidencia = IncidenciaMatricula::create($validated);
        $this->cacheService->clear($incidencia->programa_estudio_id, $incidencia->periodo_academico);

        return redirect()->route('matriculas.index', ['tab' => 'incidencias', 'periodo' => $incidencia->periodo_academico])
            ->with('success', 'Incidencia de matrícula registrada correctamente y calculada en el indicador FI-003.');
    }

    public function updateIncidencia(Request $request, IncidenciaMatricula $incidencia): RedirectResponse
    {
        $validated = $request->validate([
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'periodo_academico' => ['required', 'string', 'max:10', 'regex:/^\d{4}-(I|II)$/'],
            'descripcion' => ['required', 'string', 'min:5', 'max:1000'],
            'estado' => ['required', Rule::in(['REPORTADA', 'EN_ATENCION', 'RESUELTA', 'CERRADA'])],
        ]);

        if (in_array($validated['estado'], ['RESUELTA', 'CERRADA'], true) && $incidencia->resuelta_en === null) {
            $validated['resuelta_en'] = now();
        }

        $incidencia->update($validated);
        $this->cacheService->clear($incidencia->programa_estudio_id, $incidencia->periodo_academico);

        return redirect()->route('matriculas.index', ['tab' => 'incidencias', 'periodo' => $incidencia->periodo_academico])
            ->with('success', 'Incidencia actualizada.');
    }

    public function destroyIncidencia(IncidenciaMatricula $incidencia): RedirectResponse
    {
        $programaId = $incidencia->programa_estudio_id;
        $periodo = $incidencia->periodo_academico;
        $incidencia->delete();
        $this->cacheService->clear($programaId, $periodo);

        return back()->with('success', 'Incidencia eliminada.');
    }
}
