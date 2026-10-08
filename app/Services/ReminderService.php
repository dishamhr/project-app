<?php

namespace App\Services;

use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use App\Support\IndonesianDate;
use Carbon\CarbonInterface;

class ReminderService
{
    /**
     * Kirim semua pengingat yang sudah jatuh tempo untuk seluruh mahasiswa.
     */
    public function dispatchDue(): int
    {
        $total = 0;

        User::query()->chunkById(100, function ($users) use (&$total) {
            foreach ($users as $user) {
                $total += $this->dispatchDueFor($user);
            }
        });

        return $total;
    }

    /**
     * Kirim pengingat yang sudah jatuh tempo untuk satu mahasiswa.
     */
    public function dispatchDueFor(User $user): int
    {
        if (! $user->settings()->notifications_enabled) {
            return 0;
        }

        $now = now();
        $sent = 0;

        Task::query()
            ->forUser($user)
            ->pendingReminder()
            ->orderedByDeadline()
            ->get()
            ->each(function (Task $task) use ($now, &$sent) {
                $remindAt = $task->remindAt();
                $deadline = $task->deadlineAt();

                // Belum waktunya, atau deadline sudah lewat -> lewati.
                if ($remindAt->greaterThan($now) || $deadline->lessThanOrEqualTo($now)) {
                    return;
                }

                $this->send($task, $remindAt);
                $sent++;
            });

        return $sent;
    }

    /**
     * Buat record pengingat dan tandai tugas sudah dikirim.
     */
    public function send(Task $task, CarbonInterface $remindAt): Reminder
    {
        $reminder = $task->reminders()->create([
            'user_id' => $task->user_id,
            'title' => '🔔 Pengingat Tugas',
            'message' => $this->messageFor($task),
            'scheduled_at' => $remindAt,
            'sent_at' => now(),
        ]);

        $task->forceFill(['reminded_at' => now()])->save();

        return $reminder;
    }

    /**
     * Isi pesan notifikasi sesuai format aplikasi.
     */
    public function messageFor(Task $task): string
    {
        $deadline = $task->deadlineAt();

        return implode("\n", [
            "Tugas {$task->title} akan deadline ".IndonesianDate::relative(IndonesianDate::daysUntil($deadline)).'.',
            '',
            "Mata Kuliah: {$task->subject}",
            'Deadline: '.IndonesianDate::formatDateTime($deadline),
            'Prioritas: '.$task->priorityLabel(),
        ]);
    }
}
