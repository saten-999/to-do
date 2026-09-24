<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const STATUSES = [
        self::STATUS_TODO => 'To Do',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_COMPLETED => 'Completed',
    ];

    public const PRIORITIES = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_MEDIUM => 'Medium',
        self::PRIORITY_HIGH => 'High',
    ];

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_COMPLETED
            && $this->due_date !== null
            && $this->due_date->isPast()
            && ! $this->due_date->isToday();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status) || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        if (blank($priority) || $priority === 'all') {
            return $query;
        }

        return $query->where('priority', $priority);
    }

    public function scopeDueFilter(Builder $query, ?string $filter): Builder
    {
        return match ($filter) {
            'overdue' => $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->where('status', '!=', self::STATUS_COMPLETED),
            'today' => $query->whereDate('due_date', now()->toDateString()),
            'this_week' => $query->whereBetween('due_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]),
            'no_due_date' => $query->whereNull('due_date'),
            default => $query,
        };
    }
}
