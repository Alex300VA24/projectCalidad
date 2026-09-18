<?php

namespace Database\Seeders;

use App\Models\ProcedureCatalog;
use Illuminate\Database\Seeder;

class ProcedureCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            ['code' => 'GC-01', 'domain_enum' => 'GC', 'title' => 'Revisión y evaluación curricular'],
            ['code' => 'GC-02', 'domain_enum' => 'GC', 'title' => 'Rediseño o ajuste de plan curricular'],
            ['code' => 'GC-03', 'domain_enum' => 'GC', 'title' => 'Elaboración y visado de sílabo'],
            ['code' => 'GC-04', 'domain_enum' => 'GC', 'title' => 'Requerimiento bibliográfico y hemerográfico'],

            ['code' => 'SD-01', 'domain_enum' => 'SD', 'title' => 'Análisis de resultados de admisión y perfil de ingreso'],
            ['code' => 'SD-02', 'domain_enum' => 'SD', 'title' => 'Plan y sesiones de tutoría'],
            ['code' => 'SD-03', 'domain_enum' => 'SD', 'title' => 'Derivación y contrarreferencia del estudiante'],
            ['code' => 'SD-04', 'domain_enum' => 'SD', 'title' => 'Nivelación académica'],

            ['code' => 'EV-01', 'domain_enum' => 'EV', 'title' => 'Socialización del sílabo'],
            ['code' => 'EV-02A', 'domain_enum' => 'EV', 'title' => 'Preparación y aplicación de prueba anónima con sobre lacrado'],
            ['code' => 'EV-02B', 'domain_enum' => 'EV', 'title' => 'Desglosado, calificación y consolidación de prueba anónima'],
            ['code' => 'EV-03', 'domain_enum' => 'EV', 'title' => 'Examen de suficiencia'],
            ['code' => 'EV-04', 'domain_enum' => 'EV', 'title' => 'Rectificación de notas'],
            ['code' => 'EV-05', 'domain_enum' => 'EV', 'title' => 'Evaluación de competencias del egresado'],

            ['code' => 'IF-01', 'domain_enum' => 'IF', 'title' => 'Matriz de competencias de investigación'],
            ['code' => 'IF-02', 'domain_enum' => 'IF', 'title' => 'Formulación de proyecto de investigación'],
            ['code' => 'IF-03', 'domain_enum' => 'IF', 'title' => 'Asesoría y ejecución de proyecto de investigación'],
            ['code' => 'IF-04', 'domain_enum' => 'IF', 'title' => 'Sustentación de proyecto de investigación'],

            ['code' => 'EPC-01', 'domain_enum' => 'EPC', 'title' => 'Requerimiento de carga docente'],
            ['code' => 'EPC-02', 'domain_enum' => 'EPC', 'title' => 'Calendario académico'],
            ['code' => 'EPC-03', 'domain_enum' => 'EPC', 'title' => 'Informe de ejecución de asignatura'],
            ['code' => 'EPC-04', 'domain_enum' => 'EPC', 'title' => 'Evaluación de desempeño docente'],
            ['code' => 'EPC-05', 'domain_enum' => 'EPC', 'title' => 'Prácticas preprofesionales'],
            ['code' => 'EPC-06', 'domain_enum' => 'EPC', 'title' => 'Capacitación docente'],
            ['code' => 'EPC-07', 'domain_enum' => 'EPC', 'title' => 'Portafolio digital docente'],

            ['code' => 'CERT-01', 'domain_enum' => 'CERT', 'title' => 'Certificación de historial académico físico (ingresantes hasta 2007)'],

            ['code' => 'TIT-01', 'domain_enum' => 'TIT', 'title' => 'Constancia de expedito'],
            ['code' => 'TIT-02', 'domain_enum' => 'TIT', 'title' => 'Constancia de no adeudo'],
            ['code' => 'TIT-03', 'domain_enum' => 'TIT', 'title' => 'Constancia de aprobación de tesis'],

            ['code' => 'GRAD-01', 'domain_enum' => 'GRAD', 'title' => 'Validación de condición de egresado'],
            ['code' => 'GRAD-02', 'domain_enum' => 'GRAD', 'title' => 'Carpeta de graduación y registro STU/SUNEDU'],

            ['code' => 'SE-01', 'domain_enum' => 'SE', 'title' => 'Registro de aptos para titulación'],
            ['code' => 'SE-02', 'domain_enum' => 'SE', 'title' => 'Actualización de ficha de egresado'],
            ['code' => 'SE-03', 'domain_enum' => 'SE', 'title' => 'Base de datos de egresados'],
            ['code' => 'SE-04', 'domain_enum' => 'SE', 'title' => 'Reporte estadístico anual de egresados'],
            ['code' => 'SE-05', 'domain_enum' => 'SE', 'title' => 'Evaluación de objetivos educacionales'],

            ['code' => 'MAT-01', 'domain_enum' => 'MAT', 'title' => 'Matrícula regular'],
            ['code' => 'MAT-02', 'domain_enum' => 'MAT', 'title' => 'Matrícula extemporánea'],
            ['code' => 'MAT-03', 'domain_enum' => 'MAT', 'title' => 'Rectificación de matrícula'],

            ['code' => 'MOV-01', 'domain_enum' => 'MOV', 'title' => 'Convocatoria de movilidad'],
            ['code' => 'MOV-02', 'domain_enum' => 'MOV', 'title' => 'Postulación a movilidad'],
            ['code' => 'MOV-03A', 'domain_enum' => 'MOV', 'title' => 'Registro ORNI y exoneración de tasa'],
            ['code' => 'MOV-03B', 'domain_enum' => 'MOV', 'title' => 'Convalidación de notas'],
            ['code' => 'MOV-03C', 'domain_enum' => 'MOV', 'title' => 'Cierre y subvención de movilidad'],
        ];

        foreach ($codes as $code) {
            ProcedureCatalog::updateOrCreate(['code' => $code['code']], $code);
        }
    }
}
