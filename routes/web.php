<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExportarIndicadoresController;
use App\Http\Controllers\FormatoOficialController;
use App\Http\Controllers\MapaProcesosController;
use App\Models\IndicadorMaestro;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/mapa-procesos', MapaProcesosController::class)->name('mapa-procesos.index');
Route::get('/formatos/{slug}', FormatoOficialController::class)->name('formatos.show');

Route::view('/indicadores/calidad', 'indicators.dashboard-calidad')->name('quality-indicators.dashboard');
Route::get('/indicadores/calidad/exportar', ExportarIndicadoresController::class)->name('quality-indicators.export');
Route::get('/indicadores/calidad/{indicador:codigo}/historial', function (IndicadorMaestro $indicador) {
    return view('indicators.historial', ['indicador' => $indicador]);
})->name('quality-indicators.historial');

Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/documentos', [DocumentController::class, 'store'])->name('documents.store');
Route::delete('/documentos/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
