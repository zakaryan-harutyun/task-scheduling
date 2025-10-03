<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyUserOfTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $taskId)
    {
    }

    public function handle(): void
    {
        $task = Task::with('user')->find($this->taskId);
        if (!$task) {
            return;
        }
        Log::info('Notify user of task change', [
            'task_id' => $task->id,
            'title' => $task->title,
            'user_id' => $task->user_id,
            'user_email' => $task->user?->email,
            'status' => $task->status,
            'start' => $task->start_date,
            'end' => $task->end_date,
        ]);
    }
}


