<?php

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createOnlineOrderForAuthorizationTest(array $attributes = []): OnlineOrder
{
    return OnlineOrder::create(array_merge([
        'order_no' => 'ORD-TEST-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(40),
        'customer_name' => 'Customer Test',
        'customer_phone' => '08123456789',
        'customer_address' => 'Alamat Test',
        'subtotal_amount' => 10000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 10000,
        'payment_method' => 'TRANSFER',
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
        'status' => OnlineOrder::STATUS_NEW,
    ], $attributes));
}

function createCodOnlineOrderForAuthorizationTest(array $orderAttributes = []): array
{
    $product = Product::create([
        'name' => 'Produk COD Test ' . str()->upper(str()->random(6)),
        'slug' => 'produk-cod-test-' . str()->lower(str()->random(8)),
        'sku' => 'COD-' . str()->upper(str()->random(8)),
        'purchase_price' => 5000,
        'selling_price' => 10000,
        'stock' => 10,
        'minimum_stock' => 1,
        'unit' => 'pcs',
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest(array_merge([
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
        'subtotal_amount' => 20000,
        'total_amount' => 20000,
    ], $orderAttributes));

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => 10000,
        'subtotal_amount' => 20000,
    ]);

    return [$order->refresh(), $product->refresh()];
}

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

it('prevents admin users from accessing owner only settings routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/pengaturan/user-role')
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');

    $this->actingAs($user)
        ->get('/pengaturan/profil-toko')
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');

    $this->actingAs($user)
        ->get('/pengaturan/metode-pembayaran')
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');

    $this->actingAs($user)
        ->get('/pengaturan/template-struk')
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

it('prevents deleting the currently authenticated user', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    User::factory()->create([
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

it('prevents deactivating the currently authenticated user', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    User::factory()->create([
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

it('prevents kasir users from confirming payments', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->patch("/pembayaran/{$order->id}/confirm")
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');
});

it('prevents kasir users from rejecting payments', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->patch("/pembayaran/{$order->id}/reject", [
            'admin_payment_note' => 'Bukti tidak sesuai.',
        ])
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');
});

it('prevents kasir users from cancelling online orders', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->patch("/order-online/{$order->id}/cancel")
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');
});

it('prevents kasir users from manually converting online orders to sales', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest([
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'status' => OnlineOrder::STATUS_COMPLETED,
    ]);

    $this->actingAs($user)
        ->patch("/order-online/{$order->id}/convert-sale")
        ->assertRedirect('/dashboard')
        ->assertSessionHas('error');
});

it('keeps new cod orders from being processed before customer confirmation', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest();

    expect($order->canProcess())->toBeFalse();

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('error');

    expect($order->fresh()->status)->toBe(OnlineOrder::STATUS_NEW)
        ->and($order->fresh()->stock_deducted_at)->toBeNull()
        ->and($product->fresh()->stock)->toBe(10)
        ->and(StockMovement::count())->toBe(0);
});

it('allows kasir users to confirm cod orders without deducting stock', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/confirm-cod")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_CONFIRMED)
        ->and($order->cod_confirmed_at)->not->toBeNull()
        ->and($order->cod_confirmed_by)->toBe($user->id)
        ->and($order->stock_deducted_at)->toBeNull()
        ->and($product->fresh()->stock)->toBe(10);
});

it('allows owner and admin users to confirm cod orders', function (string $role) {
    $user = User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);

    [$order] = createCodOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/confirm-cod")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('success');

    expect($order->fresh()->status)->toBe(OnlineOrder::STATUS_CONFIRMED);
})->with([
    User::ROLE_OWNER,
    User::ROLE_ADMIN,
]);

it('processes confirmed cod orders and deducts stock only once', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
    ]);

    expect($order->canProcess())->toBeTrue();

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_PROCESSING)
        ->and($order->stock_deducted_at)->not->toBeNull()
        ->and($product->fresh()->stock)->toBe(8)
        ->and(StockMovement::count())->toBe(1);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('error');

    expect($product->fresh()->stock)->toBe(8)
        ->and(StockMovement::count())->toBe(1);
});

it('requires payment received confirmation before completing cod orders', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_PROCESSING,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
        'stock_deducted_at' => now(),
    ]);

    $product->update(['stock' => 8]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/complete")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHasErrors('cod_payment_received');

    expect($order->fresh()->status)->toBe(OnlineOrder::STATUS_PROCESSING)
        ->and($order->fresh()->payment_status)->toBe(OnlineOrder::PAYMENT_UNPAID)
        ->and(Sale::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(8);
});

it('completes cod orders after payment is received and creates a sale without deducting stock twice', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_PROCESSING,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
        'stock_deducted_at' => now(),
    ]);

    $product->update(['stock' => 8]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/complete", [
            'cod_payment_received' => '1',
        ])
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_COMPLETED)
        ->and($order->payment_status)->toBe(OnlineOrder::PAYMENT_PAID)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->payment_confirmed_at)->not->toBeNull()
        ->and($order->sale_id)->not->toBeNull()
        ->and(Sale::count())->toBe(1)
        ->and($product->fresh()->stock)->toBe(8)
        ->and(StockMovement::count())->toBe(0);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/complete", [
            'cod_payment_received' => '1',
        ])
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('error');

    expect(Sale::count())->toBe(1)
        ->and($product->fresh()->stock)->toBe(8);
});
