<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests away from the dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect('/login');
});

it('allows active users to log in', function () {
    $user = User::factory()->create([
        'email' => 'owner@example.com',
        'password' => 'secret123',
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('blocks inactive users from logging in', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => 'secret123',
        'role' => User::ROLE_ADMIN,
        'is_active' => false,
    ]);

    $this->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('prevents kasir users from accessing settings routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/pengaturan/user-role')
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');
});
