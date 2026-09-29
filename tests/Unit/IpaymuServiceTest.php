<?php

use App\Models\Cafe;
use App\Models\Transaction;
use App\Services\IpaymuService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('it creates a signed direct QRIS payment with iPaymu', function () {
    config()->set([
        'ipaymu.va' => '123456',
        'ipaymu.api_key' => 'test-api-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
        'ipaymu.callback_base_url' => 'https://public.example.com',
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Status' => 200,
            'Success' => true,
            'Data' => [
                'TransactionId' => 98765,
                'ReferenceId' => 'TRX-TEST-1',
                'Url' => 'https://sandbox.ipaymu.com/payment/98765',
            ],
        ]),
    ]);

    $cafe = new Cafe([
        'name' => 'Cafe Test',
        'phone' => '08123456789',
        'email' => 'cafe@example.com',
        'ipaymu_va' => '123456',
        'ipaymu_api_key' => 'test-api-key',
    ]);
    $transaction = new Transaction([
        'transaction_number' => 'TRX-TEST-1',
        'total_amount' => 10000,
    ]);
    $transaction->setRelation('cafe', $cafe);

    $result = app(IpaymuService::class)->generateQris($transaction);

    expect($result['reference_id'])->toBe('TRX-TEST-1')
        ->and($result['transaction_id'])->toBe('98765')
        ->and($result['checkout_url'])->toBe('https://sandbox.ipaymu.com/payment/98765')
        ->and($result['qr_url'])->toBe('');

    Http::assertSent(function (Request $request): bool {
        $body = $request->data();

        return $request->url() === 'https://sandbox.ipaymu.com/api/v2/payment/direct'
            && $request->header('va')[0] === '123456'
            && filled($request->header('signature')[0])
            && $body['paymentMethod'] === 'qris'
            && $body['paymentChannel'] === 'mpm'
            && $body['referenceId'] === 'TRX-TEST-1'
            && $body['notifyUrl'] === 'https://public.example.com/cashier/pos/ipaymu-notification';
    });
});

test('it accepts a direct QRIS response with a QR code and no checkout URL', function () {
    config()->set([
        'ipaymu.va' => '123456',
        'ipaymu.api_key' => 'test-api-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Status' => 200,
            'Success' => true,
            'Data' => [
                'TransactionId' => 98766,
                'ReferenceId' => 'TRX-TEST-QR',
                'Qr' => '00020101021226600014ID.CO.QRIS.WWW',
            ],
        ]),
    ]);

    $cafe = new Cafe(['name' => 'Cafe Test', 'ipaymu_va' => '123456', 'ipaymu_api_key' => 'test-api-key']);
    $transaction = new Transaction(['transaction_number' => 'TRX-TEST-QR', 'total_amount' => 10000]);
    $transaction->setRelation('cafe', $cafe);

    $result = app(IpaymuService::class)->generateQris($transaction);

    expect($result['reference_id'])->toBe('TRX-TEST-QR')
        ->and($result['transaction_id'])->toBe('98766')
        ->and($result['checkout_url'])->toBe('')
        ->and($result['qr_url'])->toStartWith('data:image/svg+xml;base64,');
});

test('it rejects a direct QRIS response without a URL or QR code', function () {
    config()->set([
        'ipaymu.va' => '123456',
        'ipaymu.api_key' => 'test-api-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Status' => 200,
            'Success' => true,
            'Data' => ['TransactionId' => 98767],
        ]),
    ]);

    $cafe = new Cafe(['name' => 'Cafe Test', 'ipaymu_va' => '123456', 'ipaymu_api_key' => 'test-api-key']);
    $transaction = new Transaction(['transaction_number' => 'TRX-TEST-EMPTY', 'total_amount' => 10000]);
    $transaction->setRelation('cafe', $cafe);

    expect(fn () => app(IpaymuService::class)->generateQris($transaction))
        ->toThrow(RuntimeException::class, 'iPaymu tidak mengembalikan URL atau kode QRIS.');
});

