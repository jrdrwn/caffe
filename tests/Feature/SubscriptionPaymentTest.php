<?php

use App\Enums\SubscriptionPlan;
use App\Filament\Widgets\SubscriptionUpgradeWidget;
use App\Models\Cafe;
use App\Models\Category;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

// ---------------------------------------------------------------------------
// SubscriptionPayment model
// ---------------------------------------------------------------------------

test('subscription payment can be created', function () {
    $cafe = Cafe::factory()->create();
    $subscription = Subscription::factory()->premium()->create();

    $payment = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-TEST-123',
        'amount' => 200000,
        'status' => 'pending',
    ]);

    expect($payment)->toBeInstanceOf(SubscriptionPayment::class)
        ->and($payment->order_id)->toBe('SUB-TEST-123')
        ->and($payment->isPending())->toBeTrue()
        ->and($payment->isSuccess())->toBeFalse();
});

test('subscription payment status helpers work correctly', function () {
    $cafe = Cafe::factory()->create();
    $subscription = Subscription::factory()->premium()->create();

    $pending = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-PENDING-1',
        'amount' => 200000,
        'status' => 'pending',
    ]);

    $success = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-SUCCESS-1',
        'amount' => 200000,
        'status' => 'success',
    ]);

    $failed = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-FAILED-1',
        'amount' => 200000,
        'status' => 'failed',
    ]);

    expect($pending->isPending())->toBeTrue()
        ->and($success->isSuccess())->toBeTrue()
        ->and($failed->isFailed())->toBeTrue();
});

// ---------------------------------------------------------------------------
// SubscriptionService – activate subscription
// ---------------------------------------------------------------------------

test('activate subscription updates cafe subscription_id', function () {
    $cafe = Cafe::factory()->create();
    $premiumSubscription = Subscription::factory()->premium()->create();

    $service = app(SubscriptionService::class);
    $service->activateSubscription($cafe, $premiumSubscription, 'trx-123');

    $cafe->refresh();

    expect($cafe->subscription_id)->toBe($premiumSubscription->id);
});

// ---------------------------------------------------------------------------
// Subscription plan enum – Free, Medium, Premium
// ---------------------------------------------------------------------------

test('subscription plan enum has free, medium, premium cases', function () {
    $cases = SubscriptionPlan::cases();

    expect($cases)->toHaveCount(3)
        ->and(collect($cases)->pluck('value')->toArray())->toContain('free', 'medium', 'premium');
});

test('free plan has correct default values', function () {
    $plan = SubscriptionPlan::Free;

    expect($plan->price())->toBe(0)
        ->and($plan->durationMonths())->toBe(0)
        ->and($plan->getLabel())->toBe('Free')
        ->and($plan->getColor())->toBe('gray');
});

test('medium plan has correct default values', function () {
    $plan = SubscriptionPlan::Medium;

    expect($plan->price())->toBe(150000)
        ->and($plan->durationMonths())->toBe(1)
        ->and($plan->getLabel())->toBe('Medium')
        ->and($plan->getColor())->toBe('primary');
});

test('premium plan has correct default values', function () {
    $plan = SubscriptionPlan::Premium;

    expect($plan->price())->toBe(200000)
        ->and($plan->durationMonths())->toBe(1)
        ->and($plan->getLabel())->toBe('Premium')
        ->and($plan->getColor())->toBe('primary');
});

test('free plan marketing features are correct', function () {
    $features = SubscriptionPlan::Free->marketingFeatures();

    expect($features)->toContain('10 Produk')
        ->and($features)->toContain('1 Kategori')
        ->and($features)->toContain('1 Staff')
        ->and($features)->toContain('2 Metode Pembayaran')
        ->and($features)->toContain('Laporan Dasar');
});

test('medium plan marketing features include core premium features', function () {
    $features = SubscriptionPlan::Medium->marketingFeatures();

    expect($features)->toContain('20 Produk')
        ->and($features)->toContain('7 Kategori')
        ->and($features)->toContain('8 Staff')
        ->and($features)->toContain('3 Metode Pembayaran')
        ->and($features)->toContain('Ekspor Laporan')
        ->and($features)->toContain('Manajemen Inventori')
        ->and($features)->toContain('Varian Produk')
        ->and($features)->toContain('Diskon Produk');
});

test('premium plan marketing features include all premium features', function () {
    $features = SubscriptionPlan::Premium->marketingFeatures();

    expect($features)->toContain('Produk Tidak Terbatas')
        ->and($features)->toContain('Kategori Tidak Terbatas')
        ->and($features)->toContain('Staff Tidak Terbatas')
        ->and($features)->toContain('Metode Pembayaran Tidak Terbatas')
        ->and($features)->toContain('Ekspor Laporan')
        ->and($features)->toContain('Manajemen Inventori')
        ->and($features)->toContain('Varian Produk')
        ->and($features)->toContain('Diskon Produk');
});

