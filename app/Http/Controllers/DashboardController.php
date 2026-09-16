<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Indicator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $indicators = Indicator::latest('updated_at')->get();
        $average = $indicators->isEmpty() ? 0 : round($indicators->avg(fn (Indicator $indicator) => $indicator->progress), 1);

        return view('dashboard', [
            'indicators' => $indicators,
            'average' => $average,
            'completed' => $indicators->filter(fn (Indicator $indicator) => $indicator->status === 'Cumplido')->count(),
            'atRisk' => $indicators->filter(fn (Indicator $indicator) => $indicator->status === 'En riesgo')->count(),
            'critical' => $indicators->filter(fn (Indicator $indicator) => $indicator->status === 'Crítico')->count(),
            'documents' => Document::latest('publication_date')->take(4)->get(),
        ]);
    }
}
