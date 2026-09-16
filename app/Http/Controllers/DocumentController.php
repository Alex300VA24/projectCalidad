<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Document::query()->latest('publication_date');

        if ($section = $request->string('seccion')->trim()->toString()) {
            $query->where('section', $section);
        }

        return view('documents.index', [
            'documents' => $query->get(),
            'sections' => Document::query()->distinct()->orderBy('section')->pluck('section'),
            'activeSection' => $section ?? '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Document::create($request->validate([
            'title' => ['required', 'string', 'max:180'],
            'section' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'drive_url' => ['required', 'url', 'starts_with:https://drive.google.com/'],
            'publication_date' => ['required', 'date'],
        ], [
            'drive_url.starts_with' => 'Ingresa un enlace válido de Google Drive.',
        ]));

        return back()->with('success', 'Documento vinculado correctamente.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();

        return back()->with('success', 'Documento retirado del repositorio.');
    }
}
