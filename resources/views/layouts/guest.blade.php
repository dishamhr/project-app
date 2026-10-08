<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · Task Reminder Mahasiswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('login') }}" class="mb-8 flex items-center gap-3 text-white">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/30">
                <x-icon name="alarm" :size="26" />
            </span>
            <span class="leading-tight">
                <span class="block text-xl font-semibold">Task Reminder Mahasiswa</span>
                <span class="block text-sm text-blue-100">Catat tugas, kejar deadline, tenang kuliah</span>
            </span>
        </a>

        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
            @include('partials.alerts')
            @yield('content')
        </div>

        <div class="mt-8 grid w-full max-w-3xl grid-cols-1 gap-3 text-sm text-white sm:grid-cols-3">
            <div class="flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/20">
                <x-icon name="clipboard" :size="18" /> Catat semua tugas
            </div>
            <div class="flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/20">
                <x-icon name="alarm" :size="18" /> Pengingat otomatis
            </div>
            <div class="flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/20">
                <x-icon name="calendar" :size="18" /> Kalender deadline
            </div>
        </div>
    </div>
</body>
</html>
