<?php

use App\Models\OnlineOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

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
        ->assertSessionHasErrors([
            'email' => 'Akun Anda sedang nonaktif. Hubungi Owner.',
        ]);

    $this->assertGuest();
});

it('redirects the legacy public order url to the canonical checkout url', function () {
    $this->get('/order')
        ->assertRedirect('/checkout')
        ->assertStatus(301);
});

it('throttles login attempts by email and ip and clears the limiter after success', function () {
    $user = User::factory()->create([
        'email' => 'limited@example.com',
        'password' => 'secret123',
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    RateLimiter::clear('limited@example.com|127.0.0.1');
    RateLimiter::clear(md5('login' . 'limited@example.com|127.0.0.1'));

    for ($attempt = 0; $attempt < 4; $attempt++) {
        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect('/dashboard');

    auth()->logout();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    $this->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->assertStatus(429);
});

it('prevents kasir users from accessing settings routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/pengaturan/user-role')
        ->assertForbidden();
});

it('prevents kasir users from accessing the dashboard directly', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertForbidden();
});

it('redirects kasir users from the homepage to pos', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect('/pos');
});

it('prevents admin users from accessing owner only routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/laporan/laba-rugi')
        ->assertForbidden();
});

it('prevents admin users from accessing owner only settings routes', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/pengaturan/user-role')
        ->assertForbidden();

    $this->actingAs($user)
        ->get('/pengaturan/profil-toko')
        ->assertForbidden();

    $this->actingAs($user)
        ->get('/pengaturan/metode-pembayaran')
        ->assertForbidden();

    $this->actingAs($user)
        ->get('/pengaturan/template-struk')
        ->assertForbidden();
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
        ->assertForbidden();
});

it('prevents kasir users from accessing payment pages directly', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->get('/pembayaran')
        ->assertForbidden();

    $this->actingAs($user)
        ->get("/pembayaran/{$order->id}")
        ->assertForbidden();
});

it('allows owner and admin users to access payment pages', function (string $role) {
    $user = User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->get('/pembayaran')
        ->assertOk();

    $this->actingAs($user)
        ->get("/pembayaran/{$order->id}")
        ->assertOk();
})->with([
    User::ROLE_OWNER,
    User::ROLE_ADMIN,
]);

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
        ->assertForbidden();
});

it('confirms non cod payments without deducting stock until the order is processed', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $kasir = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
        'status' => OnlineOrder::STATUS_NEW,
    ]);

    $this->actingAs($admin)
        ->from("/pembayaran/{$order->id}")
        ->patch("/pembayaran/{$order->id}/confirm")
        ->assertRedirect("/pembayaran/{$order->id}")
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->payment_status)->toBe(OnlineOrder::PAYMENT_PAID)
        ->and($order->stock_deducted_at)->toBeNull()
        ->and($order->status)->toBe(OnlineOrder::STATUS_NEW)
        ->and($product->fresh()->stock)->toBe(10)
        ->and(StockMovement::count())->toBe(0);

    $this->actingAs($kasir)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('success');

    expect($order->fresh()->status)->toBe(OnlineOrder::STATUS_PROCESSING)
        ->and($order->fresh()->stock_deducted_at)->not->toBeNull()
        ->and($product->fresh()->stock)->toBe(8)
        ->and(StockMovement::count())->toBe(1);
});

it('prevents manual payment confirmation and rejection for cod orders', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    [$order] = createCodOnlineOrderForAuthorizationTest([
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
        'status' => OnlineOrder::STATUS_NEW,
    ]);

    expect($order->canConfirmPayment())->toBeFalse()
        ->and($order->canRejectPayment())->toBeFalse();

    $this->actingAs($admin)
        ->from("/pembayaran/{$order->id}")
        ->patch("/pembayaran/{$order->id}/confirm")
        ->assertRedirect("/pembayaran/{$order->id}")
        ->assertSessionHasErrors('payment_status');

    $this->actingAs($admin)
        ->from("/pembayaran/{$order->id}")
        ->patch("/pembayaran/{$order->id}/reject", [
            'admin_payment_note' => 'Tidak boleh tolak COD dari modul pembayaran.',
        ])
        ->assertRedirect("/pembayaran/{$order->id}")
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe(OnlineOrder::PAYMENT_WAITING_CONFIRMATION);
});

