<?php

namespace Tests\Feature;

use App\Models\Document;
use Database\Seeders\ConvenioMovilidadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvenioMovilidadSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobility_documents_are_imported_as_institutional_documents(): void
    {
        $this->seed(ConvenioMovilidadSeeder::class);

        $this->assertDatabaseCount('documents', 20);
        $this->assertDatabaseHas('documents', [
            'title' => '005-2025 ESTUDIANTE QUE PARTICIPARA EN MOVILIDAD INTERNACIONAL-CARDENAS SANDRO.pdf',
            'document_type' => Document::TIPO_INSTITUCIONAL,
            'section' => 'EXPEDIENTES ALUMNOS UNIVERSIDAD OUROPRETO-SAO PAULO-GRANADA',
            'publication_date' => '2025-12-31 00:00:00',
        ]);
        $this->assertDatabaseHas('documents', [
            'title' => 'RCU-N-458-2024-UNT.pdf',
            'document_type' => Document::TIPO_INSTITUCIONAL,
            'section' => 'RESOLUCIONES - UNIVERSIDADES DE SAO PAULO Y OUROPRETO',
            'publication_date' => '2024-12-31 00:00:00',
        ]);

        $this->seed(ConvenioMovilidadSeeder::class);

        $this->assertDatabaseCount('documents', 20);
    }
}
