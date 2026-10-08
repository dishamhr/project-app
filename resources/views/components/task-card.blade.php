@props(['task'])

@php
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

    $deadline = $task->deadlineAt();
    $isOverdue = ! $task->isDone() && $deadline->isPast();
@endphp

<article {{ $attributes->merge(['class' => 'card transition hover:shadow-md']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('tasks.show', $task) }}" class="block truncate font-semibold text-slate-900 hover:text-brand-600">
                {{ $task->title }}
            </a>
            <p class="mt-1 flex items-center gap-1.5 truncate text-sm text-slate-500">
                <x-icon name="book" :size="15" class="text-brand-600" />
                {{ $task->subject }}
            </p>
        </div>

        <span class="chip shrink-0 {{ $statusStyle[$task->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
            {{ $task->statusLabel() }}
        </span>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
        <span class="flex items-center gap-1.5 {{ $isOverdue ? 'text-red-600' : 'text-slate-600' }}">
            <x-icon name="calendar" :size="15" class="text-brand-600" />
            {{ \App\Support\IndonesianDate::shortDate($deadline) }}
        </span>
        <span class="flex items-center gap-1.5 {{ $isOverdue ? 'text-red-600' : 'text-slate-600' }}">
            <x-icon name="clock" :size="15" class="text-brand-600" />
            {{ $deadline->format('H:i') }} WIB
        </span>
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="chip {{ $priorityStyle[$task->priority] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
            <x-icon name="flag" :size="12" />
            Prioritas {{ $task->priorityLabel() }}
        </span>
        <span class="flex items-center gap-1 text-xs text-slate-500">
            <x-icon name="alarm" :size="14" class="text-brand-500" />
            {{ $task->reminderLabel() }}
        </span>
        @if ($isOverdue)
            <span class="chip bg-red-50 text-red-600 ring-red-200">Lewat deadline</span>
        @endif
    </div>

    <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
        <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary btn-sm">
            <x-icon name="eye" :size="14" /> Detail
        </a>
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
</article>
