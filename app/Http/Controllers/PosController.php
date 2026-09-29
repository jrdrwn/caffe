<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\IpaymuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $validated = $request->input('pos_validated_data');

        if (! $validated) {
            return response()->json(['message' => 'Data validasi tidak ditemukan.'], 500);
        }

        $paymentMethod = $request->input('payment_method');
        $paidAmount = (int) $request->input('paid_amount');

        $totalAmount = $validated['total_amount'];
        $discountAmount = $validated['discount_amount'];
        $taxAmount = $validated['tax_amount'];
        $serviceAmount = $validated['service_charge_amount'];
        $cartDetails = $validated['cart_details'];
        $changeAmount = max(0, $paidAmount - $totalAmount);

        try {
            return DB::transaction(function () use ($user, $cartDetails, $paymentMethod, $totalAmount, $discountAmount, $taxAmount, $serviceAmount, $paidAmount, $changeAmount) {
                $requestedQuantities = [];
                foreach ($cartDetails as $detail) {
                    $productId = $detail['product']->id;
                    $requestedQuantities[$productId] = ($requestedQuantities[$productId] ?? 0) + $detail['qty'];
                }

                $lockedProducts = Product::where('cafe_id', $user->cafe_id)
                    ->whereIn('id', array_keys($requestedQuantities))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($requestedQuantities as $productId => $quantity) {
                    $product = $lockedProducts->get($productId);
                    if (! $product || ! $product->is_active || $product->stock < $quantity) {
                        throw new \RuntimeException('Stok produk berubah. Perbarui keranjang dan coba lagi.');
                    }
                }

                // Transaction status mirrors payment settlement:
                // cash = completed immediately; debit/qris = pending until confirmed
                $transactionStatus = $paymentMethod === 'cash' ? 'completed' : 'pending';

                $transaction = Transaction::create([
                    'cafe_id' => $user->cafe_id,
                    'cashier_id' => $user->id,
                    'transaction_number' => 'TRX'.time().rand(1000, 9999),
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'service_charge_amount' => $serviceAmount,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'status' => $transactionStatus,
                    'notes' => "POS checkout - {$paymentMethod} payment",
                ]);

                foreach ($cartDetails as $detail) {
                    $product = $lockedProducts->get($detail['product']->id);
                    $qty = $detail['qty'];

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $detail['price'],
                        'subtotal' => $detail['subtotal'],
                        'notes' => $detail['notes'],
                    ]);

                    $before = $product->stock;
                    $product->decrement('stock', $qty);
                    $after = $product->stock;

                    InventoryLog::create([
                        'cafe_id' => $user->cafe_id,
                        'product_id' => $product->id,
                        'action' => 'sale',
                        'quantity_change' => -$qty,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'reference_id' => $transaction->id,
                        'reference_type' => 'transaction',
                        'notes' => "POS sale - {$paymentMethod}",
                        'created_by' => $user->id,
                    ]);
                }

                $paymentStatus = match ($paymentMethod) {
                    'cash' => 'success',
                    'debit' => 'pending',
                    'qris' => 'pending',
                    default => 'pending'
                };

                // Resolve or auto-create the payment method record for this cafe
                $paymentMethodRecord = PaymentMethod::firstOrCreate(
                    ['cafe_id' => $user->cafe_id, 'type' => $paymentMethod],
                    ['name' => strtoupper($paymentMethod), 'is_active' => true]
                );

                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethodRecord->id,
                    'amount' => $paidAmount,
                    'reference_number' => $paymentMethod === 'qris'
                        ? $transaction->transaction_number
                        : "{$paymentMethod}-{$transaction->transaction_number}",
                    'status' => $paymentStatus,
                ]);

                $qrisData = null;
                if ($paymentMethod === 'qris') {
                    $cafeRecord = Cafe::find($user->cafe_id);
                    $effectiveQrisType = $cafeRecord->qris_type ?? (filled($cafeRecord->ipaymu_va) ? 'ipaymu' : 'manual');

                    if ($effectiveQrisType === 'ipaymu') {
                        try {
                            $ipaymuResponse = app(IpaymuService::class)->generateQris($transaction);

                            $qrisData = [
                                'type' => 'ipaymu',
                                'reference_id' => $ipaymuResponse['reference_id'],
                                'qr_url' => $ipaymuResponse['qr_url'] ?? null,
                                'checkout_url' => $ipaymuResponse['checkout_url'] ?? null,
                                'transaction_id' => $ipaymuResponse['transaction_id'] ?? null,
                                'expiry_time' => now()->addMinutes(15)->format('H:i'),
                            ];

                            if (empty($qrisData['checkout_url']) && empty($qrisData['qr_url'])) {
                                throw new \Exception('iPaymu tidak memberikan URL atau kode QRIS. Cek konfigurasi pembayaran.');
                            }

                            $payment->update([
                                'gateway_transaction_id' => $qrisData['transaction_id'],
                            ]);
                        } catch (\Exception $e) {
                            Log::error('iPaymu QRIS Error: '.$e->getMessage());
                            throw new \Exception('Gagal terhubung ke iPaymu: '.$e->getMessage());
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'total_amount' => $totalAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'qris_data' => $qrisData,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function checkStatus(string $transactionNumber)
    {
        $transaction = Transaction::where('transaction_number', $transactionNumber)
            ->where('cafe_id', Auth::user()->cafe_id)
            ->first();

        if (! $transaction) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json(['status' => match ($transaction->status) {
            'completed' => 'success',
            'cancelled' => 'failed',
            default => 'pending',
        }]);
    }

    public function cancelOrder(string $transactionNumber)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($transactionNumber, $user) {
            $transaction = Transaction::where('transaction_number', $transactionNumber)
                ->where('cafe_id', $user->cafe_id)
                ->lockForUpdate()
                ->first();

            if (! $transaction || $transaction->status !== 'pending') {
                return response()->json(['message' => 'Transaksi tidak ditemukan atau sudah diproses.'], 404);
            }

            if ($transaction->payments()->whereNotNull('gateway_transaction_id')->exists()) {
                return response()->json(['message' => 'Pembayaran iPaymu sedang diproses. Tunggu notifikasi pembayaran.'], 409);
            }

            $transaction->update(['status' => 'cancelled']);
            $transaction->payments()->update(['status' => 'failed']);

            foreach ($transaction->items as $item) {
                $product = $item->product;
                if ($product) {
                    $before = $product->stock;
                    $product->increment('stock', $item->quantity);
                    $after = $product->stock;

                    InventoryLog::create([
                        'cafe_id' => $user->cafe_id,
                        'product_id' => $product->id,
                        'action' => 'adjustment',
                        'quantity_change' => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'reference_id' => $transaction->id,
                        'reference_type' => 'transaction',
                        'notes' => "POS Order Cancelled - Stock Returned (#{$transaction->transaction_number})",
                        'created_by' => $user->id,
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan dan stok telah kembali.']);
        });
    }

    public function handleIpaymuNotification(Request $request)
    {
        try {
            $content = $request->getContent();
            $decoded = str_starts_with(ltrim($content), '{') ? json_decode($content, true) : null;
            $payload = is_array($decoded) ? $decoded : $request->all();

            app(IpaymuService::class)->handleNotification($payload, $request->headers->all());

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $exception) {
            Log::error('iPaymu POS notification failed', ['error' => $exception->getMessage()]);

            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }

    public function finish(Request $request)
    {
        $orderId = $request->input('order_id');

        Log::info('iPaymu POS finish redirect triggered', ['order_id' => $orderId]);

        return redirect('/cashier')
            ->with('info', 'Status pembayaran akan diperbarui setelah notifikasi iPaymu diterima.');
    }
}
