<?php

namespace Tests\Feature;

use App\Models\Curriculum;
use App\Models\CurriculumReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TramitesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_hub_lists_the_eleven_procedure_domains(): void
    {
        $this->get('/tramites')
            ->assertOk()
            ->assertSee('Gestión Curricular')
            ->assertSee('Matrícula y Movilidad');
    }

    public function test_a_curriculum_can_be_created_and_edited(): void
    {
        $this->post('/tramites/curriculos', [
            'name' => 'Plan Curricular Ingeniería',
            'version' => '2026-I',
        ])->assertRedirect(route('curricula.index'))->assertSessionHas('success');

        $curriculum = Curriculum::firstOrFail();

        $this->get(route('curricula.index', ['edit' => $curriculum->id]))
            ->assertOk()
            ->assertSee('Plan Curricular Ingeniería');

        $this->put(route('curricula.update', $curriculum), [
            'name' => 'Plan Curricular Ingeniería de Sistemas',
            'version' => '2026-I',
        ])->assertRedirect(route('curricula.index'));

        $this->assertDatabaseHas('curricula', ['name' => 'Plan Curricular Ingeniería de Sistemas']);

        $this->delete(route('curricula.destroy', $curriculum))->assertRedirect();

        $this->assertDatabaseMissing('curricula', ['id' => $curriculum->id]);
    }

    public function test_a_curriculum_review_can_be_registered_with_a_json_checklist(): void
    {
        $curriculum = Curriculum::create(['name' => 'Plan Base', 'version' => '2025-II']);
        $coteccu = User::factory()->create();

        $this->post('/tramites/revisiones-curriculares', [
            'curriculum_id' => $curriculum->id,
            'coteccu_user_id' => $coteccu->id,
            'review_checklist_data' => json_encode(['F-M01.01-DPA-009' => ['aprobado' => true]]),
            'decision' => 'revalidar',
            'state' => 'en_revision',
        ])->assertRedirect(route('curriculum-reviews.index'))->assertSessionHas('success');

        $review = CurriculumReview::firstOrFail();

        $this->assertSame('revalidar', $review->decision);
        $this->assertSame(['F-M01.01-DPA-009' => ['aprobado' => true]], $review->review_checklist_data);
    }
}
