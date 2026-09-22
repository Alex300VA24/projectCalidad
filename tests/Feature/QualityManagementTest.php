<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\PeriodoAcademico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class QualityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_summary_information_without_an_active_program(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('La calidad se gestiona')
            ->assertSee('Aún no hay datos de indicadores');
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

        $this->get('/documentos?tipo=institucional')->assertOk()->assertSee('Informe de calidad');
    }

    public function test_a_google_drive_folder_opens_externally_instead_of_in_the_pdf_viewer(): void
    {
        $folderUrl = 'https://drive.google.com/drive/folders/carpeta-publica-123?usp=sharing';

        $document = Document::create([
            'title' => 'Carpeta de sílabos',
            'section' => '2025-I',
            'drive_url' => $folderUrl,
            'publication_date' => '2026-03-01',
        ]);

        $this->assertTrue($document->is_drive_folder);

        $this->get('/documentos?tipo=institucional')
            ->assertSee('Abrir carpeta en Drive')
            ->assertSee('href="'.$folderUrl.'"', false)
            ->assertDontSee('data-preview="'.$folderUrl.'"', false);
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

    public function test_syllabi_are_filtered_by_the_cycles_supported_for_each_semester(): void
    {
        $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => '2025-I'], ['activo' => true]);
        Document::create([
            'title' => 'Inteligencia Artificial I',
            'document_type' => Document::TIPO_SILABO,
            'periodo_academico_id' => $periodo->id,
            'ciclo_academico' => 5,
            'drive_url' => 'https://drive.google.com/file/d/silabo-ia-2025/view',
            'publication_date' => '2025-03-01',
        ]);

        $this->get('/documentos?tipo=silabo')
            ->assertViewHasAll([
                'activeType' => Document::TIPO_SILABO,
                'activePeriodo' => '2025-I',
                'activeCycle' => 1,
            ])
            ->assertSee('2025-I')
            ->assertSee('2025-II')
            ->assertSee('2026-I')
            ->assertSee('2026-II')
            ->assertDontSee('Inteligencia Artificial I');

        $this->get('/documentos?tipo=silabo&periodo=2025-I')
            ->assertViewHas('activeCycle', 1)
            ->assertSee('1.er ciclo')
            ->assertSee('3.er ciclo')
            ->assertSee('5.º ciclo')
            ->assertSee('7.º ciclo')
            ->assertSee('9.º ciclo')
            ->assertSee('periodo=2025-I&amp;ciclo=5', false)
            ->assertDontSee('periodo=2025-I&amp;ciclo=2', false)
            ->assertDontSee('Inteligencia Artificial I');

        $this->get('/documentos?tipo=silabo&periodo=2025-I&ciclo=5')
            ->assertSee('Inteligencia Artificial I');

        $this->get('/documentos?tipo=silabo&periodo=2025-II')
            ->assertSee('2.º ciclo')
            ->assertSee('4.º ciclo')
            ->assertSee('6.º ciclo')
            ->assertSee('8.º ciclo')
            ->assertSee('10.º ciclo')
            ->assertSee('periodo=2025-II&amp;ciclo=10', false)
            ->assertDontSee('periodo=2025-II&amp;ciclo=3', false);
    }

    public function test_quality_documents_can_be_created_and_viewed_in_their_own_section(): void
    {
        $this->post('/documentos', [
            'document_type' => Document::TIPO_CALIDAD,
            'title' => 'Matriz de calidad',
            'drive_url' => 'https://drive.google.com/file/d/matriz-calidad-2026/view',
            'publication_date' => '2026-09-17',
        ])->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'document_type' => Document::TIPO_CALIDAD,
            'title' => 'Matriz de calidad',
        ]);

        $this->get('/documentos?tipo=calidad')
            ->assertSee('Matriz de calidad')
            ->assertSee('Revisión del Modelo Educativo')
            ->assertSee(route('formatos.show', 'gc-01-f-m0101-dpa-001'), false)
            ->assertSee('data-open-execution-reports="M01.01.03.01-F-005"', false)
            ->assertSee('data-open-execution-reports="M01.01.03.01-F-013"', false)
            ->assertSee('data-open-student-references', false)
            ->assertSee('Visualizar documentos')
            ->assertSee('Paginación de documentos de calidad')
            ->assertSee('tipo=calidad&amp;pagina=2', false)
            ->assertViewHas('qualityDocuments', function (LengthAwarePaginator $documents): bool {
                return $documents->currentPage() === 1
                    && $documents->perPage() === 12
                    && $documents->total() > $documents->perPage()
                    && $documents->pluck('code')->take(3)->values()->all() === [
                        'M01.01.03.01-F-005',
                        'M01.01.03.01-F-013',
                        'F.M01.04-DDA/PG-06',
                    ];
            });

        $this->get('/documentos?tipo=calidad&pagina=2')
            ->assertOk()
            ->assertViewHas('qualityDocuments', fn (LengthAwarePaginator $documents): bool => $documents->currentPage() === 2);
    }

    public function test_syllabus_requires_an_academic_cycle(): void
    {
        $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => '2026-II'], ['activo' => true]);

        $this->post('/documentos', [
            'document_type' => Document::TIPO_SILABO,
            'title' => 'Base de Datos II',
            'periodo_academico_id' => $periodo->id,
            'drive_url' => 'https://drive.google.com/file/d/base-datos-ii/view',
            'publication_date' => '2026-09-17',
        ])->assertSessionHasErrors('ciclo_academico');

        $this->assertDatabaseMissing('documents', ['title' => 'Base de Datos II']);
    }

    public function test_syllabus_cycle_must_match_the_semester_parity(): void
    {
        $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => '2026-II'], ['activo' => true]);

        $this->post('/documentos', [
            'document_type' => Document::TIPO_SILABO,
            'title' => 'Curso impar en semestre par',
            'periodo_academico_id' => $periodo->id,
            'ciclo_academico' => 3,
            'drive_url' => 'https://drive.google.com/file/d/ciclo-invalido/view',
            'publication_date' => '2026-09-17',
        ])->assertSessionHasErrors('ciclo_academico');

        $this->assertDatabaseMissing('documents', ['title' => 'Curso impar en semestre par']);
    }

    public function test_document_view_has_only_the_requested_categories_and_no_sharing_notice(): void
    {
        $this->get('/documentos')
            ->assertSee('Documentos institucionales')
            ->assertSee('Sílabos visados')
            ->assertSee('Documentos de calidad')
            ->assertViewHasAll([
                'activeType' => Document::TIPO_SILABO,
                'activePeriodo' => '2025-I',
                'activeCycle' => 1,
            ])
            ->assertDontSee('Políticas y lineamientos')
            ->assertDontSee('Cualquier persona con el enlace');
    }
}
