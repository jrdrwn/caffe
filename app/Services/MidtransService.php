<?php

namespace App\Services;

use App\Models\Cafe;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    private string $serverKey;
    private string $clientKey;

    private bool $isProduction;

    private string $baseUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key', '');
        $this->clientKey = (string) config('midtrans.client_key', '');
        $this->isProduction = (bool) config('midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Configure the service for a specific cafe's Midtrans credentials.
     */
    public function forCafe(Cafe $cafe): self
    {
        $clone = clone $this;

        if (filled($cafe->midtrans_server_key)) {
            $clone->serverKey = $cafe->midtrans_server_key;
            $clone->clientKey = $cafe->midtrans_client_key ?? '';
            $clone->isProduction = (bool) $cafe->midtrans_is_production;
            $clone->baseUrl = $clone->isProduction
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com';
        }

        return $clone;
    }

    /**
     * Generate a QRIS code for a transaction.
     */
    public function generateQris(\App\Models\Transaction $transaction): array
    {
        $cafe = $transaction->cafe;
        $orderId = $transaction->transaction_number;

        $items = $transaction->items->map(function ($item) {
            return [
                'id' => (string) $item->product_id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product->name ?? 'Item', 0, 50),
            ];
        })->toArray();

        if ($transaction->tax_amount > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => (int) $transaction->tax_amount,
                'quantity' => 1,
                'name' => 'Pajak (Tax)',
            ];
        }

        if ($transaction->service_charge_amount > 0) {
            $items[] = [
                'id' => 'SERVICE',
                'price' => (int) $transaction->service_charge_amount,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }

        if ($transaction->discount_amount > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) $transaction->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 15,
            ],
        ];

        $service = $this->forCafe($cafe);

        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withBasicAuth($service->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$service->baseUrl}/v2/charge", $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal generate QRIS Midtrans: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Create a Snap token for a POS transaction.
     */
    public function createTransactionSnapToken(\App\Models\Transaction $transaction): string
    {
        $cafe = $transaction->cafe;
        $orderId = $transaction->transaction_number;

        $items = $transaction->items->map(function ($item) {
            return [
                'id' => (string) $item->product_id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product->name ?? 'Item', 0, 50),
            ];
        })->toArray();

        if ($transaction->tax_amount > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => (int) $transaction->tax_amount,
                'quantity' => 1,
                'name' => 'Pajak (Tax)',
            ];
        }

        if ($transaction->service_charge_amount > 0) {
            $items[] = [
                'id' => 'SERVICE',
                'price' => (int) $transaction->service_charge_amount,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }

        if ($transaction->discount_amount > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) $transaction->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'customer_details' => [
                'first_name' => $cafe->name.' Customer',
            ],
            'item_details' => $items,
            'enabled_payments' => ['qris', 'gopay', 'shopeepay', 'other_qris'],
        ];

        $service = $this->forCafe($cafe);

        $response = Http::timeout(10)
            ->withBasicAuth($service->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$service->baseUrl}/snap/v1/transactions", $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal membuat Snap Token POS: '.$response->body());
        }

        return $response->json()['token'];
    }

    /**
     * Create a Snap token for subscription upgrade.
     */
    public function createSnapToken(Cafe $cafe, Subscription $subscription): string
    {
        $orderId = 'SUB-'.Str::upper(Str::random(8)).'-'.$cafe->id;

        $payment = SubscriptionPayment::create([
            'cafe_id' => $cafe->id,
            'subscription_id' => $subscription->id,
            'order_id' => $orderId,
            'amount' => $subscription->price,
            'status' => 'pending',
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $subscription->price,
            ],
            'customer_details' => [
                'first_name' => $cafe->name,
                'email' => $cafe->email,
                'phone' => $cafe->phone,
            ],
            'item_details' => [
                [
                    'id' => (string) $subscription->id,
                    'price' => (int) $subscription->price,
                    'quantity' => 1,
                    'name' => substr($subscription->name, 0, 50),
                ],
            ],
        ];

        $response = Http::timeout(10)
            ->withBasicAuth($this->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/snap/v1/transactions", $payload);

        if (! $response->successful()) {
            $payment->update(['status' => 'failed', 'metadata' => ['error' => $response->body()]]);
            throw new \RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        $data = $response->json();

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'snap_token' => $data['token'] ?? null,
                'redirect_url' => $data['redirect_url'] ?? null,
            ]),
        ]);

        return $data['token'];
    }

    /**
     * Handle Midtrans notification webhook.
     *
     * @param  array<string, mixed>  $notification
     */
    public function handleNotification(array $notification): SubscriptionPayment
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        if (! $this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            throw new \RuntimeException('Invalid Midtrans signature.');
        }

        $payment = SubscriptionPayment::where('order_id', $orderId)->firstOrFail();

        $transactionStatus = $notification['transaction_status'] ?? '';
        $paymentType = $notification['payment_type'] ?? null;
        $transactionId = $notification['transaction_id'] ?? null;
        $transactionTime = $notification['transaction_time'] ?? null;
        $settlementTime = $notification['settlement_time'] ?? null;

        $status = match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expire',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'payment_type' => $paymentType,
            'transaction_id' => $transactionId,
            'transaction_time' => $transactionTime,
            'settlement_time' => $settlementTime,
            'metadata' => array_merge($payment->metadata ?? [], $notification),
        ]);

        if ($status === 'success') {
            app(SubscriptionService::class)->activateSubscription(
                $payment->cafe,
                $payment->subscription,
                $transactionId ?? $orderId
            );
        }

        return $payment;
    }
    
    /**
     * Check transaction status from Midtrans API.
     */
    public function checkStatus(string $orderId): array
    {
        $response = Http::timeout(10)
            ->withBasicAuth($this->serverKey, '')
            ->get("{$this->baseUrl}/v2/{$orderId}/status");

        return $response->json() ?? [];
    }

    /**
     * Verify Midtrans notification signature.
     */
    private function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expected, $signatureKey);
    }

    /**
     * Get the Snap JS URL.
     */
    public function snapUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Get the client key.
     */
    public function clientKey(): string
    {
        return (string) config('midtrans.client_key', '');
    }
}
