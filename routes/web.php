<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\IndicatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/indicadores', [IndicatorController::class, 'index'])->name('indicators.index');
Route::post('/indicadores', [IndicatorController::class, 'store'])->name('indicators.store');
Route::patch('/indicadores/{indicator}', [IndicatorController::class, 'update'])->name('indicators.update');
Route::delete('/indicadores/{indicator}', [IndicatorController::class, 'destroy'])->name('indicators.destroy');

Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/documentos', [DocumentController::class, 'store'])->name('documents.store');
Route::delete('/documentos/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
