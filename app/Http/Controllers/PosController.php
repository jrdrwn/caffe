<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use App\Models\CashFlow;
use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\MidtransService;
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
                    $product = $detail['product'];
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
                    'reference_number' => "{$paymentMethod}-{$transaction->transaction_number}",
                    'status' => $paymentStatus,
                ]);

                $qrisData = null;
                if ($paymentMethod === 'qris') {
                    $cafeRecord = Cafe::find($user->cafe_id);
                    $effectiveQrisType = $cafeRecord->qris_type ?? (filled($cafeRecord->midtrans_server_key) ? 'midtrans' : 'manual');

                    if ($effectiveQrisType === 'midtrans') {
                        try {
                            $midtransResponse = app(MidtransService::class)->generateQris($transaction);

                            // Find the QR code action in Midtrans response
                            $qrAction = collect($midtransResponse['actions'] ?? [])->where('name', 'generate-qr-code')->first();

                            $qrisData = [
                                'type' => 'midtrans',
                                'qr_url' => $qrAction['url'] ?? null,
                                'transaction_id' => $midtransResponse['transaction_id'] ?? null,
                                'expiry_time' => now()->addMinutes(15)->format('H:i'),
                            ];

                            if (empty($qrisData['qr_url'])) {
                                throw new \Exception('Midtrans tidak memberikan URL QR Code. Cek konfigurasi pembayaran.');
                            }

                            $payment->update(['metadata' => $qrisData]);
                        } catch (\Exception $e) {
                            Log::error('Midtrans QRIS Error: '.$e->getMessage());
                            throw new \Exception('Gagal terhubung ke Midtrans: '.$e->getMessage());
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

        // If already completed in our DB, just return success
        if ($transaction->status === 'completed') {
            return response()->json(['status' => 'success']);
        }

        // If pending, try to check Midtrans directly for latest status
        try {
            $midtrans = app(MidtransService::class)->forCafe($transaction->cafe);
            $statusResponse = $midtrans->checkStatus($transactionNumber);

            $transactionStatus = $statusResponse['transaction_status'] ?? '';

            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                $transaction->update(['status' => 'completed']);
                $transaction->payments()->update(['status' => 'success']);

                // Record to cash flow if not already done
                // Note: cafe project does not have CashFlow model yet, so we skip it or handle it if it exists
                if (class_exists(CashFlow::class)) {
                    CashFlow::firstOrCreate(
                        ['reference_id' => $transaction->id, 'reference_type' => 'transaction'],
                        [
                            'cafe_id' => $transaction->cafe_id,
                            'type' => 'income',
                            'category' => 'sales',
                            'amount' => $transaction->total_amount,
                            'description' => "Penjualan POS #{$transaction->transaction_number} (Midtrans Check)",
                            'created_by' => $transaction->cashier_id,
                        ]
                    );
                }

                return response()->json(['status' => 'success']);
            }

            if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                $transaction->update(['status' => 'cancelled']);

                return response()->json(['status' => 'failed']);
            }

        } catch (\Exception $e) {
            // Ignore errors during check, just return current local status
        }

        return response()->json(['status' => $transaction->status]);
    }

    public function cancelOrder(string $transactionNumber)
    {
        $user = Auth::user();
        $transaction = Transaction::where('transaction_number', $transactionNumber)
            ->where('cafe_id', $user->cafe_id)
            ->where('status', 'pending')
            ->first();

        if (! $transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan atau sudah diproses.'], 404);
        }

        DB::transaction(function () use ($transaction, $user) {
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
        });

        return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan dan stok telah kembali.']);
    }
}
