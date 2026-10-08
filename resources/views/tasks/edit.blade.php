@extends('layouts.app')

@section('title', 'Edit Tugas')
@section('subtitle', 'Perbarui informasi tugas: ' . $task->title)

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="card">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="pencil" :size="22" />
                </span>
                <div>
                    <h2 class="font-semibold text-slate-900">Edit Tugas</h2>
                    <p class="text-sm text-slate-500">Mengubah jadwal akan mengaktifkan kembali pengingat tugas.</p>
                </div>
            </div>

            @include('tasks.partials.form')
        </div>
    </div>
@endsection
