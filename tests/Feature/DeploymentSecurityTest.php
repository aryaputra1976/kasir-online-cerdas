<?php

use App\Models\OnlineOrder;
use App\Models\Sale;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function deploymentOrder(array $attributes = []): OnlineOrder
{
    return OnlineOrder::create(array_merge([
        'order_no' => 'ONL-SEC-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(64),
        'customer_name' => 'Customer Security',
        'customer_phone' => '08123456789',
        'customer_email' => 'security@example.test',
        'customer_address' => 'Alamat Security',
        'subtotal_amount' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => Sale::PAYMENT_QRIS,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
    ], $attributes));
}

it('adds baseline security headers and only sends hsts on production https', function () {
    $this->get('/login')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Content-Security-Policy-Report-Only')
        ->assertHeaderMissing('Strict-Transport-Security');

    $this->app->detectEnvironment(fn () => 'production');

    $this->withServerVariables(['HTTPS' => 'on'])
        ->withHeader('X-Forwarded-Proto', 'https')
        ->get('/login')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
});

it('throttles important public endpoints', function () {
    $order = deploymentOrder();

    RateLimiter::clear('minute|127.0.0.1');
    RateLimiter::clear('hour|127.0.0.1');
    for ($i = 0; $i < 5; $i++) {
        $this->post('/checkout')->assertStatus(302);
    }
    $this->post('/checkout')->assertTooManyRequests();

    $trackingKey = $order->tracking_token . '|127.0.0.1';
    RateLimiter::clear($trackingKey);
    for ($i = 0; $i < 30; $i++) {
        $this->get(route('public.tracking', $order->tracking_token))->assertOk();
    }
    $this->get(route('public.tracking', $order->tracking_token))->assertTooManyRequests();

    RateLimiter::clear('minute|' . $trackingKey);
    RateLimiter::clear('hour|' . $trackingKey);
    for ($i = 0; $i < 3; $i++) {
        $this->post(route('public.payment-proof.upload', $order->tracking_token))->assertStatus(302);
    }
    $this->post(route('public.payment-proof.upload', $order->tracking_token))->assertTooManyRequests();
});

it('rejects unsafe payment proof uploads and stores valid proof privately', function () {
    Storage::fake('payment_proofs');
    Storage::fake('public');

    StoreSetting::current()->update([
        'payment_qris_enabled' => true,
    ]);

    $order = deploymentOrder();
    $limiterKey = $order->tracking_token . '|127.0.0.1';

    $this->from(route('public.tracking', $order->tracking_token))
        ->post(route('public.payment-proof.upload', $order->tracking_token), [
            'payment_method' => Sale::PAYMENT_QRIS,
            'payment_proof' => UploadedFile::fake()->create('proof.svg', 10, 'image/svg+xml'),
        ])
        ->assertSessionHasErrors('payment_proof');

    $this->from(route('public.tracking', $order->tracking_token))
        ->post(route('public.payment-proof.upload', $order->tracking_token), [
            'payment_method' => Sale::PAYMENT_QRIS,
            'payment_proof' => UploadedFile::fake()->create('proof.jpg', 10, 'text/plain'),
        ])
        ->assertSessionHasErrors('payment_proof');

    $this->from(route('public.tracking', $order->tracking_token))
        ->post(route('public.payment-proof.upload', $order->tracking_token), [
            'payment_method' => Sale::PAYMENT_QRIS,
            'payment_proof' => UploadedFile::fake()->create('large.jpg', 3000, 'image/jpeg'),
        ])
        ->assertSessionHasErrors('payment_proof');

    $wideImagePath = tempnam(sys_get_temp_dir(), 'wide-proof') . '.png';
    $wideImage = imagecreatetruecolor(6001, 1);
    imagepng($wideImage, $wideImagePath);
    imagedestroy($wideImage);

    $dimensionOrder = deploymentOrder();

    $this->from(route('public.tracking', $dimensionOrder->tracking_token))
        ->post(route('public.payment-proof.upload', $dimensionOrder->tracking_token), [
            'payment_method' => Sale::PAYMENT_QRIS,
            'payment_proof' => new UploadedFile($wideImagePath, 'wide.png', 'image/png', null, true),
        ])
        ->assertSessionHasErrors('payment_proof');

    $validOrder = deploymentOrder();

    $this->from(route('public.tracking', $validOrder->tracking_token))
        ->post(route('public.payment-proof.upload', $validOrder->tracking_token), [
            'payment_method' => Sale::PAYMENT_QRIS,
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ])
        ->assertRedirect(route('public.tracking', $validOrder->tracking_token));

    $validOrder->refresh();
    expect($validOrder->payment_proof_path)->not->toBeNull();
    Storage::disk('payment_proofs')->assertExists($validOrder->payment_proof_path);
    Storage::disk('public')->assertMissing($validOrder->payment_proof_path);
});

it('protects payment proof streaming by auth role or matching tracking token', function () {
    Storage::fake('payment_proofs');
    $order = deploymentOrder(['payment_proof_path' => 'proof-a.jpg']);
    $otherOrder = deploymentOrder(['payment_proof_path' => 'proof-b.jpg']);
    Storage::disk('payment_proofs')->put('proof-a.jpg', 'proof a');
    Storage::disk('payment_proofs')->put('proof-b.jpg', 'proof b');

    $this->get(route('online-orders.payment-proof', $order))->assertRedirect('/login');

    $this->get(route('public.payment-proof.show', $otherOrder->tracking_token))
        ->assertOk()
        ->assertHeader('Cache-Control')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-site');

    $this->get('/storage/' . $order->payment_proof_path)->assertNotFound();
});

it('uses strong non sequential tracking tokens', function () {
    $first = deploymentOrder();
    $second = deploymentOrder();

    expect($first->tracking_token)->not->toBe($second->tracking_token)
        ->and(strlen($first->tracking_token))->toBeGreaterThanOrEqual(40)
        ->and($first->tracking_token)->not->toBe((string) $first->id)
        ->and($first->tracking_token)->not->toBe($first->order_no);

    $this->get(route('public.tracking', 'not-a-real-token'))->assertNotFound();
});

it('has safe production session config and deployment check detects debug mode', function () {
    Config::set('session.driver', 'database');
    Config::set('session.encrypt', true);
    Config::set('session.secure', true);
    Config::set('session.http_only', true);
    Config::set('session.same_site', 'lax');

    expect(config('session.driver'))->toBe('database')
        ->and(config('session.encrypt'))->toBeTrue()
        ->and(config('session.secure'))->toBeTrue()
        ->and(config('session.http_only'))->toBeTrue()
        ->and(config('session.same_site'))->toBe('lax');

    Config::set('app.debug', true);
    $exitCode = Artisan::call('app:deployment-check');

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('APP_DEBUG false');
});
