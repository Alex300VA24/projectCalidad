<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.curricula.index', [
            'curricula' => Curriculum::query()->withCount('courses')->latest('id')->get(),
            'editing' => $request->integer('edit') ? Curriculum::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Curriculum::create($request->validate([
            'name' => ['required', 'string', 'max:180'],
            'version' => ['required', 'string', 'max:30'],
            'approved_at' => ['nullable', 'date'],
        ]));

        return redirect()->route('curricula.index')->with('success', 'Currículo registrado correctamente.');
    }

    public function update(Request $request, Curriculum $curriculum): RedirectResponse
    {
        $curriculum->update($request->validate([
            'name' => ['required', 'string', 'max:180'],
            'version' => ['required', 'string', 'max:30'],
            'approved_at' => ['nullable', 'date'],
        ]));

        return redirect()->route('curricula.index')->with('success', 'Currículo actualizado correctamente.');
    }

    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        $curriculum->delete();

        return back()->with('success', 'Currículo eliminado.');
    }
}
