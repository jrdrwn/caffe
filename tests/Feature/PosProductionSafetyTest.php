<?php

use App\Models\Cafe;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function signedPosPayload(array $payload): string
{
    $signedPayload = [...$payload, 'additional_info' => []];
    ksort($signedPayload, SORT_STRING);

    return hash_hmac('sha256', json_encode($signedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'cafe-va');
}

function createPendingPosPayment(): array
{
    $cafe = Cafe::factory()->create(['ipaymu_va' => 'cafe-va', 'ipaymu_api_key' => 'cafe-key']);
    $cashier = User::factory()->create(['role' => 'cashier', 'cafe_id' => $cafe->id]);
    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    $product = Product::factory()->create(['cafe_id' => $cafe->id, 'category_id' => $category->id, 'stock' => 4]);
    $transaction = Transaction::create([
        'cafe_id' => $cafe->id,
        'cashier_id' => $cashier->id,
        'transaction_number' => 'TRX-SAFETY-'.$product->id,
        'total_amount' => 10000,
        'status' => 'pending',
    ]);
    TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'subtotal' => 10000,
    ]);
    $method = PaymentMethod::factory()->create(['cafe_id' => $cafe->id, 'type' => 'qris']);
    $payment = Payment::create([
        'transaction_id' => $transaction->id,
        'payment_method_id' => $method->id,
        'amount' => 10000,
        'reference_number' => $transaction->transaction_number,
        'gateway_transaction_id' => '12345',
        'status' => 'pending',
    ]);

    return compact('cashier', 'product', 'transaction', 'payment');
}

test('POS rejects nonpositive and duplicate quantities before changing stock', function () {
    $cafe = Cafe::factory()->create();
    $cashier = User::factory()->create(['role' => 'cashier', 'cafe_id' => $cafe->id]);
    $category = Category::factory()->create(['cafe_id' => $cafe->id]);
    $product = Product::factory()->create(['cafe_id' => $cafe->id, 'category_id' => $category->id, 'stock' => 3]);

    foreach ([-1, 0] as $quantity) {
        $this->actingAs($cashier)->postJson(route('pos.checkout'), [
            'cart' => [['id' => $product->id, 'qty' => $quantity]],
            'payment_method' => 'cash',
            'paid_amount' => 10000,
        ])->assertUnprocessable();
    }

    $this->actingAs($cashier)->postJson(route('pos.checkout'), [
        'cart' => [
            ['id' => $product->id, 'qty' => 2],
            ['id' => $product->id, 'qty' => 2],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 1000000,
    ])->assertUnprocessable();

    $this->actingAs($cashier)->postJson(route('pos.checkout'), [
        'cart' => [['id' => $product->id, 'qty' => 1]],
        'payment_method' => 'unknown',
        'paid_amount' => 1000000,
    ])->assertUnprocessable();

    $this->actingAs($cashier)->postJson(route('pos.checkout'), [
        'cart' => [['id' => $product->id, 'qty' => 1]],
        'payment_method' => 'qris',
        'paid_amount' => 1000000,
    ])->assertUnprocessable();

    expect($product->fresh()->stock)->toBe(3)
        ->and(Transaction::where('cafe_id', $cafe->id)->count())->toBe(0);
});

test('active iPaymu order cannot be cancelled locally', function () {
    ['cashier' => $cashier, 'product' => $product, 'transaction' => $transaction] = createPendingPosPayment();

    $this->actingAs($cashier)->postJson(route('pos.cancel', $transaction->transaction_number))
        ->assertStatus(409);

    expect($transaction->fresh()->status)->toBe('pending')
        ->and($product->fresh()->stock)->toBe(4);
});

test('POS callback rejects a different gateway transaction ID', function () {
    ['product' => $product, 'transaction' => $transaction, 'payment' => $payment] = createPendingPosPayment();
    $payload = ['reference_id' => $transaction->transaction_number, 'status' => 'berhasil', 'status_code' => 1, 'trx_id' => 99999];

    $this->postJson(route('pos.ipaymu.notification'), $payload, ['X-Signature' => signedPosPayload($payload)])
        ->assertStatus(400);

    expect($transaction->fresh()->status)->toBe('pending')
        ->and($payment->fresh()->status)->toBe('pending')
        ->and($product->fresh()->stock)->toBe(4);
});

test('POS callback rejects an amount different from the order', function () {
    ['transaction' => $transaction, 'payment' => $payment] = createPendingPosPayment();
    $payload = ['reference_id' => $transaction->transaction_number, 'status' => 'berhasil', 'status_code' => 1, 'trx_id' => 12345, 'amount' => '1000'];

    $this->postJson(route('pos.ipaymu.notification'), $payload, ['X-Signature' => signedPosPayload($payload)])
        ->assertStatus(400);

    expect($transaction->fresh()->status)->toBe('pending')
        ->and($payment->fresh()->status)->toBe('pending');
});

test('failed POS callback restores reserved stock exactly once', function () {
    ['product' => $product, 'transaction' => $transaction, 'payment' => $payment] = createPendingPosPayment();
    $payload = ['reference_id' => $transaction->transaction_number, 'status' => 'gagal', 'status_code' => 2, 'trx_id' => 12345];

    $this->postJson(route('pos.ipaymu.notification'), $payload, ['X-Signature' => signedPosPayload($payload)])
        ->assertOk();
    $this->postJson(route('pos.ipaymu.notification'), $payload, ['X-Signature' => signedPosPayload($payload)])
        ->assertOk();

    expect($transaction->fresh()->status)->toBe('cancelled')
        ->and($payment->fresh()->status)->toBe('failed')
        ->and($product->fresh()->stock)->toBe(5)
        ->and(InventoryLog::where('reference_id', $transaction->id)->where('action', 'adjustment')->count())->toBe(1);
});

test('cafe serialization hides payment gateway secrets', function () {
    $cafe = new Cafe(['ipaymu_api_key' => 'private-key', 'midtrans_server_key' => 'legacy-key']);

    expect($cafe->toArray())->not->toHaveKeys(['ipaymu_api_key', 'midtrans_server_key']);
});

test('cafe API keys are encrypted and legacy plaintext keys are migrated', function () {
    $cafe = Cafe::factory()->create(['ipaymu_api_key' => 'new-secret']);

    expect($cafe->fresh()->ipaymu_api_key)->toBe('new-secret')
        ->and(DB::table('cafes')->where('id', $cafe->id)->value('ipaymu_api_key'))->not->toBe('new-secret');

    DB::table('cafes')->where('id', $cafe->id)->update(['ipaymu_api_key' => 'legacy-plaintext']);
    $migration = require database_path('migrations/2026_09_29_023329_encrypt_cafe_ipaymu_api_keys.php');
    $migration->up();

    expect($cafe->fresh()->ipaymu_api_key)->toBe('legacy-plaintext')
        ->and(DB::table('cafes')->where('id', $cafe->id)->value('ipaymu_api_key'))->not->toBe('legacy-plaintext');
});