test('it requires the iPaymu transaction ID for a testable QRIS payment', function () {
    config()->set([
        'ipaymu.va' => '123456',
        'ipaymu.api_key' => 'test-api-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Status' => 200,
            'Success' => true,
            'Data' => [
                'ReferenceId' => 'TRX-TEST-NO-ID',
                'Qr' => '00020101021226600014ID.CO.QRIS.WWW',
            ],
        ]),
    ]);

    $cafe = new Cafe(['name' => 'Cafe Test', 'ipaymu_va' => '123456', 'ipaymu_api_key' => 'test-api-key']);
    $transaction = new Transaction(['transaction_number' => 'TRX-TEST-NO-ID', 'total_amount' => 10000]);
    $transaction->setRelation('cafe', $cafe);

    expect(fn () => app(IpaymuService::class)->generateQris($transaction))
        ->toThrow(RuntimeException::class, 'iPaymu tidak mengembalikan ID transaksi untuk Tes Notify.');
});

test('production mode uses the configured account instead of old cafe sandbox credentials', function () {
    config()->set([
        'ipaymu.va' => 'production-va',
        'ipaymu.api_key' => 'production-key',
        'ipaymu.is_production' => true,
        'ipaymu.api_url' => 'https://my.ipaymu.com/api/v2',
    ]);

    Http::fake([
        'https://my.ipaymu.com/api/v2/payment/direct' => Http::response([
            'Success' => true,
            'Data' => [
                'TransactionId' => 12345,
                'ReferenceId' => 'TRX-PRODUCTION-1',
                'Url' => 'https://my.ipaymu.com/payment/12345',
            ],
        ]),
    ]);

    $cafe = new Cafe(['name' => 'Cafe Test', 'ipaymu_va' => 'old-sandbox-va', 'ipaymu_api_key' => 'old-sandbox-key']);
    $transaction = new Transaction(['transaction_number' => 'TRX-PRODUCTION-1', 'total_amount' => 10000]);
    $transaction->setRelation('cafe', $cafe);

    app(IpaymuService::class)->generateQris($transaction);

    Http::assertSent(fn (Request $request): bool => $request->header('va')[0] === 'production-va');
});

test('production mode refuses to fall back to cafe sandbox credentials', function () {
    config()->set([
        'ipaymu.va' => '',
        'ipaymu.api_key' => '',
        'ipaymu.is_production' => true,
        'ipaymu.api_url' => 'https://my.ipaymu.com/api/v2',
    ]);

    Http::fake();

    $cafe = new Cafe(['name' => 'Cafe Test', 'ipaymu_va' => 'old-sandbox-va', 'ipaymu_api_key' => 'old-sandbox-key']);
    $transaction = new Transaction(['transaction_number' => 'TRX-PRODUCTION-EMPTY', 'total_amount' => 10000]);
    $transaction->setRelation('cafe', $cafe);

    expect(fn () => app(IpaymuService::class)->generateQris($transaction))
        ->toThrow(RuntimeException::class, 'iPaymu VA dan API key belum dikonfigurasi.');

    Http::assertNothingSent();
});

test('failed iPaymu API response does not expose gateway body', function () {
    config()->set([
        'ipaymu.va' => 'test-va',
        'ipaymu.api_key' => 'test-key',
        'ipaymu.api_url' => 'https://sandbox.ipaymu.com/api/v2',
    ]);

    Http::fake([
        'https://sandbox.ipaymu.com/api/v2/payment/direct' => Http::response('private gateway response', 502),
    ]);

    $cafe = new Cafe(['name' => 'Cafe Test']);
    $transaction = new Transaction(['transaction_number' => 'TRX-ERROR-1', 'total_amount' => 10000]);
    $transaction->setRelation('cafe', $cafe);

    expect(fn () => app(IpaymuService::class)->generateQris($transaction))
        ->toThrow(RuntimeException::class, 'iPaymu API error (HTTP 502).');
});
