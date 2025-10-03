<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\UserAvailability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserAvailabilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $userId)
    {
    }

    public function handle(): void
    {
        // Recompute availability as the convex hull of task ranges for the user
        $tasks = Task::query()
            ->where('user_id', $this->userId)
            ->orderBy('start_date')
            ->get(['start_date', 'end_date']);

        UserAvailability::query()->where('user_id', $this->userId)->delete();

        $currentStart = null;
        $currentEnd = null;

        foreach ($tasks as $task) {
            if ($currentStart === null) {
                $currentStart = $task->start_date;
                $currentEnd = $task->end_date;
                continue;
            }

            if ($task->start_date <= $currentEnd) {
                if ($task->end_date > $currentEnd) {
                    $currentEnd = $task->end_date;
                }
            } else {
                UserAvailability::query()->create([
                    'user_id' => $this->userId,
                    'start_date' => $currentStart,
                    'end_date' => $currentEnd,
                ]);
                $currentStart = $task->start_date;
                $currentEnd = $task->end_date;
            }
        }

        if ($currentStart !== null) {
            UserAvailability::query()->create([
                'user_id' => $this->userId,
                'start_date' => $currentStart,
                'end_date' => $currentEnd,
            ]);
        }
    }
}


