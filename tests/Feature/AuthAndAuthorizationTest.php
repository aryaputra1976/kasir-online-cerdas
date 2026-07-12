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

it('redirects kasir users to pos after login', function () {
    $user = User::factory()->create([
        'email' => 'kasir@example.com',
        'password' => 'secret123',
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect('/pos');

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

it('redirects kasir users away from the dashboard to pos', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/pos')
        ->assertSessionHas('info');
});

it('prevents admin users from accessing owner only routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/laporan/laba-rugi')
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');
});

it('allows owner users to access owner only routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/laporan/laba-rugi')
        ->assertOk();
});

it('prevents changing the last active owner to admin', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->from('/pengaturan/user-role')
        ->put("/pengaturan/user-role/{$owner->id}", [
            'name' => $owner->name,
            'email' => $owner->email,
            'phone' => $owner->phone,
            'role' => User::ROLE_ADMIN,
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect('/pengaturan/user-role')
        ->assertSessionHas('error');

    expect($owner->fresh()->role)->toBe(User::ROLE_OWNER);
});

it('prevents deactivating the last active owner', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->from('/pengaturan/user-role')
        ->put("/pengaturan/user-role/{$owner->id}", [
            'name' => $owner->name,
            'email' => $owner->email,
            'phone' => $owner->phone,
            'role' => User::ROLE_OWNER,
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect('/pengaturan/user-role')
        ->assertSessionHas('error');

    expect($owner->fresh()->is_active)->toBeTrue();
});

it('prevents deleting the last active owner', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->from('/pengaturan/user-role')
        ->delete("/pengaturan/user-role/{$owner->id}")
        ->assertRedirect('/pengaturan/user-role')
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', [
        'id' => $owner->id,
    ]);
});
