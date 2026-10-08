@extends('layouts.guest')

@section('title', 'Registrasi')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Buat akun mahasiswa 🎓</h1>
        <p class="mt-1 text-sm text-slate-500">Gratis, dan tugasmu tidak akan pernah terlupakan.</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="label" for="name">Nama</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="user" :size="16" />
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    autocomplete="name" class="input pl-9" placeholder="Nama lengkap">
            </div>
        </div>

        <div>
            <label class="label" for="email">Email</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="mail" :size="16" />
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    autocomplete="email" class="input pl-9" placeholder="nama@kampus.ac.id">
            </div>
        </div>

        <div>
            <label class="label" for="password">Password</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="lock" :size="16" />
                </span>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="input pl-9" placeholder="Minimal 8 karakter">
            </div>
        </div>

        <div>
            <label class="label" for="password_confirmation">Konfirmasi Password</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="lock" :size="16" />
                </span>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password" class="input pl-9" placeholder="Ulangi password">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Daftar &amp; Mulai</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Masuk</a>
    </p>
@endsection
