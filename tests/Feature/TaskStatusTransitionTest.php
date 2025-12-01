<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function test_task_can_transition_from_todo_to_scheduled()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);

        $task->markAsScheduled();

        $this->assertEquals(TaskStatus::SCHEDULED, $task->fresh()->status);
    }

    /** @test */
    public function test_task_can_transition_from_todo_to_in_progress()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);

        $task->markAsInProgress();

        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->started_at);
    }

    /** @test */
    public function test_task_can_transition_from_in_progress_to_review()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::IN_PROGRESS,
            'created_by' => $this->user->id,
        ]);

        $task->markAsInReview();

        $this->assertEquals(TaskStatus::REVIEW, $task->fresh()->status);
    }

    /** @test */
    public function test_task_can_transition_from_review_to_done()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::REVIEW,
            'created_by' => $this->user->id,
        ]);

        $task->markAsDone();

        $this->assertEquals(TaskStatus::DONE, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    /** @test */
    public function test_task_can_transition_from_done_to_archived()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::DONE,
            'created_by' => $this->user->id,
            'completed_at' => now(),
        ]);

        $task->markAsArchived();

        $this->assertEquals(TaskStatus::ARCHIVED, $task->fresh()->status);
    }

    /** @test */
    public function test_task_can_be_blocked_from_any_active_state()
    {
        $states = [TaskStatus::TODO, TaskStatus::SCHEDULED, TaskStatus::IN_PROGRESS, TaskStatus::REVIEW];

        foreach ($states as $state) {
            $task = Task::factory()->create([
                'status' => $state,
                'created_by' => $this->user->id,
            ]);

            $task->markAsBlocked('Esperando recursos externos');

            $this->assertEquals(TaskStatus::BLOCKED, $task->fresh()->status);
            $this->assertEquals('Esperando recursos externos', $task->fresh()->blocked_reason);
        }
    }

    /** @test */
    public function test_task_can_be_cancelled_from_any_active_state()
    {
        $states = [TaskStatus::TODO, TaskStatus::SCHEDULED, TaskStatus::IN_PROGRESS, TaskStatus::BLOCKED, TaskStatus::REVIEW];

        foreach ($states as $state) {
            $task = Task::factory()->create([
                'status' => $state,
                'created_by' => $this->user->id,
            ]);

            $task->markAsCancelled();

            $this->assertEquals(TaskStatus::CANCELLED, $task->fresh()->status);
        }
    }

    /** @test */
    public function test_task_cannot_transition_from_todo_to_done_directly()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No se puede cambiar el estado de 'Pendiente' a 'Completada'. Transición no permitida.");

        $task->markAsDone();
    }

    /** @test */
    public function test_task_cannot_transition_from_scheduled_to_done_directly()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::SCHEDULED,
            'created_by' => $this->user->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $task->markAsDone();
    }

    /** @test */
    public function test_task_tracks_status_changes()
    {
        $this->actingAs($this->user);

        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);

        $task->markAsInProgress();

        $freshTask = $task->fresh();
        $this->assertEquals(TaskStatus::TODO, $freshTask->previous_status);
        $this->assertNotNull($freshTask->status_changed_at);
        $this->assertEquals($this->user->id, $freshTask->status_changed_by);
    }

    /** @test */
    public function test_blocked_task_can_be_unblocked()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::BLOCKED,
            'blocked_reason' => 'Esperando aprobación',
            'created_by' => $this->user->id,
        ]);

        $task->unblock();

        $freshTask = $task->fresh();
        $this->assertEquals(TaskStatus::TODO, $freshTask->status);
        $this->assertNull($freshTask->blocked_reason);
    }

    /** @test */
    public function test_unblocking_non_blocked_task_throws_exception()
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La tarea no está bloqueada.');

        $task->unblock();
    }

    /** @test */
    public function test_task_status_enum_returns_correct_labels()
    {
        $this->assertEquals('Pendiente', TaskStatus::TODO->label());
        $this->assertEquals('Programada', TaskStatus::SCHEDULED->label());
        $this->assertEquals('En Progreso', TaskStatus::IN_PROGRESS->label());
        $this->assertEquals('Bloqueada', TaskStatus::BLOCKED->label());
        $this->assertEquals('En Revisión', TaskStatus::REVIEW->label());
        $this->assertEquals('Completada', TaskStatus::DONE->label());
        $this->assertEquals('Cancelada', TaskStatus::CANCELLED->label());
        $this->assertEquals('Archivada', TaskStatus::ARCHIVED->label());
    }

    /** @test */
    public function test_task_status_enum_returns_correct_colors()
    {
        $this->assertEquals('gray', TaskStatus::TODO->color());
        $this->assertEquals('purple', TaskStatus::SCHEDULED->color());
        $this->assertEquals('blue', TaskStatus::IN_PROGRESS->color());
        $this->assertEquals('orange', TaskStatus::BLOCKED->color());
        $this->assertEquals('yellow', TaskStatus::REVIEW->color());
        $this->assertEquals('green', TaskStatus::DONE->color());
        $this->assertEquals('red', TaskStatus::CANCELLED->color());
        $this->assertEquals('slate', TaskStatus::ARCHIVED->color());
    }

    /** @test */
    public function test_task_status_enum_correctly_identifies_active_states()
    {
        $this->assertTrue(TaskStatus::TODO->isActive());
        $this->assertTrue(TaskStatus::SCHEDULED->isActive());
        $this->assertTrue(TaskStatus::IN_PROGRESS->isActive());
        $this->assertTrue(TaskStatus::BLOCKED->isActive());
        $this->assertTrue(TaskStatus::REVIEW->isActive());
        $this->assertFalse(TaskStatus::DONE->isActive());
        $this->assertFalse(TaskStatus::CANCELLED->isActive());
        $this->assertFalse(TaskStatus::ARCHIVED->isActive());
    }

    /** @test */
    public function test_task_status_enum_correctly_identifies_final_states()
    {
        $this->assertFalse(TaskStatus::TODO->isFinal());
        $this->assertFalse(TaskStatus::SCHEDULED->isFinal());
        $this->assertFalse(TaskStatus::IN_PROGRESS->isFinal());
        $this->assertFalse(TaskStatus::BLOCKED->isFinal());
        $this->assertFalse(TaskStatus::REVIEW->isFinal());
        $this->assertTrue(TaskStatus::DONE->isFinal());
        $this->assertTrue(TaskStatus::CANCELLED->isFinal());
        $this->assertTrue(TaskStatus::ARCHIVED->isFinal());
    }

    /** @test */
    public function test_task_can_follow_complete_workflow()
    {
        $this->actingAs($this->user);

        // Crear tarea
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);
        $this->assertEquals(TaskStatus::TODO, $task->status);

        // Programar
        $task->markAsScheduled();
        $this->assertEquals(TaskStatus::SCHEDULED, $task->fresh()->status);

        // Empezar a trabajar
        $task->markAsInProgress();
        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->started_at);

        // Enviar a revisión
        $task->markAsInReview();
        $this->assertEquals(TaskStatus::REVIEW, $task->fresh()->status);

        // Completar
        $task->markAsDone();
        $this->assertEquals(TaskStatus::DONE, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        // Archivar
        $task->markAsArchived();
        $this->assertEquals(TaskStatus::ARCHIVED, $task->fresh()->status);
    }

    /** @test */
    public function test_task_can_be_blocked_and_resumed()
    {
        $this->actingAs($this->user);

        $task = Task::factory()->create([
            'status' => TaskStatus::IN_PROGRESS,
            'created_by' => $this->user->id,
        ]);

        // Bloquear
        $task->markAsBlocked('Esperando información del cliente');
        $this->assertEquals(TaskStatus::BLOCKED, $task->fresh()->status);
        $this->assertEquals('Esperando información del cliente', $task->fresh()->blocked_reason);

        // Desbloquear
        $task->unblock();
        $this->assertEquals(TaskStatus::TODO, $task->fresh()->status);
        $this->assertNull($task->fresh()->blocked_reason);

        // Continuar con el flujo normal
        $task->markAsInProgress();
        $task->markAsDone();
        $this->assertEquals(TaskStatus::DONE, $task->fresh()->status);
    }

    /** @test */
    public function test_task_requires_confirmation_for_certain_transitions()
    {
        // Reabrir una tarea completada requiere confirmación
        $this->assertTrue(TaskStatus::DONE->requiresConfirmation(TaskStatus::IN_PROGRESS));

        // Reactivar una tarea cancelada requiere confirmación
        $this->assertTrue(TaskStatus::CANCELLED->requiresConfirmation(TaskStatus::TODO));

        // Desarchive requiere confirmación
        $this->assertTrue(TaskStatus::ARCHIVED->requiresConfirmation(TaskStatus::TODO));
        $this->assertTrue(TaskStatus::ARCHIVED->requiresConfirmation(TaskStatus::DONE));

        // Transiciones normales no requieren confirmación
        $this->assertFalse(TaskStatus::TODO->requiresConfirmation(TaskStatus::IN_PROGRESS));
        $this->assertFalse(TaskStatus::IN_PROGRESS->requiresConfirmation(TaskStatus::REVIEW));
        $this->assertFalse(TaskStatus::REVIEW->requiresConfirmation(TaskStatus::DONE));
    }

    /** @test */
    public function test_get_allowed_transitions_returns_correct_options()
    {
        $this->actingAs($this->admin);

        $task = Task::factory()->create([
            'status' => TaskStatus::TODO,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson(route('tasks.transitions', $task));

        $response->assertOk();
        $response->assertJsonStructure([
            'current_status' => ['value', 'label'],
            'allowed_transitions',
        ]);

        $data = $response->json();
        $this->assertEquals('todo', $data['current_status']['value']);
        $this->assertEquals('Pendiente', $data['current_status']['label']);

        $allowedValues = array_column($data['allowed_transitions'], 'value');
        $this->assertContains('scheduled', $allowedValues);
        $this->assertContains('in_progress', $allowedValues);
        $this->assertContains('cancelled', $allowedValues);
        $this->assertNotContains('done', $allowedValues); // No se puede ir directo de TODO a DONE
    }
}
