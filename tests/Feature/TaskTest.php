<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_task_statistics(): void
    {
        Task::factory()->todo()->create(['due_date' => now()->addWeek()]);
        Task::factory()->inProgress()->count(2)->create(['due_date' => now()->addWeek()]);
        Task::factory()->completed()->count(3)->create(['due_date' => now()->subWeek()]);
        Task::factory()->overdue()->create(['status' => 'todo']);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $stats = $response->viewData('stats');

        $this->assertEquals(7, $stats['total']);
        $this->assertEquals(2, $stats['todo']);
        $this->assertEquals(2, $stats['in_progress']);
        $this->assertEquals(3, $stats['completed']);
        $this->assertEquals(1, $stats['overdue']);
    }

    public function test_task_can_be_created(): void
    {
        $payload = [
            'title' => 'Write project report',
            'description' => 'Summarize progress for stakeholders.',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addWeek()->toDateString(),
        ];

        $response = $this->post(route('tasks.store'), $payload);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tasks', [
            'title' => 'Write project report',
            'status' => 'todo',
            'priority' => 'high',
        ]);
    }

    public function test_task_creation_requires_a_title(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => '',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_creation_validates_status_and_priority_values(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => 'Invalid task',
            'status' => 'not-a-status',
            'priority' => 'not-a-priority',
        ]);

        $response->assertSessionHasErrors(['status', 'priority']);
    }

    public function test_task_can_be_viewed(): void
    {
        $task = Task::factory()->create(['title' => 'Review pull requests']);

        $response = $this->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSee('Review pull requests');
    }

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->todo()->create();

        $response = $this->put(route('tasks.update', $task), [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'status' => 'in_progress',
            'priority' => 'low',
            'due_date' => null,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'status' => 'in_progress',
        ]);
    }

    public function test_updating_task_to_completed_sets_completed_at(): void
    {
        $task = Task::factory()->todo()->create();

        $this->put(route('tasks.update', $task), [
            'title' => $task->title,
            'status' => 'completed',
            'priority' => $task->priority,
        ]);

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->create();

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_can_be_marked_completed(): void
    {
        $task = Task::factory()->todo()->create();

        $response = $this->patch(route('tasks.complete', $task));

        $response->assertRedirect();
        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_completed_task_can_be_reopened(): void
    {
        $task = Task::factory()->completed()->create();

        $response = $this->patch(route('tasks.reopen', $task));

        $response->assertRedirect();
        $task->refresh();
        $this->assertEquals('todo', $task->status);
        $this->assertNull($task->completed_at);
    }

    public function test_tasks_can_be_searched_by_title_or_description(): void
    {
        Task::factory()->create(['title' => 'Fix login bug', 'description' => 'Users cannot log in.']);
        Task::factory()->create(['title' => 'Design new logo', 'description' => 'Something unrelated.']);
        Task::factory()->create(['title' => 'Something else', 'description' => 'Mentions login issue here.']);

        $response = $this->get(route('tasks.index', ['search' => 'login']));

        $response->assertOk();
        $tasks = $response->viewData('tasks');
        $this->assertCount(2, $tasks);
    }

    public function test_tasks_can_be_filtered_by_status_and_priority(): void
    {
        Task::factory()->todo()->create(['priority' => 'high']);
        Task::factory()->todo()->create(['priority' => 'low']);
        Task::factory()->inProgress()->create(['priority' => 'high']);

        $response = $this->get(route('tasks.index', ['status' => 'todo', 'priority' => 'high']));

        $tasks = $response->viewData('tasks');
        $this->assertCount(1, $tasks);
        $this->assertEquals('todo', $tasks->first()->status);
        $this->assertEquals('high', $tasks->first()->priority);
    }

    public function test_tasks_can_be_filtered_by_overdue_due_date(): void
    {
        Task::factory()->overdue()->create();
        Task::factory()->todo()->create(['due_date' => now()->addWeek()]);

        $response = $this->get(route('tasks.index', ['due' => 'overdue']));

        $tasks = $response->viewData('tasks');
        $this->assertCount(1, $tasks);
    }

    public function test_tasks_can_be_sorted_by_title_ascending(): void
    {
        Task::factory()->create(['title' => 'Zebra task']);
        Task::factory()->create(['title' => 'Alpha task']);

        $response = $this->get(route('tasks.index', ['sort' => 'title', 'direction' => 'asc']));

        $tasks = $response->viewData('tasks');
        $this->assertEquals('Alpha task', $tasks->first()->title);
    }

    public function test_filters_are_combinable(): void
    {
        Task::factory()->inProgress()->create([
            'priority' => 'high',
            'due_date' => now()->addDays(2),
        ]);
        Task::factory()->inProgress()->create([
            'priority' => 'low',
            'due_date' => now()->addDays(2),
        ]);
        Task::factory()->todo()->create([
            'priority' => 'high',
            'due_date' => now()->addDays(2),
        ]);

        $response = $this->get(route('tasks.index', [
            'status' => 'in_progress',
            'priority' => 'high',
            'due' => 'this_week',
        ]));

        $tasks = $response->viewData('tasks');
        $this->assertCount(1, $tasks);
        $this->assertEquals('in_progress', $tasks->first()->status);
        $this->assertEquals('high', $tasks->first()->priority);
    }
}
