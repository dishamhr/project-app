@extends('layouts.app')

@section('title', 'Pengaturan')
@section('subtitle', 'Atur notifikasi dan waktu pengingat default')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-icon name="alarm" :size="22" />
                    </span>
                    <div>
                        <h2 class="font-semibold text-slate-900">Notifikasi Pengingat</h2>
                        <p class="text-sm text-slate-500">Sistem mengirim pengingat otomatis sebelum deadline.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <input type="hidden" name="notifications_enabled" value="0">
                    <label class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Aktifkan notifikasi</span>
                            <span class="block text-xs text-slate-500">Tampilkan pengingat tugas sebelum deadline</span>
                        </span>
                        <span class="relative inline-flex shrink-0">
                            <input type="checkbox" name="notifications_enabled" value="1" class="peer sr-only"
                                @checked(old('notifications_enabled', $settings->notifications_enabled))>
                            <span class="block h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-600"></span>
                            <span class="absolute left-0.5 top-0.5 block h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                        </span>
                    </label>

                    <div class="rounded-2xl bg-slate-50 px-4 py-3.5">
                        <label class="label" for="default_reminder_minutes">Waktu pengingat default</label>
                        <select id="default_reminder_minutes" name="default_reminder_minutes" class="input bg-white">
                            @foreach ($reminders as $minutes => $label)
                                <option value="{{ $minutes }}"
                                    @selected((int) old('default_reminder_minutes', $settings->default_reminder_minutes) === (int) $minutes)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Dipakai otomatis saat kamu menambah tugas baru.</p>
                    </div>

                    <input type="hidden" name="sound_enabled" value="0">
                    <label class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Suara notifikasi</span>
                            <span class="block text-xs text-slate-500">Bunyi singkat saat pengingat muncul</span>
                        </span>
                        <span class="relative inline-flex shrink-0">
                            <input type="checkbox" name="sound_enabled" value="1" class="peer sr-only"
                                @checked(old('sound_enabled', $settings->sound_enabled))>
                            <span class="block h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-600"></span>
                            <span class="absolute left-0.5 top-0.5 block h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                </div>

                <div class="mt-6 flex gap-3 border-t border-slate-100 pt-5">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" :size="16" /> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>

        <div class="card">
            <h3 class="flex items-center gap-2 font-semibold text-slate-900">
                <x-icon name="cog" :size="18" class="text-brand-600" />
                Cara kerja pengingat
            </h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li class="flex gap-2"><span class="text-brand-600">1.</span> Setiap tugas punya waktu pengingat (mis. 1 hari sebelum deadline).</li>
                <li class="flex gap-2"><span class="text-brand-600">2.</span> Sistem mengecek jadwal setiap menit dan menampilkan notifikasi saat waktunya tiba.</li>
                <li class="flex gap-2"><span class="text-brand-600">3.</span> Notifikasi muncul sebagai popup berbunyi di pojok layar dan tersimpan di lonceng.</li>
                <li class="flex gap-2"><span class="text-brand-600">4.</span> Pengingat berhenti otomatis setelah kamu menandai tugas selesai.</li>
            </ul>
        </div>
    </div>
@endsection
