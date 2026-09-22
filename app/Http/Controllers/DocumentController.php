<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PeriodoAcademico;
use App\Services\MapaProcesosCatalogService;
use App\Services\QualityEvidenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private const SYLLABUS_PERIODS = ['2025-I', '2025-II', '2026-I', '2026-II'];

    private const ODD_CYCLES = [1, 3, 5, 7, 9];

    private const EVEN_CYCLES = [2, 4, 6, 8, 10];

    private const DEFAULT_SYLLABUS_PERIOD = '2025-I';

    private const QUALITY_DOCUMENTS_PER_PAGE = 12;

    public function __construct(private readonly QualityEvidenceService $qualityEvidence) {}

    public function index(Request $request): View
    {
        $type = $request->string('tipo')->trim()->toString();
        $type = in_array($type, [Document::TIPO_INSTITUCIONAL, Document::TIPO_SILABO, Document::TIPO_CALIDAD], true)
            ? $type
            : Document::TIPO_SILABO;

        $query = Document::query()->where('document_type', $type)->latest('publication_date');
        $activePeriodo = '';
        $activeCycle = 0;
        $cycles = [];
        $showDocuments = true;

        if ($type === Document::TIPO_SILABO) {
            $query->with('periodoAcademico');
            $requestedPeriodo = $request->string('periodo')->trim()->toString();
            $activePeriodo = in_array($requestedPeriodo, self::SYLLABUS_PERIODS, true)
                ? $requestedPeriodo
                : self::DEFAULT_SYLLABUS_PERIOD;
            $cycles = $this->cyclesForPeriod($activePeriodo);
            $requestedCycle = $request->integer('ciclo');
            $activeCycle = in_array($requestedCycle, $cycles, true) ? $requestedCycle : $cycles[0];
            $showDocuments = true;

            $query
                ->where('ciclo_academico', $activeCycle)
                ->whereHas('periodoAcademico', fn ($builder) => $builder->where('codigo', $activePeriodo));
        }

        $isQualitySection = $type === Document::TIPO_CALIDAD;

        return view('documents.index', [
            'documents' => $showDocuments ? $query->get() : collect(),
            'activeType' => $type,
            'periodos' => self::SYLLABUS_PERIODS,
            'periodoOptions' => PeriodoAcademico::query()
                ->whereIn('codigo', self::SYLLABUS_PERIODS)
                ->orderByDesc('codigo')
                ->get(['id', 'codigo']),
            'activePeriodo' => $activePeriodo,
            'cycles' => $cycles,
            'activeCycle' => $activeCycle,
            'showDocuments' => $showDocuments,
            'qualityDocuments' => $isQualitySection ? $this->qualityDocuments($request) : collect(),
            'executionReportsByPeriod' => $isQualitySection ? $this->qualityEvidence->executionReportsByPeriod() : [],
            'consolidatedReportsByPeriod' => $isQualitySection ? $this->qualityEvidence->consolidatedReportsByPeriod() : [],
            'studentReferences' => $isQualitySection ? $this->qualityEvidence->studentReferences() : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->string('document_type')->toString();
        $selectedPeriod = $type === Document::TIPO_SILABO
            ? PeriodoAcademico::query()->find($request->integer('periodo_academico_id'))
            : null;
        $allowedCycles = $selectedPeriod ? $this->cyclesForPeriod($selectedPeriod->codigo) : [];

        $validated = $request->validate([
            'document_type' => ['required', Rule::in([Document::TIPO_INSTITUCIONAL, Document::TIPO_SILABO, Document::TIPO_CALIDAD])],
            'title' => ['required', 'string', 'max:180'],
            'periodo_academico_id' => [Rule::excludeIf($type !== Document::TIPO_SILABO), 'required', 'integer', 'exists:periodos_academicos,id'],
            'ciclo_academico' => [Rule::excludeIf($type !== Document::TIPO_SILABO), 'required', 'integer', Rule::in($allowedCycles)],
            'description' => ['nullable', 'string', 'max:500'],
            'drive_url' => ['required', 'url', 'starts_with:https://drive.google.com/'],
            'publication_date' => ['required', 'date'],
        ], [
            'drive_url.starts_with' => 'Ingresa un enlace válido de Google Drive.',
        ]);

        Document::create($validated);

        return back()->with('success', 'Documento vinculado correctamente.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();

        return back()->with('success', 'Documento retirado del repositorio.');
    }

    /** @return list<int> */
    private function cyclesForPeriod(string $period): array
    {
        return str_ends_with($period, '-I') ? self::ODD_CYCLES : self::EVEN_CYCLES;
    }

    /**
     * @return LengthAwarePaginator<int, array{code: string, name: string, process_code: string, process_title: string, extension: string, url: string, viewer: string|null}>
     */
    private function qualityDocuments(Request $request): LengthAwarePaginator
    {
        $documents = collect(MapaProcesosCatalogService::all())
            ->flatMap(function (array $process): Collection {
                return collect($process['formats'] ?? [])->map(fn (array $format): array => [
                    'code' => $format['code'],
                    'name' => $format['name'],
                    'process_code' => $process['code'],
                    'process_title' => $process['title'],
                    'extension' => Str::upper(pathinfo($format['path'], PATHINFO_EXTENSION)),
                    'url' => route('formatos.show', Str::slug($process['code'].' '.$format['code'])),
                    'viewer' => match (true) {
                        $process['code'] === 'EPC-01' && in_array($format['code'], ['M01.01.03.01-F-005', 'M01.01.03.01-F-013'], true) => 'execution-reports',
                        $process['code'] === 'SD-02' && $format['code'] === 'F.M01.04-DDA/PG-06' => 'student-references',
                        default => null,
                    },
                ]);
            })
            ->sortBy(fn (array $document): int => match ($document['code']) {
                'M01.01.03.01-F-005' => 0,
                'M01.01.03.01-F-013' => 1,
                'F.M01.04-DDA/PG-06' => 2,
                default => 3,
            })
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage('pagina');

        return (new LengthAwarePaginator(
            $documents->forPage($currentPage, self::QUALITY_DOCUMENTS_PER_PAGE)->values(),
            $documents->count(),
            self::QUALITY_DOCUMENTS_PER_PAGE,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'pagina',
            ],
        ))->appends($request->except('pagina'));
    }
}
