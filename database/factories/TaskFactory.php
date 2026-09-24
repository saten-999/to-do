<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(array_keys(Task::STATUSES));

        return [
            'title' => rtrim($this->faker->sentence(4), '.'),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'status' => $status,
            'priority' => $this->faker->randomElement(array_keys(Task::PRIORITIES)),
            'due_date' => $this->faker->optional(0.75)->dateTimeBetween('-2 weeks', '+3 weeks'),
            'completed_at' => $status === Task::STATUS_COMPLETED
                ? $this->faker->dateTimeBetween('-2 weeks', 'now')
                : null,
        ];
    }

    /**
     * Task with a status of "to do".
     */
    public function todo(): static
    {
        return $this->state(fn() => [
            'status' => Task::STATUS_TODO,
            'completed_at' => null,
        ]);
    }

    /**
     * Task with a status of "in progress".
     */
    public function inProgress(): static
    {
        return $this->state(fn() => [
            'status' => Task::STATUS_IN_PROGRESS,
            'completed_at' => null,
        ]);
    }

    /**
     * Task marked as completed.
     */
    public function completed(): static
    {
        return $this->state(fn() => [
            'status' => Task::STATUS_COMPLETED,
            'completed_at' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
        ]);
    }

    /**
     * Task with a due date in the past that is not completed.
     */
    public function overdue(): static
    {
        return $this->state(fn() => [
            'status' => $this->faker->randomElement([Task::STATUS_TODO, Task::STATUS_IN_PROGRESS]),
            'due_date' => $this->faker->dateTimeBetween('-3 weeks', '-1 day'),
            'completed_at' => null,
        ]);
    }

    /**
     * Task without a due date.
     */
    public function noDueDate(): static
    {
        return $this->state(fn() => [
            'due_date' => null,
        ]);
    }
}
