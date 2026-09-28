<?php

/**
 * Manual webhook simulator for iPaymu subscription payment
 */

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Payload dari log error user
$payloadRaw = [
    'trx_id' => 234715,
    'sid' => 'ada43805-2308-473a-90e3-193dc5db9ecb',
    'reference_id' => 'SUB-TMJPWBOM-1',
    'status' => 'berhasil',
    'status_code' => 1,
    'sub_total' => '150000',
    'total' => '152700',
    'amount' => '152700',
    'fee' => '3750',
    'paid_off' => 148950,
    'created_at' => '2026-09-24 14:23:30',
    'expired_at' => '2026-09-24 15:23:30',
    'paid_at' => '2026-09-24 14:23:44',
    'settlement_status' => 'settled',
    'transaction_status_code' => 7,
    'is_escrow' => true,
    'system_notes' => 'Sandbox notify',
    'via' => 'qris',
    'channel' => 'qris',
    'payment_no' => '',
    'buyer_name' => 'Cafe Sample',
    'buyer_email' => 'cafe@example.com',
    'buyer_phone' => '021888999',
    'additional_info' => [],
    'url' => 'http://cafe.test/subscription/ipaymu/notification',
];

// Normalize payload sesuai logic di IpaymuService (skipping 'url' field)
$normalized = [];
foreach ($payloadRaw as $key => $value) {
    // Skip URL field - not part of signature calculation
    if ($key === 'url') {
        continue;
    }

    if (in_array($key, ['trx_id', 'status_code', 'transaction_status_code', 'paid_off'], true)) {
        $normalized[$key] = is_numeric($value) ? (int) $value : $value;
    } elseif ($key === 'is_escrow') {
        $normalized[$key] = in_array($value, [true, 1, '1', 'true'], true);
    } elseif ($key === 'additional_info') {
        $normalized[$key] = is_array($value) ? $value : [];
    } else {
        $normalized[$key] = is_array($value)
            ? json_encode($value)
            : (string) $value;
    }
}
$normalized['additional_info'] = [];

ksort($normalized, SORT_STRING);
$jsonBody = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$expectedSignature = hash_hmac('sha256', $jsonBody, '0000002340646756');

echo "Normalized JSON:\n";
echo $jsonBody."\n\n";
echo "Expected Signature:\n";
echo $expectedSignature."\n\n";

// Kirim POST ke local endpoint
$response = Http::withHeaders([
    'Content-Type' => 'application/x-www-form-urlencoded',
    'X-Signature' => $expectedSignature,
    'Accept' => 'application/json',
])->post('http://cafe.test/subscription/ipaymu/notification', $normalized);

echo 'Status Code: '.$response->status().PHP_EOL;
echo 'Response Body: '.$response->body().PHP_EOL;
echo PHP_EOL.'Test completed!'.PHP_EOL;
