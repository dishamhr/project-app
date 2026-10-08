<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Kalender tugas berdasarkan tanggal deadline.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $reference = $this->referenceMonth($request->query('month'));

        $tasks = Task::forUser($user)
            ->whereYear('deadline_date', $reference->year)
            ->whereMonth('deadline_date', $reference->month)
            ->orderedByDeadline()
            ->get();

        $tasksByDate = [];
        foreach ($tasks as $task) {
            $tasksByDate[$task->deadlineAt()->toDateString()][] = $task;
        }

        $start = $reference->copy()->startOfMonth()->startOfWeek();
        $days = [];
        for ($i = 0; $i < 42; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        return view('calendar', [
            'reference' => $reference,
            'days' => $days,
            'tasksByDate' => $tasksByDate,
            'monthTasks' => $tasks,
            'prevMonth' => $reference->copy()->subMonthNoOverflow()->format('Y-m'),
            'nextMonth' => $reference->copy()->addMonthNoOverflow()->format('Y-m'),
        ]);
    }

    /**
     * Ambil bulan tujuan dari query string ?month=YYYY-MM.
     *
     * @param  mixed  $month
     */
    private function referenceMonth($month): Carbon
    {
        if (is_string($month) && preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
            $year = (int) $matches[1];
            $monthNumber = (int) $matches[2];

            if ($monthNumber >= 1 && $monthNumber <= 12) {
                return Carbon::create($year, $monthNumber, 1);
            }
        }

        return Carbon::now();
    }
}