it('prevents rejecting payments after an order is already locked', function (array $lockedAttributes) {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $sale = null;

    if (($lockedAttributes['sale_id'] ?? false) === true) {
        $sale = Sale::create([
            'invoice_no' => 'INV-LOCK-' . str()->upper(str()->random(8)),
            'sale_date' => now(),
            'customer_name' => 'Customer Lock',
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 10000,
            'payment_method' => Sale::PAYMENT_TRANSFER,
            'paid_amount' => 10000,
            'change_amount' => 0,
            'status' => Sale::STATUS_COMPLETED,
        ]);

        $lockedAttributes['sale_id'] = $sale->id;
    }

    $order = createOnlineOrderForAuthorizationTest(array_merge([
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
        'status' => OnlineOrder::STATUS_NEW,
    ], $lockedAttributes));

    expect($order->canRejectPayment())->toBeFalse();

    $this->actingAs($admin)
        ->from("/pembayaran/{$order->id}")
        ->patch("/pembayaran/{$order->id}/reject", [
            'admin_payment_note' => 'Order sudah terkunci.',
        ])
        ->assertRedirect("/pembayaran/{$order->id}")
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe(OnlineOrder::PAYMENT_WAITING_CONFIRMATION);
})->with([
    'processing' => [[
        'status' => OnlineOrder::STATUS_PROCESSING,
        'processed_at' => now(),
    ]],
    'completed' => [[
        'status' => OnlineOrder::STATUS_COMPLETED,
        'completed_at' => now(),
    ]],
    'cancelled' => [[
        'status' => OnlineOrder::STATUS_CANCELLED,
        'cancelled_at' => now(),
    ]],
    'stock deducted' => [[
        'stock_deducted_at' => now(),
    ]],
    'sale exists' => [[
        'sale_id' => true,
    ]],
]);

it('prevents kasir users from cancelling online orders', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest();

    $this->actingAs($user)
        ->patch("/order-online/{$order->id}/cancel")
        ->assertForbidden();
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
        ->assertForbidden();
});

it('restricts kasir receipt access to their own or just completed pos sales', function () {
    $kasir = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $otherKasir = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $ownSale = Sale::create([
        'created_by' => $kasir->id,
        'invoice_no' => 'POS-OWN-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Sendiri',
        'subtotal_amount' => 10000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 10000,
        'payment_method' => Sale::PAYMENT_CASH,
        'paid_amount' => 10000,
        'change_amount' => 0,
        'status' => Sale::STATUS_COMPLETED,
    ]);

    $otherSale = Sale::create([
        'created_by' => $otherKasir->id,
        'invoice_no' => 'POS-OTHER-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Lain',
        'subtotal_amount' => 10000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 10000,
        'payment_method' => Sale::PAYMENT_CASH,
        'paid_amount' => 10000,
        'change_amount' => 0,
        'status' => Sale::STATUS_COMPLETED,
    ]);

    $recentSale = Sale::create([
        'created_by' => null,
        'invoice_no' => 'POS-RECENT-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Baru',
        'subtotal_amount' => 10000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 10000,
        'payment_method' => Sale::PAYMENT_CASH,
        'paid_amount' => 10000,
        'change_amount' => 0,
        'status' => Sale::STATUS_COMPLETED,
    ]);

    $this->actingAs($kasir)
        ->get("/pos/struk/{$ownSale->id}")
        ->assertOk();

    $this->actingAs($kasir)
        ->get("/pos/struk/{$otherSale->id}")
        ->assertForbidden();

    $this->actingAs($kasir)
        ->withSession(['pos.recent_receipt_sale_id' => $recentSale->id])
        ->get("/pos/struk/{$recentSale->id}")
        ->assertOk();
});

it('allows owner and admin users to access any receipt', function (string $role) {
    $user = User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);

    $sale = Sale::create([
        'created_by' => null,
        'invoice_no' => 'POS-ANY-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Bebas',
        'subtotal_amount' => 10000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 10000,
        'payment_method' => Sale::PAYMENT_CASH,
        'paid_amount' => 10000,
        'change_amount' => 0,
        'status' => Sale::STATUS_COMPLETED,
    ]);

    $this->actingAs($user)
        ->get("/pos/struk/{$sale->id}")
        ->assertOk();
})->with([
    User::ROLE_OWNER,
    User::ROLE_ADMIN,
]);

