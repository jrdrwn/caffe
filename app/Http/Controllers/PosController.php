<?php

namespace App\Http\Controllers;

use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:products,id',
            'cart.*.qty' => 'required|integer|min:1',
            'payment_method' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $cart = $request->input('cart');
        $paymentMethod = $request->input('payment_method', 'cash');

        return DB::transaction(function () use ($user, $cart) {
            $total = 0;
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['id']);
                $total += ($product->price * $item['qty']);
            }

            $transaction = Transaction::create([
                'cafe_id' => $user->cafe_id,
                'cashier_id' => $user->id,
                'transaction_number' => 'TRX'.time().rand(100, 999),
                'total_amount' => $total,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'paid_amount' => $total,
                'change_amount' => 0,
                'status' => 'completed',
                'notes' => 'POS checkout',
            ]);

            foreach ($cart as $item) {
                $product = Product::findOrFail($item['id']);
                $qty = (int) $item['qty'];
                $subtotal = $product->price * $qty;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'notes' => null,
                ]);

                // Update stock and inventory log
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
                    'notes' => 'POS sale',
                    'created_by' => $user->id,
                ]);
            }

            // Fake payment record
            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method_id' => null,
                'amount' => $total,
                'reference_number' => 'FAKE-'.$transaction->transaction_number,
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
            ]);
        });
    }
}
