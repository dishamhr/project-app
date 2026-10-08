@extends('layouts.app')

@section('title', 'Tambah Tugas')
@section('subtitle', 'Catat tugas baru beserta deadline dan pengingatnya')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="card">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="plus" :size="22" />
                </span>
                <div>
                    <h2 class="font-semibold text-slate-900">Tambah Tugas Baru</h2>
                    <p class="text-sm text-slate-500">Isi informasi tugas, tentukan deadline dan waktu pengingat.</p>
                </div>
            </div>

            @include('tasks.partials.form')
        </div>
    </div>
@endsection