it('rejects inactive customers during pos checkout validation', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $product = Product::create([
        'name' => 'Produk POS Customer Nonaktif',
        'slug' => 'produk-pos-customer-nonaktif-' . str()->lower(str()->random(8)),
        'sku' => 'POS-CUST-' . str()->upper(str()->random(8)),
        'purchase_price' => 5000,
        'selling_price' => 10000,
        'stock' => 5,
        'minimum_stock' => 1,
        'unit' => 'pcs',
        'is_active' => true,
    ]);

    $customer = Customer::create([
        'customer_code' => 'CUST-' . str()->upper(str()->random(8)),
        'name' => 'Pelanggan Nonaktif',
        'is_active' => false,
    ]);

    $cart = [
        (string) $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'selling_price' => $product->selling_price,
            'quantity' => 1,
            'stock' => $product->stock,
        ],
    ];

    $this->actingAs($user)
        ->withSession(['pos.cart' => $cart])
        ->from('/pos')
        ->post('/pos/checkout', [
            'customer_id' => $customer->id,
            'payment_method' => Sale::PAYMENT_CASH,
            'paid_amount' => 10000,
        ])
        ->assertRedirect('/pos')
        ->assertSessionHasErrors('customer_id');

    expect(Sale::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(5);
});

it('calculates pos monetary amounts as integer rupiah values', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    StoreSetting::current()->update([
        'tax_percentage' => '12.50',
    ]);

    $product = Product::create([
        'name' => 'Produk POS Pajak Pecahan',
        'slug' => 'produk-pos-pajak-pecahan-' . str()->lower(str()->random(8)),
        'sku' => 'POS-TAX-' . str()->upper(str()->random(8)),
        'purchase_price' => 500,
        'selling_price' => 999,
        'stock' => 5,
        'minimum_stock' => 1,
        'unit' => 'pcs',
        'is_active' => true,
    ]);

    $cart = [
        (string) $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'selling_price' => $product->selling_price,
            'quantity' => 1,
            'stock' => $product->stock,
        ],
    ];

    $this->actingAs($user)
        ->withSession(['pos.cart' => $cart])
        ->from('/pos')
        ->post('/pos/checkout', [
            'payment_method' => Sale::PAYMENT_CASH,
            'paid_amount' => 1200,
        ])
        ->assertRedirect();

    $sale = Sale::query()->with('items')->firstOrFail();

    expect($sale->subtotal_amount)->toBe('999.00')
        ->and($sale->discount_amount)->toBe('0.00')
        ->and($sale->tax_amount)->toBe('125.00')
        ->and($sale->total_amount)->toBe('1124.00')
        ->and($sale->paid_amount)->toBe('1200.00')
        ->and($sale->change_amount)->toBe('76.00')
        ->and($sale->items->first()->subtotal_amount)->toBe('999.00')
        ->and($product->fresh()->stock)->toBe(4);
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

it('rolls back the whole process action when stock deduction fails', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
    ]);

    $product->update(['stock' => 1]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHasErrors('stock');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_CONFIRMED)
        ->and($order->processed_at)->toBeNull()
        ->and($order->stock_deducted_at)->toBeNull()
        ->and($product->fresh()->stock)->toBe(1)
        ->and(StockMovement::count())->toBe(0);
});

it('rejects process action when an order item has invalid quantity', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
    ]);

    $order->items()->first()->update([
        'quantity' => 0,
    ]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHasErrors('items');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_CONFIRMED)
        ->and($order->processed_at)->toBeNull()
        ->and($order->stock_deducted_at)->toBeNull()
        ->and($product->fresh()->stock)->toBe(10)
        ->and(StockMovement::count())->toBe(0);
});

it('deducts duplicate product items using the latest locked stock value', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
    ]);

    $product->update(['stock' => 5]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 3,
        'unit_price' => 10000,
        'subtotal_amount' => 30000,
    ]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHas('success');

    expect($order->fresh()->stock_deducted_at)->not->toBeNull()
        ->and($product->fresh()->stock)->toBe(0)
        ->and(StockMovement::orderBy('id')->pluck('stock_before')->all())->toBe([5, 3])
        ->and(StockMovement::orderBy('id')->pluck('stock_after')->all())->toBe([3, 0]);
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

it('rolls back the whole completion when stock deduction fails', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_PROCESSING,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
        'stock_deducted_at' => null,
    ]);

    $product->update(['stock' => 1]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/complete", [
            'cod_payment_received' => '1',
        ])
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHasErrors('stock');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_PROCESSING)
        ->and($order->payment_status)->toBe(OnlineOrder::PAYMENT_UNPAID)
        ->and($order->paid_at)->toBeNull()
        ->and($order->payment_confirmed_at)->toBeNull()
        ->and($order->completed_at)->toBeNull()
        ->and($order->stock_deducted_at)->toBeNull()
        ->and($order->sale_id)->toBeNull()
        ->and(Sale::count())->toBe(0)
        ->and(StockMovement::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(1);
});

