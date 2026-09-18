<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Roles y sus permisos asociados, según la matriz de la Escuela Profesional.
     *
     * @var array<string, list<string>>
     */
    private array $roleMatrix = [
        'Director_Escuela' => [
            'curriculum.approve',
            'curriculum.revalidate',
            'syllabus.vise',
            'jury_resolution.issue',
            'advisor_resolution.issue',
            'tutor.designate',
            'report.elevate_to_dean',
            'indicator.view',
            'indicator.analyze',
            'indicator.consolidate',
            'indicator.manage',
        ],
        'COTECCU' => [
            'curriculum.evaluate',
            'curriculum_plan.design',
            'curriculum_plan.adjust',
            'research_matrix.elaborate',
        ],
        'Secretaria_Escuela' => [
            'academic_history.elaborate',
            'graduation_folder.validate',
            'egresado_condition.register',
            'sunedu_data.register',
        ],
        'Docente' => [
            'syllabus.upload',
            'syllabus.socialize',
            'anonymous_exam.apply',
            'grades.register',
            'course_execution_report.elaborate',
        ],
        'Docente_Tutor' => [
            'tutoring.register',
            'referral.elaborate',
            'tutoring.annual_report.emit',
        ],
        'Comision_Evaluadora' => [
            'competency.evaluate',
            'educational_objective.evaluate',
        ],
        'Comision_Tutoria' => [
            'tutoring.oversee',
        ],
        'Responsable_Seguimiento_Egresado' => [
            'graduate_database.manage',
            'employability_survey.apply',
            'indicator.consolidate',
            'indicator.view',
            'indicator.analyze',
        ],
        'Administrador' => [
            'indicator.view',
            'indicator.analyze',
            'indicator.consolidate',
            'indicator.manage',
        ],
        'Estudiante' => [
            'request.register',
            'anonymous_exam.fill_desglosable',
            'graduate_profile.update',
            'mobility.apply',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->roleMatrix as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }

            $role->syncPermissions($permissions);
        }
    }
}
