<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [['id', 'title', 'status', 'priority', 'due_date', 'is_overdue']],
            'links',
            'meta',
        ]);
    }

    public function test_it_returns_task_statistics(): void
    {
        Task::factory()->todo()->create();
        Task::factory()->completed()->count(2)->create();

        $response = $this->getJson('/api/tasks/stats');

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'total' => 3,
                'todo' => 1,
                'completed' => 2,
            ],
        ]);
    }

    public function test_it_creates_a_task(): void
    {
        $payload = [
            'title' => 'Ship the API',
            'description' => 'Expose tasks over JSON.',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addWeek()->toDateString(),
        ];

        $response = $this->postJson('/api/tasks', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'Ship the API');
        $this->assertDatabaseHas('tasks', ['title' => 'Ship the API']);
    }

    public function test_it_rejects_invalid_task_payload(): void
    {
        $response = $this->postJson('/api/tasks', ['title' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'status', 'priority']);
    }

    public function test_it_shows_a_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $task->id);
    }

    public function test_it_updates_a_task(): void
    {
        $task = Task::factory()->todo()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated via API',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'Updated via API');
        $response->assertJsonPath('data.status', 'in_progress');
    }

    public function test_it_deletes_a_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJson(['message' => 'Task deleted successfully.']);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_it_completes_and_reopens_a_task(): void
    {
        $task = Task::factory()->todo()->create();

        $completeResponse = $this->patchJson("/api/tasks/{$task->id}/complete");
        $completeResponse->assertOk();
        $completeResponse->assertJsonPath('data.status', 'completed');
        $this->assertNotNull($task->fresh()->completed_at);

        $reopenResponse = $this->patchJson("/api/tasks/{$task->id}/reopen");
        $reopenResponse->assertOk();
        $reopenResponse->assertJsonPath('data.status', 'todo');
        $this->assertNull($task->fresh()->completed_at);
    }
}
