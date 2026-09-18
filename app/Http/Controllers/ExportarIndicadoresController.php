<?php

namespace App\Http\Controllers;

use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Services\CalculadorIndicadoresService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ExportarIndicadoresController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, CalculadorIndicadoresService $calculador): Response
    {
        Gate::authorize('viewAny', IndicadorMedicion::class);
        $validated = $request->validate([
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'periodo_academico' => ['required', 'regex:/^\d{4}-(I|II)$/'],
        ]);
        $programa = ProgramaEstudio::query()->findOrFail($validated['programa_estudio_id']);
        $mediciones = $calculador->calcularActuales($programa, $validated['periodo_academico']);

        return Pdf::loadView('pdf.indicadores-calidad', [
            'programa' => $programa,
            'periodo' => $validated['periodo_academico'],
            'mediciones' => $mediciones,
        ])->download("indicadores-{$programa->codigo}-{$validated['periodo_academico']}.pdf");
    }
}
