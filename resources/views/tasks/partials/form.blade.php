@php
    $isEdit = isset($task);
    $formAction = $isEdit ? route('tasks.update', $task) : route('tasks.store');

    $selectedDate = old('deadline_date', $isEdit ? $task->deadline_date : now()->addDays(7)->toDateString());
    $selectedTime = old('deadline_time', $isEdit ? $task->deadline_time : '23:59');
    $selectedReminder = (int) old('reminder_minutes', $isEdit ? $task->reminder_minutes : ($defaultReminder ?? 1440));

    try {
        $reminderPreview = \Carbon\Carbon::parse("{$selectedDate} {$selectedTime}")->subMinutes($selectedReminder);
    } catch (\Throwable $e) {
        $reminderPreview = null;
    }
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-5">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="label" for="title">Nama Tugas</label>
            <input id="title" type="text" name="title" value="{{ old('title', $isEdit ? $task->title : '') }}"
                required maxlength="255" class="input" placeholder="Contoh: Makalah Cybercrime">
        </div>

        <div>
            <label class="label" for="subject">Mata Kuliah</label>
            <input id="subject" type="text" name="subject" value="{{ old('subject', $isEdit ? $task->subject : '') }}"
                required maxlength="255" class="input" placeholder="Contoh: Keamanan Komputer">
        </div>

        <div>
            <label class="label" for="lecturer">Dosen</label>
            <input id="lecturer" type="text" name="lecturer" value="{{ old('lecturer', $isEdit ? $task->lecturer : '') }}"
                maxlength="255" class="input" placeholder="Contoh: Dr. Andi Pratama">
        </div>

        <div class="sm:col-span-2">
            <label class="label" for="description">Deskripsi Tugas</label>
            <textarea id="description" name="description" rows="4" maxlength="5000" class="input"
                placeholder="Jelaskan tugasnya secara singkat...">{{ old('description', $isEdit ? $task->description : '') }}</textarea>
        </div>

        <div>
            <label class="label" for="deadline_date">Deadline Tanggal</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="calendar" :size="16" />
                </span>
                <input id="deadline_date" type="date" name="deadline_date" value="{{ $selectedDate }}" required
                    class="input pl-9">
            </div>
        </div>

        <div>
            <label class="label" for="deadline_time">Deadline Jam</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="clock" :size="16" />
                </span>
                <input id="deadline_time" type="time" name="deadline_time" value="{{ $selectedTime }}" required
                    class="input pl-9">
            </div>
        </div>

        <div>
            <label class="label" for="priority">Prioritas</label>
            <select id="priority" name="priority" class="input" required>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', $isEdit ? $task->priority : 'medium') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label" for="reminder_minutes">Waktu Pengingat</label>
            <select id="reminder_minutes" name="reminder_minutes" class="input" required>
                @foreach ($reminders as $minutes => $label)
                    <option value="{{ $minutes }}" @selected($selectedReminder === (int) $minutes)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1.5 text-xs text-slate-500">
                <x-icon name="alarm" :size="13" class="inline text-brand-500" />
                Pengingat dikirim pada
                <span data-reminder-preview class="font-medium text-brand-700">
                    {{ $reminderPreview ? \App\Support\IndonesianDate::formatDateTime($reminderPreview) : '-' }}
                </span>
            </p>
        </div>

        @if ($isEdit)
            <div class="sm:col-span-2">
                <label class="label" for="status">Status Tugas</label>
                <select id="status" name="status" class="input" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">
        <button type="submit" class="btn btn-primary">
            <x-icon name="check" :size="16" />
            {{ $isEdit ? 'Perbarui Tugas' : 'Simpan Tugas' }}
        </button>
        <a href="{{ $isEdit ? route('tasks.show', $task) : route('tasks.index') }}" class="btn btn-secondary">Batal</a>
    </div>
</form>