// ---------------------------------------------------------------------------
// SubscriptionPaymentController – unauthorized access
// ---------------------------------------------------------------------------

test('snap token endpoint requires authentication', function () {
    $response = $this->postJson(route('subscription.snap-token'), [
        'subscription_id' => 1,
    ]);

    $response->assertUnauthorized();
});

test('snap token endpoint requires manager role', function () {
    $user = User::factory()->create(['role' => 'cashier']);

    $response = $this->actingAs($user)->postJson(route('subscription.snap-token'), [
        'subscription_id' => 1,
    ]);

    $response->assertForbidden();
});

// ---------------------------------------------------------------------------
// iPaymu notification webhook
// ---------------------------------------------------------------------------

test('ipaymu notification endpoint is accessible without auth', function () {
    $response = $this->postJson(route('subscription.notification'), [
        'reference_id' => 'SUB-TEST-123',
        'status' => 'berhasil',
        'status_code' => 1,
    ], [
        'X-Signature' => 'invalid-signature',
    ]);

    // Should return 400 because signature is invalid, not 401
    $response->assertStatus(400);
});

test('subscription stays pending after payment return and activates only after a signed callback', function () {
    config()->set('ipaymu.va', 'global-va');

    $cafe = Cafe::factory()->create(['ipaymu_va' => 'cafe-va', 'ipaymu_api_key' => 'cafe-key']);
    $subscription = Subscription::factory()->premium()->create();
    $payment = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-CALLBACK-1',
        'amount' => $subscription->price,
        'status' => 'pending',
    ]);

    $returnUrl = route('subscription.finish', [
        'order_id' => $payment->order_id,
        'status' => 'berhasil',
        'trx_id' => '12345',
    ]);

    $this->get($returnUrl)->assertRedirect()->assertSessionHas('info');
    expect($payment->fresh()->status)->toBe('pending')
        ->and($cafe->fresh()->subscription_id)->toBeNull();

    $payload = [
        'reference_id' => $payment->order_id,
        'status' => 'berhasil',
        'status_code' => 1,
        'trx_id' => 12345,
    ];
    $signedPayload = [...$payload, 'additional_info' => []];
    ksort($signedPayload, SORT_STRING);
    $signature = hash_hmac('sha256', json_encode($signedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'cafe-va');

    $this->postJson(route('subscription.ipaymu.notification'), $payload, ['X-Signature' => 'invalid'])
        ->assertStatus(400);
    expect($payment->fresh()->status)->toBe('pending');

    $this->postJson(route('subscription.ipaymu.notification'), $payload, ['X-Signature' => $signature])
        ->assertOk();

    expect($payment->fresh()->status)->toBe('success')
        ->and($payment->fresh()->transaction_id)->toBe('12345')
        ->and($cafe->fresh()->subscription_id)->toBe($subscription->id);

    $this->get($returnUrl)->assertRedirect()->assertSessionHas('success');
    $this->postJson(route('subscription.ipaymu.notification'), $payload, ['X-Signature' => $signature])
        ->assertOk();

    expect($payment->fresh()->status)->toBe('success');

    $latePending = ['reference_id' => $payment->order_id, 'status' => 'pending', 'status_code' => 0];
    $signedPending = [...$latePending, 'additional_info' => []];
    ksort($signedPending, SORT_STRING);
    $pendingSignature = hash_hmac('sha256', json_encode($signedPending, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'cafe-va');

    $this->postJson(route('subscription.ipaymu.notification'), $latePending, ['X-Signature' => $pendingSignature])
        ->assertOk();

    expect($payment->fresh()->status)->toBe('success')
        ->and($cafe->fresh()->subscription_id)->toBe($subscription->id);
});

