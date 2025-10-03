<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function show(User $user, Request $request)
    {
        $start = $request->date('start');
        $end = $request->date('end');

        $tasks = Task::query()
            ->where('user_id', $user->id)
            ->when($start && $end, function ($q) use ($start, $end) {
                $start = Carbon::parse($start);
                $end = Carbon::parse($end);
                $q->where(function ($q2) use ($start, $end) {
                    $q2->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q3) use ($start, $end) {
                            $q3->where('start_date', '<=', $start)
                               ->where('end_date', '>=', $end);
                        });
                });
            })
            ->orderBy('start_date')
            ->get(['id', 'title', 'start_date', 'end_date', 'status']);

        return [
            'user' => $user,
            'busy' => $tasks,
        ];
    }
}


