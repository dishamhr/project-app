@extends('layouts.app')

@section('title', 'Detail Tugas')
@section('subtitle', $task->title)

@section('content')
    @php
        $deadline = $task->deadlineAt();
        $remindAt = $task->remindAt();
        $statusStyle = [
            'not_started' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'in_progress' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'done' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        ];
        $priorityStyle = [
            'low' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'medium' => 'bg-brand-50 text-brand-700 ring-brand-100',
            'high' => 'bg-red-100 text-red-700 ring-red-200',
        ];
        $notificationsOn = auth()->user()->settings()->notifications_enabled;
    @endphp

    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
                <x-icon name="chevron-left" :size="14" /> Kembali ke daftar
            </a>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary btn-sm">
                    <x-icon name="pencil" :size="14" /> Edit
                </a>

                @unless ($task->isDone())
                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">
                            <x-icon name="check" :size="14" /> Tandai Selesai
                        </button>
                    </form>
                @endunless

                <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                    data-confirm="Yakin hapus tugas &quot;{{ $task->title }}&quot;?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <x-icon name="trash" :size="14" /> Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-5">
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-slate-900">{{ $task->title }}</h2>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500">
                        <x-icon name="book" :size="16" class="text-brand-600" /> {{ $task->subject }}
                        @if ($task->lecturer)
                            <span class="text-slate-300">•</span>
                            <x-icon name="user" :size="15" class="text-brand-500" /> {{ $task->lecturer }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="chip {{ $statusStyle[$task->status] }}">{{ $task->statusLabel() }}</span>
                    <span class="chip {{ $priorityStyle[$task->priority] }}">
                        <x-icon name="flag" :size="12" /> Prioritas {{ $task->priorityLabel() }}
                    </span>
                </div>
            </div>

            <dl class="grid grid-cols-1 gap-4 py-5 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <dt class="flex items-center gap-1.5 text-xs uppercase tracking-wide text-slate-500">
                        <x-icon name="calendar" :size="14" class="text-brand-600" /> Tanggal Deadline
                    </dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                        {{ \App\Support\IndonesianDate::formatDate($deadline) }}
                    </dd>
                </div>

                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <dt class="flex items-center gap-1.5 text-xs uppercase tracking-wide text-slate-500">
                        <x-icon name="clock" :size="14" class="text-brand-600" /> Jam Deadline
                    </dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $deadline->format('H:i') }} WIB</dd>
                </div>

                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <dt class="flex items-center gap-1.5 text-xs uppercase tracking-wide text-slate-500">
                        <x-icon name="alarm" :size="14" class="text-brand-600" /> Waktu Pengingat
                    </dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $task->reminderLabel() }}</dd>
                </div>

                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <dt class="flex items-center gap-1.5 text-xs uppercase tracking-wide text-slate-500">
                        <x-icon name="check-circle" :size="14" class="text-brand-600" /> Status
                    </dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $task->statusLabel() }}</dd>
                </div>
            </dl>

            @if ($task->description)
                <div class="border-t border-slate-100 pt-5">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deskripsi</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $task->description }}</p>
                </div>
            @endif

            <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-brand-100 bg-brand-50 px-4 py-4 sm:flex-row sm:items-center">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <x-icon name="alarm" :size="20" />
                </span>
                <div class="text-sm text-brand-800">
                    <p class="font-semibold">
                        {{ $task->isDone() ? 'Tugas sudah selesai — pengingat dinonaktifkan.' : 'Pengingat dikirim otomatis' }}
                    </p>
                    @unless ($task->isDone())
                        <p class="text-brand-700">
                            {{ $remindAt->isPast() ? 'Waktu pengingat sudah lewat.' : 'Pada' }}
                            {{ $remindAt->isFuture() ? \App\Support\IndonesianDate::formatDateTime($remindAt) . ' WIB' : '' }}
                            — {{ $task->reminderLabel() }}.
                        </p>
                    @endunless
                    @unless ($notificationsOn)
                        <p class="mt-1 text-red-600">
                            Notifikasi kamu sedang <strong>nonaktif</strong>.
                            <a href="{{ route('settings.show') }}" class="font-semibold underline">Aktifkan di Pengaturan</a>
                        </p>
                    @endunless
                </div>
            </div>

            <p class="mt-4 text-xs text-slate-400">
                Dibuat {{ $task->created_at->diffForHumans() }} · Terakhir diperbarui {{ $task->updated_at->diffForHumans() }}
            </p>
        </div>
    </div>
@endsection
