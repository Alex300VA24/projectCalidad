<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IndicatorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Indicator::query()->latest('updated_at');

        if ($search = $request->string('buscar')->trim()->toString()) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('area', 'like', "%{$search}%"));
        }

        return view('indicators.index', ['indicators' => $query->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Indicator::create($request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:indicators,code'],
            'name' => ['required', 'string', 'max:160'],
            'area' => ['required', 'string', 'max:100'],
            'objective' => ['required', 'string', 'max:500'],
            'unit' => ['required', 'string', 'max:30'],
            'target_value' => ['required', 'numeric', 'gt:0'],
            'current_value' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', Rule::in(['Mensual', 'Trimestral', 'Semestral', 'Anual'])],
            'responsible' => ['required', 'string', 'max:120'],
            'period' => ['required', 'string', 'max:30'],
        ]));

        return back()->with('success', 'Indicador creado correctamente.');
    }

    public function update(Request $request, Indicator $indicator): RedirectResponse
    {
        $indicator->update($request->validate([
            'current_value' => ['required', 'numeric', 'min:0'],
        ]));

        return back()->with('success', 'Avance del indicador actualizado.');
    }

    public function destroy(Indicator $indicator): RedirectResponse
    {
        $indicator->delete();

        return back()->with('success', 'Indicador eliminado.');
    }
}
