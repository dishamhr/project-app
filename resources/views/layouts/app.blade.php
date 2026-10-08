<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Task Reminder Mahasiswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <div class="flex min-h-screen">
        {{-- Navigasi samping --}}
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-blue-700 text-white transition-transform duration-200 lg:static lg:translate-x-0">
            <div class="flex items-center gap-3 px-5 py-5 border-b border-blue-600/60">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15">
                    <x-icon name="alarm" :size="22" />
                </span>
                <div class="leading-tight">
                    <p class="font-semibold">Task Reminder</p>
                    <p class="text-xs text-blue-100">Mahasiswa</p>
                </div>
                <button type="button" data-sidebar-close class="ml-auto rounded-lg p-1 text-blue-100 hover:bg-white/10 lg:hidden">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                        ['route' => 'tasks.index', 'label' => 'Daftar Tugas', 'icon' => 'clipboard'],
                        ['route' => 'tasks.create', 'label' => 'Tambah Tugas', 'icon' => 'plus'],
                        ['route' => 'calendar', 'label' => 'Kalender', 'icon' => 'calendar'],
                        ['route' => 'settings.show', 'label' => 'Pengaturan', 'icon' => 'cog'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ request()->routeIs($item['route']) ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                        <x-icon :name="$item['icon']" :size="18" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-blue-600/60 px-3 py-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-blue-50 transition hover:bg-white/10">
                        <x-icon name="logout" :size="18" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
        <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

        {{-- Konten utama --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <button type="button" data-sidebar-open class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                        <x-icon name="menu" :size="20" />
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-base font-semibold text-slate-900 sm:text-lg">@yield('title', 'Dashboard')</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">@yield('subtitle', 'Kelola tugas kuliah dan deadline kamu')</p>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <div class="relative">
                            <button type="button" data-bell-toggle
                                class="relative rounded-xl p-2 text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50">
                                <x-icon name="alarm" :size="20" />
                                <span data-bell-badge
                                    class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-red-500 px-1 text-center text-[11px] font-semibold leading-5 text-white">0</span>
                            </button>

                            <div data-bell-panel hidden
                                class="absolute right-0 z-30 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-900">🔔 Pengingat Tugas</p>
                                    <button type="button" data-mark-all-read class="text-xs font-medium text-blue-600 hover:underline">Tandai semua</button>
                                </div>
                                <div data-bell-list class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                    <p class="px-4 py-6 text-center text-sm text-slate-500">Tidak ada pengingat baru.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 rounded-xl bg-slate-100 py-1.5 pl-1.5 pr-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-semibold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Wadah notifikasi toast --}}
    <div data-toast-container class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-3"></div>

    <script>
        window.AppConfig = {
            remindersUrl: @json(route('reminders.unread')),
            markAllReadUrl: @json(route('reminders.read-all')),
            csrfToken: @json(csrf_token()),
        };
    </script>
    @stack('scripts')
</body>
</html>