test('POS status reads callback result from database after a page refresh', function () {
    $cafe = Cafe::factory()->create(['ipaymu_va' => 'cafe-va', 'ipaymu_api_key' => 'cafe-key']);
    $cashier = User::factory()->create(['role' => 'cashier', 'cafe_id' => $cafe->id]);
    $transaction = Transaction::create([
        'cafe_id' => $cafe->id,
        'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-CALLBACK-1',
        'total_amount' => 10000,
        'status' => 'pending',
    ]);
    $method = PaymentMethod::factory()->create(['cafe_id' => $cafe->id, 'type' => 'qris']);
    $payment = Payment::create([
        'transaction_id' => $transaction->id,
        'payment_method_id' => $method->id,
        'amount' => 10000,
        'status' => 'pending',
    ]);

    $this->actingAs($cashier)->get(route('pos.check-status', $transaction->transaction_number))
        ->assertOk()->assertJsonPath('status', 'pending');

    $this->get(route('pos.finish', ['order_id' => $transaction->transaction_number]))
        ->assertRedirect()->assertSessionHas('info');
    expect($transaction->fresh()->status)->toBe('pending');

    $payload = [
        'reference_id' => $transaction->transaction_number,
        'status' => 'berhasil',
        'status_code' => 1,
        'trx_id' => 54321,
        'url' => 'https://public.example.com/cashier/pos/ipaymu-notification',
    ];
    $signedPayload = [...$payload, 'additional_info' => []];
    ksort($signedPayload, SORT_STRING);
    $signature = hash_hmac('sha256', json_encode($signedPayload, JSON_UNESCAPED_UNICODE), 'cafe-va');

    $this->post(route('pos.ipaymu.notification'), $payload, ['X-Signature' => $signature])
        ->assertOk();

    $this->actingAs($cashier)->get(route('pos.check-status', $transaction->transaction_number))
        ->assertOk()->assertJsonPath('status', 'success');
    expect($payment->fresh()->status)->toBe('success')
        ->and($transaction->fresh()->status)->toBe('completed');
});

test('POS can create another QRIS payment after a successful callback', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    $cafe = Cafe::factory()->create([
        'ipaymu_va' => 'cafe-va',
        'ipaymu_api_key' => 'cafe-key',
        'qris_type' => 'ipaymu',
    ]);
    $cashier = User::factory()->create(['role' => 'cashier', 'cafe_id' => $cafe->id]);
    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    $product = Product::factory()->create([
        'cafe_id' => $cafe->id,
        'category_id' => $category->id,
        'price' => 10000,
        'stock' => 5,
    ]);

    $gatewayTransactionId = 98765;
    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => function ($request) use (&$gatewayTransactionId) {
            return Http::response([
                'Status' => 200,
                'Success' => true,
                'Data' => [
                    'TransactionId' => ++$gatewayTransactionId,
                    'ReferenceId' => $request->data()['referenceId'],
                    'Qr' => '00020101021226600014ID.CO.QRIS.WWW',
                ],
            ]);
        },
    ]);

    $response = $this->actingAs($cashier)->postJson(route('pos.checkout'), [
        'cart' => [['id' => $product->id, 'qty' => 1]],
        'payment_method' => 'qris',
        'paid_amount' => 10000,
    ])->assertOk()
        ->assertJsonPath('qris_data.type', 'ipaymu')
        ->assertJsonPath('qris_data.checkout_url', '')
        ->assertJsonPath('qris_data.transaction_id', '98766');

    expect($response->json('qris_data.qr_url'))->toStartWith('data:image/svg+xml;base64,')
        ->and($response->json('qris_data.reference_id'))->toBe($response->json('transaction_number'))
        ->and(Payment::where('transaction_id', $response->json('transaction_id'))->firstOrFail()->reference_number)
        ->toBe($response->json('qris_data.reference_id'))
        ->and($product->fresh()->stock)->toBe(4);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://sandbox.ipaymu.com/api/v2/payment/direct'
        && $request->data()['referenceId'] === $response->json('qris_data.reference_id'));

    $payload = [
        'reference_id' => $response->json('qris_data.reference_id'),
        'status' => 'berhasil',
        'status_code' => 1,
        'trx_id' => 98766,
    ];
    $signedPayload = [...$payload, 'additional_info' => []];
    ksort($signedPayload, SORT_STRING);
    $signature = hash_hmac('sha256', json_encode($signedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'cafe-va');

    $this->postJson(route('pos.ipaymu.notification'), $payload, ['X-Signature' => $signature])
        ->assertOk();

    $secondResponse = $this->actingAs($cashier)->postJson(route('pos.checkout'), [
        'cart' => [['id' => $product->id, 'qty' => 1]],
        'payment_method' => 'qris',
        'paid_amount' => 10000,
    ])->assertOk()
        ->assertJsonPath('qris_data.transaction_id', '98767');

    expect($secondResponse->json('qris_data.qr_url'))->toStartWith('data:image/svg+xml;base64,')
        ->and($secondResponse->json('qris_data.reference_id'))->not->toBe($response->json('qris_data.reference_id'))
        ->and($product->fresh()->stock)->toBe(3);
});

