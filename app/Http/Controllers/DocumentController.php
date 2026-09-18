<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PeriodoAcademico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private const SYLLABUS_PERIODS = ['2025-I', '2025-II', '2026-I', '2026-II'];

    private const ODD_CYCLES = [1, 3, 5, 7, 9];

    private const EVEN_CYCLES = [2, 4, 6, 8, 10];

    public function index(Request $request): View
    {
        $type = $request->string('tipo')->trim()->toString();
        $type = in_array($type, [Document::TIPO_INSTITUCIONAL, Document::TIPO_SILABO, Document::TIPO_CALIDAD], true)
            ? $type
            : Document::TIPO_INSTITUCIONAL;

        $query = Document::query()->where('document_type', $type)->latest('publication_date');
        $activePeriodo = '';
        $activeCycle = 0;
        $cycles = [];
        $showDocuments = true;

        if ($type === Document::TIPO_SILABO) {
            $query->with('periodoAcademico');
            $requestedPeriodo = $request->string('periodo')->trim()->toString();
            $activePeriodo = in_array($requestedPeriodo, self::SYLLABUS_PERIODS, true) ? $requestedPeriodo : '';
            $cycles = $activePeriodo !== '' ? $this->cyclesForPeriod($activePeriodo) : [];
            $requestedCycle = $request->integer('ciclo');
            $activeCycle = in_array($requestedCycle, $cycles, true) ? $requestedCycle : 0;
            $showDocuments = $activePeriodo !== '' && $activeCycle !== 0;

            if ($showDocuments) {
                $query
                    ->where('ciclo_academico', $activeCycle)
                    ->whereHas('periodoAcademico', fn ($builder) => $builder->where('codigo', $activePeriodo));
            }
        }

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
}
