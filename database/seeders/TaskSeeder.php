<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table with a realistic mix of sample data.
     */
    public function run(): void
    {
        // A handful of hand-picked tasks so the dashboard looks meaningful immediately.
        Task::factory()->todo()->create([
            'title' => 'Update homepage banner',
            'description' => 'Replace the hero image and refresh the call-to-action copy.',
            'priority' => 'high',
            'due_date' => now()->addDays(2),
        ]);

        Task::factory()->inProgress()->create([
            'title' => 'Fix API rate limiting issue',
            'description' => 'Investigate the 429 errors reported by the mobile team.',
            'priority' => 'medium',
            'due_date' => now()->addDays(4),
        ]);

        Task::factory()->completed()->create([
            'title' => 'Write onboarding documentation',
            'description' => 'Draft the new team onboarding guide.',
            'priority' => 'low',
            'due_date' => now()->subDays(3),
        ]);

        Task::factory()->overdue()->create([
            'title' => 'Renew SSL certificate',
            'description' => 'The production certificate expires soon and needs renewal.',
            'priority' => 'high',
        ]);

        Task::factory()->noDueDate()->create([
            'title' => 'Explore new logging service',
            'description' => 'Research alternatives to the current logging provider.',
            'priority' => 'low',
        ]);

        // Bulk of randomized tasks covering every status/priority/due-date combination.
        Task::factory()->count(6)->todo()->create();
        Task::factory()->count(4)->inProgress()->create();
        Task::factory()->count(5)->completed()->create();
        Task::factory()->count(3)->overdue()->create();
        Task::factory()->count(2)->noDueDate()->create();
    }
}
