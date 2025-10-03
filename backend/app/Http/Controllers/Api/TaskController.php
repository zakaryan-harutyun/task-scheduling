<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateUserAvailabilityJob;
use App\Events\TaskAssigned;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('assignee')) {
            $query->where('user_id', $request->integer('assignee'));
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->whereFullText(['title', 'description'], $q);
        }

        return $query->orderByDesc('id')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string'],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $this->assertNoOverlap($data['user_id'], $data['start_date'], $data['end_date']);

        $task = Task::create($data);

        UpdateUserAvailabilityJob::dispatch($task->user_id);
        TaskAssigned::dispatch($task);

        return response()->json($task->load('user'), 201);
    }

    public function show(Task $task)
    {
        return $task->load('user');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string'],
            'user_id' => ['sometimes', 'integer', Rule::exists('users', 'id')],
        ]);

        $userId = $data['user_id'] ?? $task->user_id;
        $start = $data['start_date'] ?? $task->start_date;
        $end = $data['end_date'] ?? $task->end_date;

        $this->assertNoOverlap($userId, $start, $end, $task->id);

        $task->fill($data)->save();

        UpdateUserAvailabilityJob::dispatch($userId);
        TaskAssigned::dispatch($task);

        return $task->fresh()->load('user');
    }

    public function destroy(Task $task)
    {
        $userId = $task->user_id;
        $task->delete();
        UpdateUserAvailabilityJob::dispatch($userId);
        return response()->noContent();
    }

    public function reassign(Request $request, Task $task)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $this->assertNoOverlap($data['user_id'], $task->start_date, $task->end_date, $task->id);

        $task->user_id = $data['user_id'];
        $task->save();

        UpdateUserAvailabilityJob::dispatch($task->user_id);
        TaskAssigned::dispatch($task);

        return $task->fresh()->load('user');
    }

    private function assertNoOverlap(int $userId, $start, $end, ?int $ignoreTaskId = null): void
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $overlap = Task::query()
            ->where('user_id', $userId)
            ->when($ignoreTaskId, fn($q) => $q->where('id', '!=', $ignoreTaskId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })
            ->exists();

        if ($overlap) {
            abort(422, 'User has an overlapping task in the selected date range.');
        }
    }
}


