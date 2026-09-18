<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseExecutionReport;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\IndicadoresCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseExecutionReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.course-execution-reports.index', [
            'reports' => CourseExecutionReport::query()->with(['course', 'teacher'])->latest('id')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'programas' => ProgramaEstudio::query()->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => PeriodoAcademico::query()->where('activo', true)->orderByDesc('codigo')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? CourseExecutionReport::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $report = CourseExecutionReport::create($this->prepare($request->validate($this->rules($request)), $request));
        app(IndicadoresCacheService::class)->clear($report->programa_estudio_id, $report->academic_period);

        return redirect()->route('course-execution-reports.index')->with('success', 'Informe de ejecución registrado correctamente y reflejado en el indicador de avance silábico.');
    }

    public function update(Request $request, CourseExecutionReport $courseExecutionReport): RedirectResponse
    {
        $courseExecutionReport->update($this->prepare($request->validate($this->rules($request)), $request));
        app(IndicadoresCacheService::class)->clear($courseExecutionReport->programa_estudio_id, $courseExecutionReport->academic_period);

        return redirect()->route('course-execution-reports.index')->with('success', 'Informe de ejecución actualizado correctamente.');
    }

    public function destroy(CourseExecutionReport $courseExecutionReport): RedirectResponse
    {
        $programaId = $courseExecutionReport->programa_estudio_id;
        $periodo = $courseExecutionReport->academic_period;
        $courseExecutionReport->delete();
        app(IndicadoresCacheService::class)->clear($programaId, $periodo);

        return back()->with('success', 'Informe de ejecución eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'socialization_format' => ['required', 'string', 'max:80'],
            'status' => ['required', Rule::in(['BORRADOR', 'CONSOLIDADO', 'APROBADO', 'ANULADO'])],
            'porcentaje_avance' => ['nullable', 'numeric', 'between:0,100'],
            'execution_summary_data' => ['nullable'],
            'portafolio_digital_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated, Request $request): array
    {
        $validated['academic_period'] = PeriodoAcademico::query()->findOrFail($validated['periodo_academico_id'])->codigo;

        $summaryData = [];
        if (! empty($validated['execution_summary_data']) && is_string($validated['execution_summary_data'])) {
            $decoded = json_decode($validated['execution_summary_data'], true);
            if (is_array($decoded)) {
                $summaryData = $decoded;
            }
        } elseif (is_array($validated['execution_summary_data'] ?? null)) {
            $summaryData = $validated['execution_summary_data'];
        }

        if ($request->filled('porcentaje_avance')) {
            $summaryData['porcentaje_avance'] = (float) $request->input('porcentaje_avance');
        }

        $validated['execution_summary_data'] = $summaryData ?: null;
        unset($validated['porcentaje_avance']);

        return $validated;
    }
}
