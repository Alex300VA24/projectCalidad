<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Indicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_summary_information(): void
    {
        Indicator::create($this->indicatorData());

        $this->get('/')
            ->assertOk()
            ->assertSee('La calidad se gestiona')
            ->assertSee('Satisfacción de usuarios');
    }

    public function test_an_indicator_can_be_created_and_updated(): void
    {
        $this->post('/indicadores', $this->indicatorData())
            ->assertRedirect()
            ->assertSessionHas('success');

        $indicator = Indicator::firstOrFail();

        $this->patch("/indicadores/{$indicator->id}", ['current_value' => 95])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('indicators', ['code' => 'CAL-01', 'current_value' => 95]);
    }

    public function test_a_google_drive_document_generates_a_preview_url(): void
    {
        $document = Document::create([
            'title' => 'Informe de calidad',
            'section' => 'Informes',
            'description' => 'Evidencia institucional',
            'drive_url' => 'https://drive.google.com/file/d/archivo-publico-123/view',
            'publication_date' => '2026-09-16',
        ]);

        $this->assertSame(
            'https://drive.google.com/file/d/archivo-publico-123/preview',
            $document->preview_url
        );

        $this->get('/documentos')->assertOk()->assertSee('Informe de calidad');
    }

    public function test_non_drive_links_are_rejected(): void
    {
        $this->post('/documentos', [
            'title' => 'Documento externo',
            'section' => 'Informes',
            'drive_url' => 'https://example.com/informe.pdf',
            'publication_date' => '2026-09-16',
        ])->assertSessionHasErrors('drive_url');
    }

    private function indicatorData(): array
    {
        return [
            'code' => 'CAL-01',
            'name' => 'Satisfacción de usuarios',
            'area' => 'Calidad',
            'objective' => 'Medir la percepción de los servicios.',
            'unit' => '%',
            'target_value' => 90,
            'current_value' => 84,
            'frequency' => 'Trimestral',
            'responsible' => 'Oficina de Calidad',
            'period' => '2026 - III',
        ];
    }
}
