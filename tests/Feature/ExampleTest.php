<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tamu yang membuka halaman utama dialihkan ke login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('mahasiswa yang login dialihkan ke dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
