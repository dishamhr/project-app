@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Selamat datang kembali 👋</h1>
        <p class="mt-1 text-sm text-slate-500">Masuk untuk melihat tugas dan pengingatmu.</p>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
        @csrf

        <div>
            <label class="label" for="email">Email</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="mail" :size="16" />
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email" class="input pl-9" placeholder="nama@kampus.ac.id">
            </div>
        </div>

        <div>
            <label class="label" for="password">Password</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="lock" :size="16" />
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="input pl-9" placeholder="••••••••">
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1"
                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Ingat saya
        </label>

        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline">Daftar sekarang</a>
    </p>
@endsection
