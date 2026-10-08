@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan tugas kuliah dan deadline kamu')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Halo, {{ auth()->user()->name }} 👋</h2>
            <p class="text-sm text-slate-500">
                @if ($unfinishedCount > 0)
                    Kamu punya {{ $unfinishedCount }} tugas yang belum selesai. Semangat!
                @else
                    Semua tugas selesai. Kerja bagus! 🎉
                @endif
            </p>
        </div>

        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <x-icon name="plus" :size="16" /> Tambah Tugas
        </a>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="clipboard" :size="22" />
                </span>
                <div>
                    <p class="text-sm text-slate-500">Belum selesai</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $unfinishedCount }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-icon name="alarm" :size="22" />
                </span>
                <div>
                    <p class="text-sm text-slate-500">Segera deadline (3 hari)</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $upcomingSoonCount }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="check-circle" :size="22" />
                </span>
                <div>
                    <p class="text-sm text-slate-500">Sudah selesai</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $completedCount }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <x-icon name="calendar" :size="22" />
                </span>
                <div>
                    <p class="text-sm text-slate-500">Total tugas</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $totalTasks }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tugas terdekat --}}
    <div class="mt-6">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-slate-500">
            <x-icon name="clock" :size="16" class="text-brand-600" />
            Tugas terdekat
        </h3>

        @if ($nearest)
            @php
                $deadline = $nearest->deadlineAt();
                $priorityStyle = [
                    'low' => 'bg-slate-100 text-slate-600 ring-slate-200',
                    'medium' => 'bg-brand-50 text-brand-700 ring-brand-100',
                    'high' => 'bg-red-100 text-red-700 ring-red-200',
                ];
            @endphp

            <div class="card ring-2 ring-brand-500/40">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('tasks.show', $nearest) }}"
                                class="text-base font-semibold text-slate-900 hover:text-brand-600">{{ $nearest->title }}</a>
                            <span class="chip {{ $priorityStyle[$nearest->priority] }}">
                                Prioritas {{ $nearest->priorityLabel() }}
                            </span>
                        </div>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500">
                            <x-icon name="book" :size="15" class="text-brand-600" /> {{ $nearest->subject }}
                            @if ($nearest->lecturer)
                                <span class="text-slate-300">•</span> {{ $nearest->lecturer }}
                            @endif
                        </p>
                        <p class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                            <span class="flex items-center gap-1.5 font-medium text-brand-700">
                                <x-icon name="calendar" :size="16" /> {{ \App\Support\IndonesianDate::formatDate($deadline) }}
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-brand-700">
                                <x-icon name="clock" :size="16" /> {{ $deadline->format('H:i') }} WIB
                            </span>
                            <span class="flex items-center gap-1.5 text-slate-500">
                                <x-icon name="alarm" :size="16" /> {{ $nearest->reminderLabel() }}
                            </span>
                        </p>
                    </div>

                    <a href="{{ route('tasks.show', $nearest) }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                </div>
            </div>
        @else
            <div class="card text-center">
                <p class="text-sm text-slate-500">Belum ada tugas mendatang. Saatnya menambah tugas baru!</p>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm mt-3">
                    <x-icon name="plus" :size="14" /> Tambah Tugas
                </a>
            </div>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Berikutnya</h3>
                <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-brand-600 hover:underline">Lihat semua</a>
            </div>

            @if ($nextUpcoming->isEmpty())
                <div class="card text-sm text-slate-500">Tidak ada tugas mendatang.</div>
            @else
                <div class="space-y-4">
                    @foreach ($nextUpcoming as $task)
                        <x-task-card :task="$task" />
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Baru saja selesai</h3>

            @if ($recentlyCompleted->isEmpty())
                <div class="card text-sm text-slate-500">Belum ada tugas yang diselesaikan.</div>
            @else
                <div class="card divide-y divide-slate-100 p-0">
                    @foreach ($recentlyCompleted as $task)
                        <a href="{{ route('tasks.show', $task) }}"
                            class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <x-icon name="check" :size="16" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-slate-900">{{ $task->title }}</span>
                                <span class="block truncate text-xs text-slate-500">{{ $task->subject }}</span>
                            </span>
                            <span class="shrink-0 text-xs text-slate-400">
                                {{ \App\Support\IndonesianDate::shortDate($task->deadlineAt()) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
