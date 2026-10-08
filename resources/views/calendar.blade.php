@extends('layouts.app')

@section('title', 'Kalender Tugas')
@section('subtitle', 'Lihat deadline tugasmu per tanggal')

@section('content')
    @php
        $weekDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $priorityStyle = [
            'low' => 'bg-slate-100 text-slate-600',
            'medium' => 'bg-brand-50 text-brand-700',
            'high' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="card overflow-hidden">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ \App\Support\IndonesianDate::MONTHS[$reference->month] }} {{ $reference->year }}
                </h2>
                <p class="text-sm text-slate-500">{{ $monthTasks->count() }} tugas pada bulan ini</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('calendar', ['month' => $prevMonth]) }}"
                    class="btn btn-secondary btn-sm" aria-label="Bulan sebelumnya">
                    <x-icon name="chevron-left" :size="14" />
                </a>
                <a href="{{ route('calendar', ['month' => now()->format('Y-m')]) }}" class="btn btn-secondary btn-sm">Hari Ini</a>
                <a href="{{ route('calendar', ['month' => $nextMonth]) }}"
                    class="btn btn-secondary btn-sm" aria-label="Bulan berikutnya">
                    <x-icon name="chevron-right" :size="14" />
                </a>
            </div>
        </div>

        <div class="-mx-1 overflow-x-auto pb-1">
            <div class="min-w-[640px]">
                <div class="grid grid-cols-7 gap-1">
                    @foreach ($weekDays as $dayName)
                        <div class="pb-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-1">
                    @foreach ($days as $day)
                        @php
                            $key = $day->toDateString();
                            $dayTasks = $tasksByDate[$key] ?? [];
                            $isCurrentMonth = $day->month === $reference->month;
                            $isToday = $day->isToday();
                        @endphp

                        <div class="min-h-[6rem] rounded-xl border p-1.5 transition
                            {{ $isToday ? 'border-brand-500 bg-brand-50/60 ring-1 ring-brand-500' : ($isCurrentMonth ? 'border-slate-200 bg-white' : 'border-slate-100 bg-slate-50/70') }}">

                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-semibold
                                    {{ $isToday ? 'flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white' : ($isCurrentMonth ? 'text-slate-700' : 'text-slate-400') }}">
                                    {{ $day->day }}
                                </span>

                                @if (count($dayTasks) > 0)
                                    <span class="text-[10px] font-medium text-brand-600">{{ count($dayTasks) }} tugas</span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                @foreach (array_slice($dayTasks, 0, 3) as $task)
                                    <a href="{{ route('tasks.show', $task) }}"
                                        title="{{ $task->title }} — {{ $task->deadlineAt()->format('H:i') }}"
                                        class="flex items-center gap-1 truncate rounded-md px-1.5 py-1 text-[11px] font-medium leading-tight transition hover:ring-1 hover:ring-brand-400 {{ $task->isDone() ? 'bg-emerald-50 text-emerald-700 line-through' : ($priorityStyle[$task->priority] ?? 'bg-slate-100 text-slate-600') }}">
                                        <span class="shrink-0 tabular-nums opacity-70">{{ $task->deadlineAt()->format('H:i') }}</span>
                                        <span class="truncate">{{ $task->title }}</span>
                                    </a>
                                @endforeach

                                @if (count($dayTasks) > 3)
                                    <span class="block px-1.5 text-[11px] text-slate-400">+{{ count($dayTasks) - 3 }} tugas lagi</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 text-xs text-slate-500">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span> Hari ini</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-brand-50 ring-1 ring-brand-200"></span> Prioritas sedang</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-red-50 ring-1 ring-red-200"></span> Prioritas tinggi</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-50 ring-1 ring-emerald-200"></span> Selesai</span>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Deadline bulan {{ \App\Support\IndonesianDate::MONTHS[$reference->month] }}
        </h3>

        @if ($monthTasks->isEmpty())
            <div class="card text-sm text-slate-500">Tidak ada tugas pada bulan ini.</div>
        @else
            @php($grouped = $monthTasks->groupBy(fn ($task) => $task->deadlineAt()->toDateString()))

            <div class="space-y-4">
                @foreach ($grouped as $date => $dayTasks)
                    <div class="card p-0">
                        <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-3 text-sm font-semibold text-slate-700">
                            <x-icon name="calendar" :size="16" class="text-brand-600" />
                            {{ \App\Support\IndonesianDate::formatDate(\Carbon\Carbon::parse($date)) }}
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach ($dayTasks as $task)
                                <a href="{{ route('tasks.show', $task) }}"
                                    class="flex flex-wrap items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                                    <span class="w-14 shrink-0 text-sm font-semibold tabular-nums text-brand-700">
                                        {{ $task->deadlineAt()->format('H:i') }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-slate-900">{{ $task->title }}</span>
                                        <span class="block truncate text-xs text-slate-500">{{ $task->subject }}</span>
                                    </span>
                                    <span class="chip {{ $priorityStyle[$task->priority] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $task->priorityLabel() }}
                                    </span>
                                    <span class="chip {{ $task->isDone() ? 'bg-emerald-100 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        {{ $task->statusLabel() }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
