<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Indicator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $indicators = [
            ['code' => 'CAL-01', 'name' => 'Satisfacción de usuarios', 'area' => 'Calidad', 'objective' => 'Medir la percepción de los usuarios sobre los servicios institucionales.', 'unit' => '%', 'target_value' => 90, 'current_value' => 92.4, 'frequency' => 'Trimestral', 'responsible' => 'Oficina de Calidad', 'period' => '2026 - III'],
            ['code' => 'PLA-04', 'name' => 'Ejecución del plan operativo', 'area' => 'Planeamiento', 'objective' => 'Controlar el avance de las actividades incluidas en el plan operativo.', 'unit' => '%', 'target_value' => 100, 'current_value' => 78.2, 'frequency' => 'Mensual', 'responsible' => 'Planeamiento', 'period' => 'Setiembre 2026'],
            ['code' => 'ACA-07', 'name' => 'Programas con autoevaluación', 'area' => 'Gestión académica', 'objective' => 'Asegurar la evaluación periódica de los programas académicos.', 'unit' => 'programas', 'target_value' => 12, 'current_value' => 8, 'frequency' => 'Semestral', 'responsible' => 'Dirección Académica', 'period' => '2026 - II'],
            ['code' => 'TH-02', 'name' => 'Cobertura de capacitación', 'area' => 'Talento humano', 'objective' => 'Fortalecer competencias del personal administrativo y académico.', 'unit' => '%', 'target_value' => 85, 'current_value' => 80.7, 'frequency' => 'Trimestral', 'responsible' => 'Recursos Humanos', 'period' => '2026 - III'],
            ['code' => 'INV-03', 'name' => 'Proyectos de investigación activos', 'area' => 'Investigación', 'objective' => 'Impulsar la producción y continuidad de proyectos institucionales.', 'unit' => 'proyectos', 'target_value' => 20, 'current_value' => 21, 'frequency' => 'Mensual', 'responsible' => 'Dirección de Investigación', 'period' => 'Setiembre 2026'],
        ];

        foreach ($indicators as $indicator) {
            Indicator::updateOrCreate(['code' => $indicator['code']], $indicator);
        }

        $documents = [
            ['title' => 'Política institucional de calidad', 'section' => 'Políticas y lineamientos', 'description' => 'Marco rector del sistema de gestión de la calidad.', 'drive_url' => 'https://drive.google.com/file/d/1EjemploPoliticaCalidad2026/view', 'publication_date' => '2026-08-12'],
            ['title' => 'Manual de indicadores 2026', 'section' => 'Instrumentos de gestión', 'description' => 'Fichas técnicas, fórmulas y criterios de medición.', 'drive_url' => 'https://drive.google.com/file/d/1EjemploManualIndicadores26/view', 'publication_date' => '2026-07-28'],
            ['title' => 'Informe de seguimiento trimestral', 'section' => 'Informes de seguimiento', 'description' => 'Resultados consolidados del tercer trimestre.', 'drive_url' => 'https://drive.google.com/file/d/1EjemploInformeTrimestre3/view', 'publication_date' => '2026-09-05'],
            ['title' => 'Plan de mejora institucional', 'section' => 'Planes de mejora', 'description' => 'Acciones, responsables y plazos de mejora priorizados.', 'drive_url' => 'https://drive.google.com/file/d/1EjemploPlanMejoraInstitucional/view', 'publication_date' => '2026-08-30'],
        ];

        foreach ($documents as $document) {
            Document::updateOrCreate(['title' => $document['title']], $document);
        }
    }
}
