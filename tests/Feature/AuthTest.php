<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman login dan registrasi dapat diakses tamu', function () {
    $this->get('/login')->assertOk()->assertSee('Selamat datang kembali');
    $this->get('/register')->assertOk()->assertSee('Buat akun mahasiswa');
});

test('tamu dialihkan ke halaman login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/tasks')->assertRedirect('/login');
});

test('mahasiswa dapat mendaftar dan otomatis masuk', function () {
    $response = $this->post('/register', [
        'name' => 'Ghadiza Maharani',
        'email' => 'ghadiza@kampus.ac.id',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'ghadiza@kampus.ac.id')->firstOrFail();

    expect($user->name)->toBe('Ghadiza Maharani');
    expect($user->setting()->exists())->toBeTrue();
});

test('registrasi memvalidasi data wajib', function () {
    $this->from('/register')
        ->post('/register', ['password_confirmation' => 'rahasia123'])
        ->assertSessionHasErrors(['name', 'email', 'password']);

    $this->assertGuest();
});

test('mahasiswa dapat masuk dengan password yang benar', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login gagal ketika password salah', function () {
    $user = User::factory()->create();

    $this->from('/login')
        ->post('/login', ['email' => $user->email, 'password' => 'salah-banget'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('mahasiswa dapat keluar dari akun', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
