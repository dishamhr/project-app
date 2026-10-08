<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mahasiswa dapat menambahkan tugas', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/tasks', [
        'title' => 'Makalah Cybercrime',
        'subject' => 'Keamanan Komputer',
        'description' => 'Membuat makalah tentang cybercrime',
        'lecturer' => 'Dr. Andi Pratama',
        'deadline_date' => '2026-10-10',
        'deadline_time' => '10:00',
        'priority' => 'high',
        'reminder_minutes' => 1440,
    ]);

    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'user_id' => $user->id,
        'title' => 'Makalah Cybercrime',
        'subject' => 'Keamanan Komputer',
        'lecturer' => 'Dr. Andi Pratama',
        'deadline_date' => '2026-10-10',
        'deadline_time' => '10:00',
        'priority' => 'high',
        'reminder_minutes' => 1440,
        'status' => Task::STATUS_NOT_STARTED,
    ]);
});

test('form tugas memvalidasi input wajib', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/tasks', [])->assertSessionHasErrors([
        'title',
        'subject',
        'deadline_date',
        'deadline_time',
        'priority',
        'reminder_minutes',
    ]);

    $this->actingAs($user)->post('/tasks', [
        'title' => 'Judul',
        'subject' => 'Matkul',
        'deadline_date' => '2026-10-10',
        'deadline_time' => '10:00',
        'priority' => 'sedang',
        'reminder_minutes' => 1440,
    ])->assertSessionHasErrors(['priority']);
});

test('daftar tugas dapat difilter berdasarkan status dan prioritas', function () {
    $user = User::factory()->create();

    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Tugas Alpha',
        'priority' => Task::PRIORITY_LOW,
        'status' => Task::STATUS_NOT_STARTED,
    ]);
    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Tugas Bravo',
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_IN_PROGRESS,
    ]);
    Task::factory()->done()->create([
        'user_id' => $user->id,
        'title' => 'Tugas Charlie',
    ]);

    $this->actingAs($user)->get('/tasks?filter=all')->assertOk()
        ->assertSee('Tugas Alpha')->assertSee('Tugas Bravo')->assertSee('Tugas Charlie');

    $this->actingAs($user)->get('/tasks?filter=not_started')->assertOk()
        ->assertSee('Tugas Alpha')->assertDontSee('Tugas Bravo')->assertDontSee('Tugas Charlie');

    $this->actingAs($user)->get('/tasks?filter=in_progress')->assertOk()
        ->assertSee('Tugas Bravo')->assertDontSee('Tugas Alpha');

    $this->actingAs($user)->get('/tasks?filter=done')->assertOk()
        ->assertSee('Tugas Charlie')->assertDontSee('Tugas Alpha');

    $this->actingAs($user)->get('/tasks?filter=high')->assertOk()
        ->assertSee('Tugas Bravo')->assertDontSee('Tugas Alpha');
});

test('tugas milik mahasiswa lain tidak dapat diakses', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->get("/tasks/{$task->id}")->assertNotFound();
    $this->actingAs($other)->get("/tasks/{$task->id}/edit")->assertNotFound();
    $this->actingAs($other)->put("/tasks/{$task->id}", [
        'title' => 'Dicuri',
        'subject' => 'Matkul',
        'deadline_date' => '2026-10-10',
        'deadline_time' => '10:00',
        'priority' => 'low',
        'reminder_minutes' => 60,
    ])->assertNotFound();
    $this->actingAs($other)->delete("/tasks/{$task->id}")->assertNotFound();
});

test('mahasiswa dapat menandai tugas selesai', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'status' => Task::STATUS_IN_PROGRESS,
    ]);

    $this->actingAs($user)
        ->patch("/tasks/{$task->id}/complete")
        ->assertRedirect();

    expect($task->fresh()->status)->toBe(Task::STATUS_DONE);
});

test('mahasiswa dapat mengedit dan menghapus tugas', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id, 'title' => 'Judul Lama']);

    $this->actingAs($user)->put("/tasks/{$task->id}", [
        'title' => 'Judul Baru',
        'subject' => 'Statistika',
        'description' => 'Diperbarui',
        'lecturer' => 'Prof. Budi',
        'deadline_date' => '2026-11-01',
        'deadline_time' => '09:30',
        'priority' => Task::PRIORITY_HIGH,
        'reminder_minutes' => 180,
        'status' => Task::STATUS_IN_PROGRESS,
    ])->assertRedirect(route('tasks.show', $task));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Judul Baru',
        'subject' => 'Statistika',
        'deadline_time' => '09:30',
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_IN_PROGRESS,
    ]);

    $this->actingAs($user)->delete("/tasks/{$task->id}")->assertRedirect(route('tasks.index'));

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('mengubah jadwal mengaktifkan kembali pengingat tugas', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'reminded_at' => now(),
    ]);

    $this->actingAs($user)->put("/tasks/{$task->id}", [
        'title' => $task->title,
        'subject' => $task->subject,
        'deadline_date' => '2026-12-01',
        'deadline_time' => '08:00',
        'priority' => Task::PRIORITY_MEDIUM,
        'reminder_minutes' => 60,
        'status' => Task::STATUS_NOT_STARTED,
    ])->assertRedirect(route('tasks.show', $task));

    expect($task->fresh()->reminded_at)->toBeNull();
});

test('halaman tambah tugas menampilkan pilihan pengingat', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/tasks/create')
        ->assertOk()
        ->assertSee('30 menit sebelum deadline')
        ->assertSee('1 hari sebelum deadline')
        ->assertSee('2 hari sebelum deadline');
});
