<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard mahasiswa.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $tasks = Task::forUser($user)->orderedByDeadline()->get();
        $now = now();

        $pending = $tasks->reject(fn (Task $task) => $task->isDone());

        $upcomingSoon = $pending->filter(function (Task $task) use ($now) {
            $deadline = $task->deadlineAt();

            return $deadline->greaterThanOrEqualTo($now)
                && $deadline->lessThanOrEqualTo($now->copy()->addDays(3));
        });

        $nearest = $pending->first(fn (Task $task) => $task->deadlineAt()->greaterThan($now));

        $nextUpcoming = $pending
            ->filter(fn (Task $task) => $task->deadlineAt()->greaterThan($now))
            ->take(5)
            ->values();

        $recentlyCompleted = $tasks
            ->filter(fn (Task $task) => $task->isDone())
            ->sortByDesc(fn (Task $task) => $task->updated_at?->getTimestamp() ?? 0)
            ->take(3)
            ->values();

        return view('dashboard', [
            'totalTasks' => $tasks->count(),
            'unfinishedCount' => $pending->count(),
            'upcomingSoonCount' => $upcomingSoon->count(),
            'completedCount' => $tasks->filter(fn (Task $task) => $task->isDone())->count(),
            'nearest' => $nearest,
            'nextUpcoming' => $nextUpcoming,
            'recentlyCompleted' => $recentlyCompleted,
        ]);
    }
}
