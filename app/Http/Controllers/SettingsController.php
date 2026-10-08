<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan notifikasi.
     */
    public function show(Request $request): View
    {
        return view('settings', [
            'settings' => $request->user()->settings(),
            'reminders' => Task::reminderOptions(),
        ]);
    }

    /**
     * Simpan pengaturan notifikasi mahasiswa.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notifications_enabled' => ['required', 'boolean'],
            'sound_enabled' => ['required', 'boolean'],
            'default_reminder_minutes' => ['required', Rule::in(Task::reminderMinutes())],
        ]);

        $request->user()->settings()->update($validated);

        return redirect()
            ->route('settings.show')
            ->with('success', 'Pengaturan notifikasi berhasil disimpan.');
    }
}
