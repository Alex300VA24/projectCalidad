<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\ResearchLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchLineController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.research-lines.index', [
            'researchLines' => ResearchLine::query()->latest('id')->get(),
            'editing' => $request->integer('edit') ? ResearchLine::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ResearchLine::create($request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:600'],
        ]));

        return redirect()->route('research-lines.index')->with('success', 'Línea de investigación registrada correctamente.');
    }

    public function update(Request $request, ResearchLine $researchLine): RedirectResponse
    {
        $researchLine->update($request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:600'],
        ]));

        return redirect()->route('research-lines.index')->with('success', 'Línea de investigación actualizada correctamente.');
    }

    public function destroy(ResearchLine $researchLine): RedirectResponse
    {
        $researchLine->delete();

        return back()->with('success', 'Línea de investigación eliminada.');
    }
}
