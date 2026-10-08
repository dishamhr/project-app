<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    /**
     * Ambil pengingat belum dibaca + picu pengecekan jadwal pengingat.
     */
    public function unread(Request $request, ReminderService $reminders): JsonResponse
    {
        $user = $request->user();

        // Pengecekan lazy: memastikan pengingat muncul walau scheduler belum jalan.
        $reminders->dispatchDueFor($user);

        $items = $user->reminders()
            ->unread()
            ->with('task:id,title')
            ->latest('sent_at')
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $user->reminders()->unread()->count(),
            'sound' => (bool) $user->settings()->sound_enabled,
            'items' => $items->map(fn (Reminder $reminder) => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'message' => $reminder->message,
                'url' => $reminder->task
                    ? route('tasks.show', $reminder->task)
                    : route('tasks.index'),
                'sent_at' => $reminder->sent_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Tandai satu pengingat sudah dibaca.
     */
    public function markRead(Request $request, Reminder $reminder): JsonResponse
    {
        abort_unless($reminder->user_id === $request->user()->id, 404);

        $reminder->markAsRead();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Tandai semua pengingat sudah dibaca.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->reminders()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
