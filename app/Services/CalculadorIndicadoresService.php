<?php

namespace App\Services;

use App\Models\CourseExecutionReport;
use App\Models\GraduateRegistry;
use App\Models\IncidenciaMatricula;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\StudentReferral;
use App\Models\Syllabus;
use App\Models\TutoringSession;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CalculadorIndicadoresService
{
    public const CODIGO_SILABOS = 'I-M01.01-DPA-004';

    public const CODIGO_RETENCION = 'M01.01.02.02-FI-001';

    public const CODIGO_REPITENCIA = 'M01.01.02.02-FI-002';

    public const CODIGO_INCIDENCIAS = 'M01.01.02.02-FI-003';

    public const CODIGO_AVANCE = 'M01.01.03.01-F-013';

    public const CODIGO_TUTORIA = 'M01.04-DDA-FI-001';

    public const CODIGO_TITULADOS = 'M01.05-DCU-FI-001';

    public const CODIGO_INSERCION = 'M01.05-DCU-FI-002';

    public function __construct(
        private EvaluadorCumplimientoIndicadorService $evaluador,
        private IndicadoresCacheService $cache,
    ) {}

    /** @return array<string, float|null> */
    public function calcularValores(ProgramaEstudio $programaEstudio, string $periodoAcademico): array
    {
        return Cache::remember(
            $this->cache->key($programaEstudio->id, $periodoAcademico),
            now()->addMinutes(5),
            fn (): array => $this->calcularValoresSinCache($programaEstudio, $periodoAcademico),
        );
    }

    /** @return Collection<int, IndicadorMedicion> */
    public function calcularActuales(ProgramaEstudio $programaEstudio, string $periodoAcademico): Collection
    {
        $valores = $this->calcularValores($programaEstudio, $periodoAcademico);
        $existentes = IndicadorMedicion::query()
            ->whereBelongsTo($programaEstudio)
            ->where('periodo_academico', $periodoAcademico)
            ->with('accionesMejora')
            ->get()
            ->keyBy('indicador_id');

        return IndicadorMaestro::query()->orderBy('codigo')->get()
            ->map(function (IndicadorMaestro $indicador) use ($existentes, $periodoAcademico, $programaEstudio, $valores): ?IndicadorMedicion {
                $valor = $valores[$indicador->codigo] ?? null;

                if ($valor === null) {
                    return null;
                }

                $medicion = $existentes->get($indicador->id) ?? new IndicadorMedicion([
                    'indicador_id' => $indicador->id,
                    'programa_estudio_id' => $programaEstudio->id,
                    'periodo_academico' => $periodoAcademico,
                ]);

                if ($medicion->consolidada_en === null) {
                    $medicion->forceFill([
                        'valor_medido' => $valor,
                        'meta_programada' => $indicador->meta_institucional,
                        'estado_cumplimiento' => $this->evaluador->evaluar($indicador, $valor),
                    ]);
                }

                $medicion->setRelation('indicador', $indicador);

                return $medicion;
            })->filter()->values();
    }

    /** @return Collection<int, IndicadorMedicion> */
    public function consolidarPeriodo(ProgramaEstudio $programaEstudio, string $periodoAcademico, ?int $registradoPor = null): Collection
    {
        return DB::transaction(function () use ($periodoAcademico, $programaEstudio, $registradoPor): Collection {
            $periodo = PeriodoAcademico::query()->where('codigo', $periodoAcademico)->first();

            return $this->calcularActuales($programaEstudio, $periodoAcademico)
                ->map(function (IndicadorMedicion $actual) use ($periodo, $registradoPor): IndicadorMedicion {
                    $medicion = IndicadorMedicion::query()->lockForUpdate()->firstOrNew([
                        'indicador_id' => $actual->indicador_id,
                        'programa_estudio_id' => $actual->programa_estudio_id,
                        'periodo_academico' => $actual->periodo_academico,
                    ]);

                    if ($medicion->exists && $medicion->consolidada_en !== null) {
                        return $medicion->load('indicador');
                    }

                    $medicion->fill([
                        'periodo_academico_id' => $periodo?->id,
                        'valor_medido' => $actual->valor_medido,
                        'meta_programada' => $actual->meta_programada,
                        'estado_cumplimiento' => $actual->estado_cumplimiento,
                        'registrado_por' => $registradoPor,
                        'consolidada_en' => now(),
                        'datos_fuente' => ['calculado_en' => now()->toIso8601String()],
                    ])->save();

                    return $medicion->load('indicador');
                });
        });
    }

    /** @return Collection<int, IndicadorMedicion> */
    public function calcularYConsolidar(ProgramaEstudio $programaEstudio, string $periodoAcademico, ?int $registradoPor = null): Collection
    {
        return $this->consolidarPeriodo($programaEstudio, $periodoAcademico, $registradoPor);
    }

    public function obtenerOCrearMedicionActual(IndicadorMaestro $indicador, ProgramaEstudio $programaEstudio, string $periodoAcademico, ?int $registradoPor): IndicadorMedicion
    {
        $existente = IndicadorMedicion::query()->where([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programaEstudio->id,
            'periodo_academico' => $periodoAcademico,
        ])->first();
        $valor = $this->calcularValores($programaEstudio, $periodoAcademico)[$indicador->codigo] ?? null;

        if ($valor === null) {
            if ($existente !== null) {
                return $existente->load('indicador');
            }

            throw new DomainException('El indicador no tiene datos suficientes para este periodo.');
        }

        $medicion = $existente ?? IndicadorMedicion::query()->firstOrNew([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programaEstudio->id,
            'periodo_academico' => $periodoAcademico,
        ]);

        if ($medicion->consolidada_en === null) {
            $medicion->fill([
                'periodo_academico_id' => PeriodoAcademico::query()->where('codigo', $periodoAcademico)->value('id'),
                'valor_medido' => $valor,
                'meta_programada' => $indicador->meta_institucional,
                'estado_cumplimiento' => $this->evaluador->evaluar($indicador, $valor),
                'registrado_por' => $registradoPor,
            ])->save();
        }

        return $medicion->load('indicador');
    }

    /** @return array{labels: list<string>, aprobados: list<int>, desaprobados: list<int>, inhabilitados: list<int>} */
    public function datosAprobacionPorCiclo(ProgramaEstudio $programaEstudio, string $periodoAcademico, ?int $cicloAcademico = null, ?int $cursoId = null): array
    {
        $filas = $this->matriculasConfirmadas($programaEstudio, $periodoAcademico)
            ->when($cicloAcademico, fn (Builder $query) => $query->where('ciclo_academico', $cicloAcademico))
            ->when($cursoId, fn (Builder $query) => $query->where('curso_id', $cursoId))
            ->toBase()
            ->select('ciclo_academico')
            ->selectRaw("SUM(CASE WHEN estado_resultado = 'APROBADO' THEN 1 ELSE 0 END) AS aprobados")
            ->selectRaw("SUM(CASE WHEN estado_resultado = 'DESAPROBADO' THEN 1 ELSE 0 END) AS desaprobados")
            ->selectRaw("SUM(CASE WHEN estado_resultado = 'INHABILITADO' THEN 1 ELSE 0 END) AS inhabilitados")
            ->groupBy('ciclo_academico')
            ->orderBy('ciclo_academico')
            ->get();

        return [
            'labels' => $filas->map(fn (object $fila): string => 'Ciclo '.$fila->ciclo_academico)->values()->all(),
            'aprobados' => $filas->map(fn (object $fila): int => (int) $fila->aprobados)->values()->all(),
            'desaprobados' => $filas->map(fn (object $fila): int => (int) $fila->desaprobados)->values()->all(),
            'inhabilitados' => $filas->map(fn (object $fila): int => (int) $fila->inhabilitados)->values()->all(),
        ];
    }

    /** @return array{labels: list<string>, retencion: list<float|null>, repitencia: list<float|null>} */
    public function datosHistoricos(ProgramaEstudio $programaEstudio): array
    {
        $mediciones = IndicadorMedicion::query()
            ->whereBelongsTo($programaEstudio)
            ->whereNotNull('consolidada_en')
            ->whereHas('indicador', fn ($query) => $query->whereIn('codigo', [self::CODIGO_RETENCION, self::CODIGO_REPITENCIA]))
            ->with('indicador:id,codigo')
            ->orderBy('periodo_academico')
            ->get(['id', 'indicador_id', 'programa_estudio_id', 'periodo_academico', 'valor_medido']);
        $periodos = $mediciones->pluck('periodo_academico')->unique()->sort()->values();
        $porCodigo = $mediciones->groupBy(fn (IndicadorMedicion $medicion): string => $medicion->indicador->codigo);

        return [
            'labels' => $periodos->all(),
            'retencion' => $this->seriePorPeriodo($periodos, $porCodigo->get(self::CODIGO_RETENCION, collect())),
            'repitencia' => $this->seriePorPeriodo($periodos, $porCodigo->get(self::CODIGO_REPITENCIA, collect())),
        ];
    }

    /** @return array{labels: list<string>, valores: list<float|null>} */
    public function datosHistoricoIndicador(ProgramaEstudio $programaEstudio, IndicadorMaestro $indicador): array
    {
        $mediciones = IndicadorMedicion::query()
            ->whereBelongsTo($programaEstudio)
            ->where('indicador_id', $indicador->id)
            ->get(['periodo_academico', 'valor_medido'])
            ->keyBy('periodo_academico');

        $periodos = PeriodoAcademico::query()->orderBy('codigo')->pluck('codigo')
            ->merge($mediciones->keys())
            ->filter(fn (mixed $periodo): bool => is_string($periodo) && preg_match('/^\d{4}-(I|II)$/', $periodo) === 1 && $periodo <= $this->periodoActual())
            ->unique()->sort()->values();

        return [
            'labels' => $periodos->all(),
            'valores' => $periodos->map(fn (string $periodo): ?float => ($m = $mediciones->get($periodo)) ? (float) $m->valor_medido : null)->all(),
        ];
    }

    /** @return array{labels: list<string>, valores: list<int>} */
    public function datosCondicionLaboral(ProgramaEstudio $programaEstudio, string $periodoAcademico): array
    {
        $conteos = $this->registrosEgresadosDelPeriodo($programaEstudio, $periodoAcademico)
            ->countBy(fn (GraduateRegistry $registro): string => $this->condicionLaboral($registro));

        return [
            'labels' => ['En su especialidad', 'En otra area', 'Buscando empleo', 'Sin informacion'],
            'valores' => [$conteos->get('ESPECIALIDAD', 0), $conteos->get('OTRA_AREA', 0), $conteos->get('BUSCANDO', 0), $conteos->get('SIN_INFORMACION', 0)],
        ];
    }

    /** @return array{programadas: int, realizadas: int, derivaciones: int} */
    public function resumenTutoria(ProgramaEstudio $programaEstudio, string $periodoAcademico): array
    {
        $periodoId = PeriodoAcademico::query()->where('codigo', $periodoAcademico)->value('id');
        $sesiones = TutoringSession::query()->whereBelongsTo($programaEstudio)
            ->when($periodoId, fn ($query) => $query->where('periodo_academico_id', $periodoId), fn ($query) => $query->whereRaw('1 = 0'));

        return [
            'programadas' => (clone $sesiones)->whereIn('status', ['programada', 'realizada', 'completada'])->count(),
            'realizadas' => (clone $sesiones)->whereIn('status', ['realizada', 'completada'])->count(),
            'derivaciones' => StudentReferral::query()->whereBelongsTo($programaEstudio)
                ->when($periodoId, fn ($query) => $query->where('periodo_academico_id', $periodoId), fn ($query) => $query->whereRaw('1 = 0'))->count(),
        ];
    }

    /** @return array{titulados: float|null, laborando: float|null, especialidad: float|null} */
    public function resumenEgresados(ProgramaEstudio $programaEstudio, string $periodoAcademico): array
    {
        $registros = $this->registrosEgresadosDelPeriodo($programaEstudio, $periodoAcademico);

        return [
            'titulados' => $this->porcentaje($registros->where('titulado', true)->count(), $registros->count()),
            'laborando' => $this->porcentaje($registros->filter(fn (GraduateRegistry $registro): bool => in_array($this->condicionLaboral($registro), ['ESPECIALIDAD', 'OTRA_AREA'], true))->count(), $registros->count()),
            'especialidad' => $this->porcentaje($registros->filter(fn (GraduateRegistry $registro): bool => $this->condicionLaboral($registro) === 'ESPECIALIDAD')->count(), $registros->count()),
        ];
    }

    /** @return array<string, float|null> */
    private function calcularValoresSinCache(ProgramaEstudio $programaEstudio, string $periodoAcademico): array
    {
        $egresados = $this->resumenEgresados($programaEstudio, $periodoAcademico);

        return [
            self::CODIGO_SILABOS => $this->porcentajeSilabosVisados($programaEstudio, $periodoAcademico),
            self::CODIGO_RETENCION => $this->tasaRetencion($programaEstudio, $periodoAcademico),
            self::CODIGO_REPITENCIA => $this->porcentajeRepitencia($programaEstudio, $periodoAcademico),
            self::CODIGO_INCIDENCIAS => $this->resolucionIncidencias($programaEstudio, $periodoAcademico),
            self::CODIGO_AVANCE => $this->avanceSilabico($programaEstudio, $periodoAcademico),
            self::CODIGO_TUTORIA => $this->eficaciaTutoria($programaEstudio, $periodoAcademico),
            self::CODIGO_TITULADOS => $egresados['titulados'],
            self::CODIGO_INSERCION => $egresados['laborando'],
        ];
    }

    private function porcentajeSilabosVisados(ProgramaEstudio $programaEstudio, string $periodoAcademico): ?float
    {
        $cursos = $this->matriculasConfirmadas($programaEstudio, $periodoAcademico)->distinct()->pluck('curso_id');

        if ($cursos->isEmpty()) {
            return null;
        }

        $visados = Syllabus::query()->whereBelongsTo($programaEstudio)->where('academic_period', $periodoAcademico)
            ->whereIn('status', ['visado', 'aprobado'])->whereIn('course_id', $cursos)->distinct('course_id')->count('course_id');

        return $this->porcentaje($visados, $cursos->count());
    }

    private function tasaRetencion(ProgramaEstudio $programaEstudio, string $periodoAcademico): ?float
    {
        $actuales = $this->matriculasConfirmadas($programaEstudio, $periodoAcademico)->distinct('estudiante_id')->count('estudiante_id');
        $anteriores = $this->matriculasConfirmadas($programaEstudio, $this->periodoAnterior($periodoAcademico))->distinct('estudiante_id')->count('estudiante_id');

        return $this->porcentaje($actuales, $anteriores);
    }

    private function porcentajeRepitencia(ProgramaEstudio $programaEstudio, string $periodoAcademico): ?float
    {
        $matriculas = $this->matriculasConfirmadas($programaEstudio, $periodoAcademico);
        $total = (clone $matriculas)->count();

        return $this->porcentaje((clone $matriculas)->where('numero_matricula', '>=', 2)->count(), $total);
    }

    private function resolucionIncidencias(ProgramaEstudio $programaEstudio, string $periodoAcademico): ?float
    {
        $incidencias = IncidenciaMatricula::query()->whereBelongsTo($programaEstudio)->where('periodo_academico', $periodoAcademico);

        return $this->porcentaje((clone $incidencias)->whereIn('estado', ['RESUELTA', 'CERRADA'])->count(), (clone $incidencias)->count());
    }

    private function avanceSilabico(ProgramaEstudio $programaEstudio, string $periodoAcademico): ?float
    {
        $indicador = IndicadorMaestro::query()->where('codigo', self::CODIGO_AVANCE)->first();
        $formatos = Arr::get($indicador?->configuracion ?? [], 'formatos_fuente', []);
        $campo = Arr::get($indicador?->configuracion ?? [], 'campo_porcentaje', 'porcentaje_avance');
        $periodoId = PeriodoAcademico::query()->where('codigo', $periodoAcademico)->value('id');
        $informes = CourseExecutionReport::query()->whereBelongsTo($programaEstudio)
            ->whereIn('status', ['CONSOLIDADO', 'APROBADO'])
            ->when($periodoId, fn ($query) => $query->where('periodo_academico_id', $periodoId), fn ($query) => $query->where('academic_period', $periodoAcademico))
            ->when($formatos !== [], fn ($query) => $query->whereIn('socialization_format', $formatos))
            ->latest('id')->get(['id', 'course_id', 'execution_summary_data'])->unique('course_id');
        $valores = $informes->map(fn (CourseExecutionReport $informe): mixed => Arr::get($informe->execution_summary_data, $campo))
            ->filter(fn (mixed $valor): bool => is_numeric($valor));

        return $valores->isEmpty() ? null : round((float) $valores->average(), 2);
    }

    private function eficaciaTutoria(ProgramaEstudio $programaEstudio, string $periodoAcademico): ?float
    {
        $resumen = $this->resumenTutoria($programaEstudio, $periodoAcademico);

        return $this->porcentaje($resumen['realizadas'], $resumen['programadas']);
    }

    /** @return Collection<int, GraduateRegistry> */
    private function registrosEgresadosDelPeriodo(ProgramaEstudio $programaEstudio, string $periodoAcademico): Collection
    {
        $periodoId = PeriodoAcademico::query()->where('codigo', $periodoAcademico)->value('id');

        return $periodoId === null ? collect() : GraduateRegistry::query()->whereBelongsTo($programaEstudio)->where('periodo_academico_id', $periodoId)->get();
    }

    private function condicionLaboral(GraduateRegistry $registro): string
    {
        if ($registro->labora_especialidad === true) {
            return 'ESPECIALIDAD';
        }

        $condicion = mb_strtoupper((string) ($registro->condicion_laboral ?? Arr::get($registro->annual_stats_report ?? [], 'condicion_laboral')));

        return match ($condicion) {
            'EMPLEADO', 'OTRA_AREA', 'OTRA AREA' => 'OTRA_AREA',
            'BUSCANDO', 'DESEMPLEADO' => 'BUSCANDO',
            default => 'SIN_INFORMACION',
        };
    }

    private function matriculasConfirmadas(ProgramaEstudio $programaEstudio, string $periodoAcademico): Builder
    {
        return Matricula::query()->whereBelongsTo($programaEstudio)->where('periodo_academico', $periodoAcademico)->where('estado_matricula', 'CONFIRMADA');
    }

    /**
     * @param  Collection<int, string>  $periodos
     * @param  Collection<int, IndicadorMedicion>  $mediciones
     * @return list<float|null>
     */
    private function seriePorPeriodo(Collection $periodos, Collection $mediciones): array
    {
        $valores = $mediciones->keyBy('periodo_academico');

        return $periodos->map(fn (string $periodo): ?float => ($medicion = $valores->get($periodo)) ? (float) $medicion->valor_medido : null)->all();
    }

    private function porcentaje(int $numerador, int $denominador): ?float
    {
        return $denominador === 0 ? null : round(($numerador / $denominador) * 100, 2);
    }

    private function periodoActual(): string
    {
        return now()->format('Y').(now()->month <= 6 ? '-I' : '-II');
    }

    private function periodoAnterior(string $periodoAcademico): string
    {
        if (preg_match('/^(\d{4})-(I|II)$/', $periodoAcademico, $partes) !== 1) {
            throw new DomainException('Periodo academico invalido. Use YYYY-I o YYYY-II.');
        }

        return $partes[2] === 'II' ? $partes[1].'-I' : ((int) $partes[1] - 1).'-II';
    }
}
