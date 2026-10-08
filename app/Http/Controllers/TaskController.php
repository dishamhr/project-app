<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Daftar tugas dengan filter status dan prioritas.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $filter = $request->query('filter', 'all');
        if (! is_string($filter) || ! array_key_exists($filter, Task::filterOptions())) {
            $filter = 'all';
        }

        $query = Task::forUser($user)->orderedByDeadline();

        match ($filter) {
            Task::STATUS_NOT_STARTED, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE => $query->where('status', $filter),
            'high' => $query->where('priority', Task::PRIORITY_HIGH),
            default => null,
        };

        $tasks = $query->get();

        return view('tasks.index', [
            'tasks' => $tasks,
            'filter' => $filter,
            'filters' => Task::filterOptions(),
            'counts' => [
                'all' => Task::forUser($user)->count(),
                Task::STATUS_NOT_STARTED => Task::forUser($user)->where('status', Task::STATUS_NOT_STARTED)->count(),
                Task::STATUS_IN_PROGRESS => Task::forUser($user)->where('status', Task::STATUS_IN_PROGRESS)->count(),
                Task::STATUS_DONE => Task::forUser($user)->where('status', Task::STATUS_DONE)->count(),
                'high' => Task::forUser($user)->where('priority', Task::PRIORITY_HIGH)->count(),
            ],
        ]);
    }

    /**
     * Tampilkan form tambah tugas.
     */
    public function create(Request $request): View
    {
        return view('tasks.create', [
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
            'reminders' => Task::reminderOptions(),
            'defaultReminder' => $request->user()->settings()->default_reminder_minutes,
        ]);
    }

    /**
     * Simpan tugas baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['status'] = $data['status'] ?? Task::STATUS_NOT_STARTED;

        $request->user()->tasks()->create($data);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tugas berhasil disimpan. Pengingat akan dikirim otomatis sesuai waktu yang Anda pilih.');
    }

    /**
     * Tampilkan detail tugas.
     */
    public function show(Request $request, Task $task): View
    {
        $this->authorizeTask($request, $task);

        return view('tasks.show', [
            'task' => $task,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
        ]);
    }

    /**
     * Tampilkan form edit tugas.
     */
    public function edit(Request $request, Task $task): View
    {
        $this->authorizeTask($request, $task);

        return view('tasks.edit', [
            'task' => $task,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
            'reminders' => Task::reminderOptions(),
        ]);
    }

    /**
     * Perbarui tugas.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request, $task);

        $data = $this->validated($request);

        $task->update($data);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Hapus tugas.
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request, $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Tandai tugas sebagai selesai.
     */
    public function complete(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request, $task);

        $task->update(['status' => Task::STATUS_DONE]);

        return back()->with('success', '🎉 Tugas berhasil ditandai selesai!');
    }

    /**
     * Validasi input form tugas.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'lecturer' => ['nullable', 'string', 'max:255'],
            'deadline_date' => ['required', 'date'],
            'deadline_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'priority' => ['required', Rule::in(array_keys(Task::priorities()))],
            'reminder_minutes' => ['required', Rule::in(Task::reminderMinutes())],
            'status' => ['nullable', Rule::in(array_keys(Task::statuses()))],
        ]);

        $data['deadline_time'] = substr($data['deadline_time'], 0, 5);
        $data['status'] = $data['status'] ?? Task::STATUS_NOT_STARTED;

        // Jadwal berubah -> aktifkan kembali pengingatnya.
        $data['reminded_at'] = null;

        return $data;
    }

    /**
     * Pastikan tugas milik mahasiswa yang sedang login.
     */
    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()?->id, 404);
    }
}
