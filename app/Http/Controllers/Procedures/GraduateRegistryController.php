<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\GraduateRegistry;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\IndicadoresCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GraduateRegistryController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.graduate-registries.index', [
            'registries' => GraduateRegistry::query()->with('student')->latest('id')->get(),
            'programas' => ProgramaEstudio::query()->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => PeriodoAcademico::query()->where('activo', true)->orderByDesc('codigo')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? GraduateRegistry::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $prepared = $this->prepare($request->validate($this->rules()));
        $registry = GraduateRegistry::create($prepared);
        $periodo = PeriodoAcademico::find($registry->periodo_academico_id)?->codigo;
        if ($periodo) {
            app(IndicadoresCacheService::class)->clear($registry->programa_estudio_id, $periodo);
        }

        return redirect()->route('graduate-registries.index')->with('success', 'Registro de egresado creado correctamente y reflejado en el Dashboard de Calidad.');
    }

    public function update(Request $request, GraduateRegistry $graduateRegistry): RedirectResponse
    {
        $prepared = $this->prepare($request->validate($this->rules()));
        $graduateRegistry->update($prepared);
        $periodo = PeriodoAcademico::find($graduateRegistry->periodo_academico_id)?->codigo;
        if ($periodo) {
            app(IndicadoresCacheService::class)->clear($graduateRegistry->programa_estudio_id, $periodo);
        }

        return redirect()->route('graduate-registries.index')->with('success', 'Registro de egresado actualizado correctamente.');
    }

    public function destroy(GraduateRegistry $graduateRegistry): RedirectResponse
    {
        $programaId = $graduateRegistry->programa_estudio_id;
        $periodo = PeriodoAcademico::find($graduateRegistry->periodo_academico_id)?->codigo;
        $graduateRegistry->delete();
        if ($periodo) {
            app(IndicadoresCacheService::class)->clear($programaId, $periodo);
        }

        return back()->with('success', 'Registro de egresado eliminado.');
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
            'apt_list_number' => ['nullable', 'string', 'max:80'],
            'titulado' => ['nullable', 'boolean'],
            'condicion_laboral' => ['nullable', Rule::in(['EMPLEADO', 'OTRA_AREA', 'BUSCANDO', 'DESEMPLEADO', 'SIN_INFORMACION'])],
            'labora_especialidad' => ['nullable', 'boolean'],
            'update_form_data' => ['nullable', 'json'],
            'database_record' => ['nullable', 'json'],
            'annual_stats_report' => ['nullable', 'json'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['titulado'] = (bool) ($validated['titulado'] ?? false);
        $validated['labora_especialidad'] = (bool) ($validated['labora_especialidad'] ?? false);
        foreach (['update_form_data', 'database_record', 'annual_stats_report'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }

        return $validated;
    }
}
