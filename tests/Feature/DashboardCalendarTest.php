<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard menampilkan ringkasan tugas mahasiswa', function () {
    $user = User::factory()->create();

    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Tugas Mendesak',
    ]);
    Task::factory()->done()->create([
        'user_id' => $user->id,
        'title' => 'Tugas Selesai',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Tugas Mendesak')
        ->assertSee('Tambah Tugas')
        ->assertSee('Belum selesai')
        ->assertSee('Sudah selesai');

    // Tugas milik mahasiswa lain tidak boleh tampil.
    $other = User::factory()->create();
    Task::factory()->create(['user_id' => $other->id, 'title' => 'Tugas Orang Lain']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Tugas Orang Lain');
});

test('halaman daftar tugas menampilkan kartu tugas', function () {
    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Makalah Cybercrime',
        'subject' => 'Keamanan Komputer',
    ]);

    $this->actingAs($user)
        ->get('/tasks')
        ->assertOk()
        ->assertSee('Makalah Cybercrime')
        ->assertSee('Keamanan Komputer')
        ->assertSee('Tandai Selesai');
});

test('detail tugas menampilkan informasi lengkap beserta pengingatnya', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Makalah Cybercrime',
        'subject' => 'Keamanan Komputer',
        'lecturer' => 'Dr. Andi Pratama',
        'deadline_date' => '2026-10-10',
        'deadline_time' => '10:00',
        'priority' => Task::PRIORITY_HIGH,
        'reminder_minutes' => 1440,
    ]);

    $this->actingAs($user)
        ->get("/tasks/{$task->id}")
        ->assertOk()
        ->assertSee('Makalah Cybercrime')
        ->assertSee('Keamanan Komputer')
        ->assertSee('Dr. Andi Pratama')
        ->assertSee('10 Oktober 2026')
        ->assertSee('10:00')
        ->assertSee('1 hari sebelum deadline')
        ->assertSee('Tinggi');
});

test('kalender menampilkan tugas berdasarkan tanggal deadline', function () {
    $user = User::factory()->create();

    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Makalah Cybercrime',
        'deadline_date' => '2026-10-10',
        'deadline_time' => '10:00',
    ]);

    $this->actingAs($user)
        ->get('/calendar?month=2026-10')
        ->assertOk()
        ->assertSee('Oktober 2026')
        ->assertSee('Makalah Cybercrime');

    $this->actingAs($user)
        ->get('/calendar?month=2026-11')
        ->assertOk()
        ->assertDontSee('Makalah Cybercrime');
});

test('kalender mengabaikan parameter bulan yang tidak valid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/calendar?month=bulan-ngaco')
        ->assertOk();
});

test('halaman pengaturan menampilkan preferensi notifikasi', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings')
        ->assertOk()
        ->assertSee('Aktifkan notifikasi')
        ->assertSee('Suara notifikasi')
        ->assertSee('Waktu pengingat default')
        ->assertSee('30 menit sebelum deadline');
});
