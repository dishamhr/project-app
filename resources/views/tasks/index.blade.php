@extends('layouts.app')

@section('title', 'Daftar Tugas')
@section('subtitle', 'Semua tugas kuliahmu dalam satu tempat')

@section('content')
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
            @foreach ($filters as $key => $label)
                <a href="{{ route('tasks.index', ['filter' => $key]) }}"
                    class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium ring-1 transition {{ $filter === $key ? 'bg-brand-600 text-white ring-brand-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">
                    @if ($key === 'high')
                        <x-icon name="flag" :size="14" />
                    @endif
                    {{ $label }}
                    <span class="rounded-full px-1.5 py-0.5 text-xs {{ $filter === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>

        <a href="{{ route('tasks.create') }}" class="btn btn-primary self-start lg:self-auto">
            <x-icon name="plus" :size="16" /> Tambah Tugas
        </a>
    </div>

    @if ($tasks->isEmpty())
        <div class="card flex flex-col items-center gap-3 py-12 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                <x-icon name="clipboard" :size="26" />
            </span>
            <div>
                <p class="font-semibold text-slate-900">Belum ada tugas di filter ini</p>
                <p class="mt-1 text-sm text-slate-500">
                    @if ($filter === 'all')
                        Yuk, catat tugas pertamamu agar tidak lupa deadline.
                    @else
                        Tidak ada tugas dengan status/filter tersebut.
                    @endif
                </p>
            </div>
            @if ($filter === 'all')
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <x-icon name="plus" :size="16" /> Tambah Tugas
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($tasks as $task)
                <x-task-card :task="$task" />
            @endforeach
        </div>
    @endif
@endsection