it('rejects complete action when an order item has invalid quantity before stock is deducted', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    [$order, $product] = createCodOnlineOrderForAuthorizationTest([
        'status' => OnlineOrder::STATUS_PROCESSING,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
        'stock_deducted_at' => null,
    ]);

    $order->items()->first()->update([
        'quantity' => 0,
    ]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/complete", [
            'cod_payment_received' => '1',
        ])
        ->assertRedirect("/order-online/{$order->id}")
        ->assertSessionHasErrors('items');

    $order->refresh();

    expect($order->status)->toBe(OnlineOrder::STATUS_PROCESSING)
        ->and($order->payment_status)->toBe(OnlineOrder::PAYMENT_UNPAID)
        ->and($order->completed_at)->toBeNull()
        ->and($order->stock_deducted_at)->toBeNull()
        ->and($order->sale_id)->toBeNull()
        ->and(Sale::count())->toBe(0)
        ->and(StockMovement::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(10);
});

it('allows public payment updates only while the online order is still new and unpaid', function () {
    $order = createOnlineOrderForAuthorizationTest([
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_REJECTED,
        'status' => OnlineOrder::STATUS_NEW,
        'admin_payment_note' => 'Bukti sebelumnya ditolak.',
        'payment_confirmed_at' => now(),
        'payment_rejected_at' => now(),
    ]);

    $this->from("/tracking/{$order->tracking_token}")
        ->post("/tracking/{$order->tracking_token}/payment-proof", [
            'payment_method' => Sale::PAYMENT_CASH,
            'payment_note' => 'Saya pilih COD.',
        ])
        ->assertRedirect("/tracking/{$order->tracking_token}")
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->payment_method)->toBe(Sale::PAYMENT_CASH)
        ->and($order->payment_status)->toBe(OnlineOrder::PAYMENT_UNPAID)
        ->and($order->payment_note)->toBe('Saya pilih COD.')
        ->and($order->admin_payment_note)->toBeNull()
        ->and($order->payment_confirmed_at)->toBeNull()
        ->and($order->payment_rejected_at)->toBeNull();
});

it('prevents public payment updates after the online order is locked', function (array $lockedAttributes) {
    $sale = null;

    if (($lockedAttributes['sale_id'] ?? false) === true) {
        $sale = Sale::create([
            'invoice_no' => 'INV-PUBLIC-' . str()->upper(str()->random(8)),
            'sale_date' => now(),
            'customer_name' => 'Customer Test',
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 10000,
            'payment_method' => Sale::PAYMENT_TRANSFER,
            'paid_amount' => 10000,
            'change_amount' => 0,
            'status' => Sale::STATUS_COMPLETED,
        ]);

        $lockedAttributes['sale_id'] = $sale->id;
    }

    $order = createOnlineOrderForAuthorizationTest(array_merge([
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
        'status' => OnlineOrder::STATUS_NEW,
        'payment_note' => 'Catatan lama',
        'admin_payment_note' => 'Catatan admin lama',
    ], $lockedAttributes));
    $originalPaymentMethod = $order->payment_method;
    $originalPaymentStatus = $order->payment_status;

    $this->from("/tracking/{$order->tracking_token}")
        ->post("/tracking/{$order->tracking_token}/payment-proof", [
            'payment_method' => Sale::PAYMENT_CASH,
            'payment_note' => 'Ubah ke COD dari tracking.',
        ])
        ->assertRedirect("/tracking/{$order->tracking_token}")
        ->assertSessionHasErrors('payment_method');

    $order->refresh();

    expect($order->payment_method)->toBe($originalPaymentMethod)
        ->and($order->payment_status)->toBe($originalPaymentStatus)
        ->and($order->payment_note)->toBe('Catatan lama')
        ->and($order->admin_payment_note)->toBe('Catatan admin lama');
})->with([
    'paid order' => [[
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'payment_confirmed_at' => now(),
    ]],
    'confirmed cod order' => [[
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'cod_confirmed_at' => now(),
    ]],
    'processing order' => [[
        'status' => OnlineOrder::STATUS_PROCESSING,
        'processed_at' => now(),
    ]],
    'completed order' => [[
        'status' => OnlineOrder::STATUS_COMPLETED,
        'completed_at' => now(),
    ]],
    'cancelled order' => [[
        'status' => OnlineOrder::STATUS_CANCELLED,
        'cancelled_at' => now(),
    ]],
    'stock already deducted' => [[
        'stock_deducted_at' => now(),
    ]],
    'sale already created' => [[
        'sale_id' => true,
        'converted_to_sale_at' => now(),
    ]],
]);

