<?php

namespace App\Livewire\Indicadores;

use App\Models\AccionMejoraIndicador;
use App\Models\Document;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\Syllabus;
use App\Services\CalculadorIndicadoresService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DashboardCalidad extends Component
{
    public int $programaEstudioId = 0;

    public string $periodoAcademico = '';

    public ?int $medicionEditandoId = null;

    public string $analisisCausas = '';

    public string $accionesMejora = '';

    public ?int $responsableId = null;

    public string $fechaLimite = '';

    public string $evidenciaUrl = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', IndicadorMedicion::class);
        $this->programaEstudioId = ProgramaEstudio::query()->where('activo', true)->value('id') ?? 0;
        $this->periodoAcademico = $this->periodosDisponibles()->first() ?? $this->periodoActual();
    }

    public function updatedProgramaEstudioId(): void
    {
        $this->cerrarPlan();
    }

    public function updatedPeriodoAcademico(): void
    {
        $this->cerrarPlan();
    }

    public function editarPlan(int $indicadorId, CalculadorIndicadoresService $calculador): void
    {
        $programa = ProgramaEstudio::query()->findOrFail($this->programaEstudioId);
        $indicador = IndicadorMaestro::query()->findOrFail($indicadorId);
        $medicion = $calculador->obtenerOCrearMedicionActual($indicador, $programa, $this->periodoAcademico, auth()->id());
        Gate::authorize('update', $medicion);

        abort_unless(in_array($medicion->estado_cumplimiento, ['OBSERVADO', 'CRITICO', 'NO_CONFORME'], true), 404);

        $accion = $medicion->accionesMejora()->latest('id')->first();
        $this->medicionEditandoId = $medicion->id;
        $this->analisisCausas = $medicion->analisis_causas ?? '';
        $this->accionesMejora = $accion?->descripcion ?? $medicion->acciones_mejora ?? '';
        $this->responsableId = $accion?->responsable_id;
        $this->fechaLimite = $accion?->fecha_limite?->format('Y-m-d') ?? '';
        $this->evidenciaUrl = $accion?->evidencia_url ?? '';
    }

    public function guardarPlan(): void
    {
        $medicion = $this->medicionEditable((int) $this->medicionEditandoId);
        Gate::authorize('update', $medicion);
        $requiereAccion = $medicion->estado_cumplimiento === 'CRITICO' || trim($this->accionesMejora) !== '';
        $validated = $this->validate([
            'analisisCausas' => ['required', 'string', 'min:10', 'max:3000'],
            'accionesMejora' => [Rule::requiredIf($medicion->estado_cumplimiento === 'CRITICO'), 'nullable', 'string', 'min:10', 'max:3000'],
            'responsableId' => [Rule::requiredIf($requiereAccion), 'nullable', 'integer', 'exists:users,id'],
            'fechaLimite' => [Rule::requiredIf($requiereAccion), 'nullable', 'date'],
            'evidenciaUrl' => ['nullable', 'url', 'max:255'],
        ]);

        $medicion->update([
            'analisis_causas' => $validated['analisisCausas'],
            'acciones_mejora' => $validated['accionesMejora'] ?: null,
            'registrado_por' => auth()->id(),
        ]);

        if ($requiereAccion) {
            AccionMejoraIndicador::query()->updateOrCreate(
                ['indicador_medicion_id' => $medicion->id, 'estado' => 'PENDIENTE'],
                [
                    'descripcion' => $validated['accionesMejora'],
                    'responsable_id' => $validated['responsableId'],
                    'fecha_limite' => $validated['fechaLimite'],
                    'evidencia_url' => $validated['evidenciaUrl'] ?: null,
                ],
            );
        }

        $this->cerrarPlan();
        session()->flash('quality-success', 'Analisis y plan de mejora guardados.');
    }

    public function consolidar(CalculadorIndicadoresService $calculador): void
    {
        Gate::authorize('consolidate', IndicadorMedicion::class);
        $programa = ProgramaEstudio::query()->findOrFail($this->programaEstudioId);
        $calculador->consolidarPeriodo($programa, $this->periodoAcademico, auth()->id());
        session()->flash('quality-success', 'Periodo consolidado. El historico queda bloqueado.');
    }

    public function cerrarPlan(): void
    {
        $this->reset(['medicionEditandoId', 'analisisCausas', 'accionesMejora', 'responsableId', 'fechaLimite', 'evidenciaUrl']);
        $this->resetValidation();
    }

    public function render(CalculadorIndicadoresService $calculador): View
    {
        $programa = ProgramaEstudio::query()->find($this->programaEstudioId);

        if ($programa === null) {
            return view('livewire.indicadores.dashboard-calidad', $this->datosVacios());
        }

        $mediciones = $calculador->calcularActuales($programa, $this->periodoAcademico);
        $porIndicador = $mediciones->keyBy('indicador_id');
        $documentosIndicadores = Document::query()
            ->where('document_type', Document::TIPO_CALIDAD)
            ->get()
            ->groupBy('section');
        $indicadores = IndicadorMaestro::query()->vigentes()->orderBy('codigo')->get()
            ->each(function (IndicadorMaestro $indicador) use ($porIndicador, $documentosIndicadores): void {
                $indicador->setRelation('mediciones', collect([$porIndicador->get($indicador->id)])->filter());
                $documentos = $documentosIndicadores->get($indicador->macro_proceso, collect());
                $indicador->setRelation('documento', $this->documentoIndicador($indicador, $documentos));
            });

        return view('livewire.indicadores.dashboard-calidad', [
            'indicadores' => $indicadores,
            'mediciones' => $mediciones,
            'puedeConsolidar' => auth()->user()?->can('indicator.consolidate') ?? false,
        ]);
    }

    private function medicionEditable(int $medicionId): IndicadorMedicion
    {
        return IndicadorMedicion::query()->whereKey($medicionId)
            ->where('programa_estudio_id', $this->programaEstudioId)
            ->where('periodo_academico', $this->periodoAcademico)
            ->whereIn('estado_cumplimiento', ['OBSERVADO', 'CRITICO', 'NO_CONFORME'])
            ->firstOrFail();
    }

    /** @return Collection<int, string> */
    private function periodosDisponibles(): Collection
    {
        return PeriodoAcademico::query()->orderByDesc('codigo')->pluck('codigo')
            ->merge(Syllabus::query()->distinct()->pluck('academic_period'))
            ->merge(Matricula::query()->distinct()->pluck('periodo_academico'))
            ->filter(fn (mixed $periodo): bool => is_string($periodo) && preg_match('/^\d{4}-(I|II)$/', $periodo) === 1)
            ->unique()->sortDesc()->values();
    }

    private function periodoActual(): string
    {
        return now()->format('Y').(now()->month <= 6 ? '-I' : '-II');
    }

    /** @param  Collection<int, Document>  $documentos */
    private function documentoIndicador(IndicadorMaestro $indicador, Collection $documentos): ?Document
    {
        $tituloDocumento = Arr::get($indicador->configuracion ?? [], 'documento_titulo');

        if (is_string($tituloDocumento) && $tituloDocumento !== '') {
            return $documentos->first(fn (Document $documento): bool => $documento->title === $tituloDocumento);
        }

        return $documentos->first(fn (Document $documento): bool => str_contains($documento->title, $indicador->codigo))
            ?? $documentos->first();
    }

    /** @return array<string, mixed> */
    private function datosVacios(): array
    {
        return [
            'indicadores' => collect(), 'mediciones' => collect(),
            'puedeConsolidar' => false,
        ];
    }
}
