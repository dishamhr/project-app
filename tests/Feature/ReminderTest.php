<?php

use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use App\Services\ReminderService;
use App\Support\IndonesianDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('pengingat 1 hari sebelum deadline dikirim tepat pada waktunya', function () {
    $this->travelTo(now()->startOfDay()->setTime(10, 0));

    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Makalah Cybercrime',
        'subject' => 'Keamanan Komputer',
        'deadline_date' => now()->addDay()->toDateString(),
        'deadline_time' => '10:00',
        'priority' => Task::PRIORITY_HIGH,
        'reminder_minutes' => 1440,
        'status' => Task::STATUS_NOT_STARTED,
    ]);

    $sent = app(ReminderService::class)->dispatchDueFor($user);

    expect($sent)->toBe(1);

    $reminder = $user->reminders()->firstOrFail();

    expect($reminder->title)->toContain('Pengingat Tugas')
        ->and($reminder->message)->toContain('Tugas Makalah Cybercrime akan deadline besok.')
        ->and($reminder->message)->toContain('Mata Kuliah: Keamanan Komputer')
        ->and($reminder->message)->toContain('Prioritas: Tinggi');

    expect($reminder->message)->toContain(
        IndonesianDate::formatDateTime(now()->addDay()->setTime(10, 0)),
    );

    expect($task = $user->tasks()->first())->reminded_at->not->toBeNull();
});

test('pengingat tidak dikirim sebelum waktunya', function () {
    $this->travelTo(now()->startOfDay()->setTime(9, 0));

    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'deadline_date' => now()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 30,
    ]);

    expect(app(ReminderService::class)->dispatchDueFor($user))->toBe(0);
    expect(Reminder::count())->toBe(0);
});

test('pengingat 30 menit sebelum deadline muncul saat mendekati jam tugas', function () {
    $this->travelTo(now()->startOfDay()->setTime(9, 30));

    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Presentasi Riset',
        'deadline_date' => now()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 30,
    ]);

    expect(app(ReminderService::class)->dispatchDueFor($user))->toBe(1);
    expect($user->reminders()->firstOrFail()->message)->toContain('akan deadline hari ini');
});

test('tugas yang sudah selesai tidak dikirimkan pengingat', function () {
    $this->travelTo(now()->startOfDay()->setTime(10, 0));

    $user = User::factory()->create();
    Task::factory()->done()->create([
        'user_id' => $user->id,
        'deadline_date' => now()->addDay()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 1440,
    ]);

    expect(app(ReminderService::class)->dispatchDueFor($user))->toBe(0);
});

test('deadline yang sudah lewat tidak memicu pengingat', function () {
    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'deadline_date' => now()->subDay()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 1440,
    ]);

    expect(app(ReminderService::class)->dispatchDueFor($user))->toBe(0);
    expect(Reminder::count())->toBe(0);
});

test('pengingat tidak dikirim ketika notifikasi dimatikan', function () {
    $this->travelTo(now()->startOfDay()->setTime(10, 0));

    $user = User::factory()->create();
    $user->settings()->update(['notifications_enabled' => false]);

    Task::factory()->create([
        'user_id' => $user->id,
        'deadline_date' => now()->addDay()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 1440,
    ]);

    expect(app(ReminderService::class)->dispatchDueFor($user))->toBe(0);
    expect(Reminder::count())->toBe(0);
});

test('pengingat tidak dikirim dua kali untuk tugas yang sama', function () {
    $this->travelTo(now()->startOfDay()->setTime(10, 0));

    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'deadline_date' => now()->addDay()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 1440,
    ]);

    $service = app(ReminderService::class);

    expect($service->dispatchDueFor($user))->toBe(1);
    expect($service->dispatchDueFor($user))->toBe(0);
    expect(Reminder::count())->toBe(1);
});

test('perintah reminders:send berjalan dan mengirim pengingat', function () {
    $this->travelTo(now()->startOfDay()->setTime(10, 0));

    $user = User::factory()->create();
    Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Tugas Sistem Operasi',
        'deadline_date' => now()->addDay()->toDateString(),
        'deadline_time' => '10:00',
        'reminder_minutes' => 1440,
    ]);

    expect(Artisan::call('reminders:send'))->toBe(0);
    expect(Reminder::count())->toBe(1);
});

test('endpoint pengingat mengembalikan daftar notifikasi belum dibaca', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id, 'title' => 'Makalah Cybercrime']);

    $task->reminders()->create([
        'user_id' => $user->id,
        'title' => '🔔 Pengingat Tugas',
        'message' => 'Tugas Makalah Cybercrime akan deadline besok.',
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);

    $this->actingAs($user)
        ->getJson('/reminders')
        ->assertOk()
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('count', 1)
        ->assertJsonPath('items.0.title', '🔔 Pengingat Tugas');
});

test('pengingat dapat ditandai sudah dibaca', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $reminder = $task->reminders()->create([
        'user_id' => $user->id,
        'title' => '🔔 Pengingat Tugas',
        'message' => 'Isi pengingat',
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);

    $this->actingAs($user)
        ->patchJson("/reminders/{$reminder->id}/read")
        ->assertOk();

    expect($reminder->fresh()->read_at)->not->toBeNull();

    $this->actingAs($user)->getJson('/reminders')->assertJsonPath('count', 0);
});

test('pengingat mahasiswa lain tidak bisa ditandai', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $owner->id]);

    $reminder = $task->reminders()->create([
        'user_id' => $owner->id,
        'title' => '🔔 Pengingat Tugas',
        'message' => 'Isi pengingat',
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);

    $this->actingAs($other)->patchJson("/reminders/{$reminder->id}/read")->assertNotFound();
});

test('mahasiswa dapat menyimpan pengaturan notifikasi', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings', [
        'notifications_enabled' => 0,
        'sound_enabled' => 0,
        'default_reminder_minutes' => 30,
    ])->assertRedirect(route('settings.show'));

    $settings = $user->settings()->fresh();

    expect($settings->notifications_enabled)->toBeFalse()
        ->and($settings->sound_enabled)->toBeFalse()
        ->and($settings->default_reminder_minutes)->toBe(30);
});

test('pengaturan notifikasi memvalidasi pilihan waktu pengingat', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings', [
        'notifications_enabled' => 1,
        'sound_enabled' => 1,
        'default_reminder_minutes' => 999,
    ])->assertSessionHasErrors('default_reminder_minutes');
});