test('return page verifies a paid sandbox session when the callback cannot reach localhost', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    $cafe = Cafe::factory()->create();
    $subscription = Subscription::factory()->premium()->create();
    $payment = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-VERIFY-1',
        'amount' => 200000,
        'status' => 'pending',
        'metadata' => ['session_id' => 'session-123'],
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/transaction' => Http::response([
            'Success' => true,
            'Data' => [
                'ReferenceId' => $payment->order_id,
                'SubTotal' => 200000,
                'Amount' => 203600,
                'PaidStatus' => 'paid',
                'Status' => 7,
                'TransactionId' => 234740,
            ],
        ]),
    ]);

    $this->get(route('subscription.finish', ['order_id' => $payment->order_id, 'status' => 'berhasil']))
        ->assertRedirect()->assertSessionHas('success');

    expect($payment->fresh()->status)->toBe('success')
        ->and($payment->fresh()->transaction_id)->toBe('234740')
        ->and($cafe->fresh()->subscription_id)->toBe($subscription->id);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://sandbox.ipaymu.com/api/v2/transaction'
        && $request->data()['transactionId'] === 'session-123');
});

test('return page rejects a paid gateway session for another order', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    $cafe = Cafe::factory()->create();
    $subscription = Subscription::factory()->premium()->create();
    $payment = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-VERIFY-2',
        'amount' => 200000,
        'status' => 'pending',
        'metadata' => ['session_id' => 'session-456'],
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/transaction' => Http::response([
            'Success' => true,
            'Data' => [
                'ReferenceId' => 'SOMEONE-ELSE',
                'SubTotal' => 200000,
                'PaidStatus' => 'paid',
                'Status' => 7,
                'TransactionId' => 234741,
            ],
        ]),
    ]);

    $this->get(route('subscription.finish', ['order_id' => $payment->order_id, 'status' => 'berhasil']))
        ->assertRedirect()->assertSessionHas('info');

    expect($payment->fresh()->status)->toBe('pending')
        ->and($cafe->fresh()->subscription_id)->toBeNull();
});

test('subscription widget restores a paid plan when the dashboard is reopened', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    $cafe = Cafe::factory()->create();
    $manager = User::factory()->create(['role' => 'manager', 'cafe_id' => $cafe->id]);
    $subscription = Subscription::factory()->premium()->create();
    $payment = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-WIDGET-1',
        'amount' => 200000,
        'status' => 'pending',
        'metadata' => ['session_id' => 'session-widget'],
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/transaction' => Http::response([
            'Success' => true,
            'Data' => [
                'ReferenceId' => $payment->order_id,
                'SubTotal' => 200000,
                'PaidStatus' => 'paid',
                'Status' => 7,
                'TransactionId' => 234742,
            ],
        ]),
    ]);

    Livewire::actingAs($manager)->test(SubscriptionUpgradeWidget::class)
        ->assertSet('hasPendingPayment', false);

    expect($payment->fresh()->status)->toBe('success')
        ->and($cafe->fresh()->subscription_id)->toBe($subscription->id);
});

test('dashboard opened in another tab notices a successful subscription callback', function () {
    $cafe = Cafe::factory()->create(['ipaymu_va' => 'cafe-va', 'ipaymu_api_key' => 'cafe-key']);
    $manager = User::factory()->create(['role' => 'manager', 'cafe_id' => $cafe->id]);
    $subscription = Subscription::factory()->premium()->create();

    $dashboard = Livewire::actingAs($manager)->test(SubscriptionUpgradeWidget::class)
        ->assertSet('hasPendingPayment', false)
        ->assertSet('showPaymentSuccess', false);

    $payment = SubscriptionPayment::create([
        'cafe_id' => $cafe->id,
        'subscription_id' => $subscription->id,
        'order_id' => 'SUB-OTHER-TAB-1',
        'amount' => $subscription->price,
        'status' => 'pending',
    ]);

    $dashboard->call('refreshPendingPayment')
        ->assertSet('hasPendingPayment', true)
        ->assertSet('showPaymentSuccess', false);

    $payload = [
        'reference_id' => $payment->order_id,
        'status' => 'berhasil',
        'status_code' => 1,
        'trx_id' => 54321,
    ];
    $signedPayload = [...$payload, 'additional_info' => []];
    ksort($signedPayload, SORT_STRING);
    $signature = hash_hmac('sha256', json_encode($signedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'cafe-va');

    $this->postJson(route('subscription.ipaymu.notification'), $payload, ['X-Signature' => $signature])
        ->assertOk();

    $dashboard->call('refreshPendingPayment')
        ->assertSet('hasPendingPayment', false)
        ->assertSet('showPaymentSuccess', true)
        ->assertSee('Refresh dashboard')
        ->assertNotified('Pembayaran berhasil');

    expect($cafe->fresh()->subscription_id)->toBe($subscription->id);
});
