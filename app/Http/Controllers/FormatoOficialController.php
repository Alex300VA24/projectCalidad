<?php

namespace App\Http\Controllers;

use App\Services\MapaProcesosCatalogService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FormatoOficialController extends Controller
{
    public function __invoke(string $slug): BinaryFileResponse
    {
        $formato = MapaProcesosCatalogService::findFormatBySlug($slug);

        abort_unless($formato, 404);

        $base = realpath(base_path('DOCS'));
        $full = $base ? realpath($base.DIRECTORY_SEPARATOR.$formato['path']) : false;

        abort_unless($base && $full && str_starts_with($full, $base), 404);

        if (strtolower(pathinfo($full, PATHINFO_EXTENSION)) === 'pdf') {
            return response()->file($full);
        }

        return response()->download($full);
    }
}