it('stores uploaded public payment proofs on the private disk', function () {
    Storage::fake('payment_proofs');
    Storage::fake('public');

    StoreSetting::current()->update([
        'payment_transfer_enabled' => true,
    ]);

    $order = createOnlineOrderForAuthorizationTest([
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_REJECTED,
        'status' => OnlineOrder::STATUS_NEW,
    ]);

    $this->from("/tracking/{$order->tracking_token}")
        ->post("/tracking/{$order->tracking_token}/payment-proof", [
            'payment_method' => Sale::PAYMENT_TRANSFER,
            'payment_note' => 'Bukti transfer baru.',
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ])
        ->assertRedirect("/tracking/{$order->tracking_token}")
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->payment_status)->toBe(OnlineOrder::PAYMENT_WAITING_CONFIRMATION)
        ->and($order->payment_proof_path)->not->toBeNull();

    Storage::disk('payment_proofs')->assertExists($order->payment_proof_path);
    Storage::disk('public')->assertMissing($order->payment_proof_path);
});

it('throttles public payment proof uploads by tracking token and ip', function () {
    $order = createOnlineOrderForAuthorizationTest([
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
    ]);

    $limiterKey = $order->tracking_token . '|127.0.0.1';
    RateLimiter::clear('minute|' . $limiterKey);
    RateLimiter::clear('hour|' . $limiterKey);

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $this->from("/tracking/{$order->tracking_token}")
            ->post("/tracking/{$order->tracking_token}/payment-proof", [
                'payment_method' => Sale::PAYMENT_CASH,
                'payment_note' => "Percobaan {$attempt}",
            ])
            ->assertRedirect("/tracking/{$order->tracking_token}")
            ->assertSessionHas('success');
    }

    $this->from("/tracking/{$order->tracking_token}")
        ->post("/tracking/{$order->tracking_token}/payment-proof", [
            'payment_method' => Sale::PAYMENT_CASH,
            'payment_note' => 'Terlalu banyak upload.',
        ])
        ->assertStatus(429);

    RateLimiter::clear('minute|' . $limiterKey);
    RateLimiter::clear('hour|' . $limiterKey);
});

it('streams payment proofs privately for owner admin and valid tracking tokens only', function () {
    Storage::fake('local');

    $order = createOnlineOrderForAuthorizationTest([
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
        'payment_proof_path' => 'payment-proofs/proof.jpg',
    ]);

    Storage::disk('local')->put($order->payment_proof_path, 'fake proof content');

    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);

    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $kasir = User::factory()->create([
        'role' => User::ROLE_KASIR,
        'is_active' => true,
    ]);

    $ownerResponse = $this->actingAs($owner)
        ->get("/order-online/{$order->id}/payment-proof")
        ->assertOk();

    expect($ownerResponse->headers->get('cache-control'))
        ->toContain('private')
        ->toContain('no-store');

    $adminResponse = $this->actingAs($admin)
        ->get("/order-online/{$order->id}/payment-proof")
        ->assertOk();

    expect($adminResponse->headers->get('cache-control'))
        ->toContain('private')
        ->toContain('no-store');

    $this->actingAs($kasir)
        ->get("/order-online/{$order->id}/payment-proof")
        ->assertForbidden();

    auth()->logout();

    $publicResponse = $this->get("/tracking/{$order->tracking_token}/payment-proof-file")
        ->assertOk();

    expect($publicResponse->headers->get('cache-control'))
        ->toContain('private')
        ->toContain('no-store');

    $this->get('/tracking/token-tidak-valid/payment-proof-file')
        ->assertNotFound();
});

it('generates invoice numbers from locked daily sequences', function () {
    $service = app(\App\Services\InvoiceNumberService::class);
    $date = now()->format('Ymd');

    expect($service->next('POS'))->toBe("POS-{$date}-0001")
        ->and($service->next('POS'))->toBe("POS-{$date}-0002")
        ->and($service->next('ONL'))->toBe("ONL-{$date}-0001");

    expect(DB::table('invoice_sequences')->where('sequence_key', "POS-{$date}")->value('last_number'))->toBe(2)
        ->and(DB::table('invoice_sequences')->where('sequence_key', "ONL-{$date}")->value('last_number'))->toBe(1);
});
