<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidatePosRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user || ($user->role !== 'cashier' && $user->role !== 'manager')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $user->cafe_id) {
            return response()->json(['message' => 'User belum terhubung ke cafe.'], 403);
        }

        $cart = $request->input('cart');
        if (empty($cart) || ! is_array($cart)) {
            return response()->json(['message' => 'Keranjang tidak boleh kosong.'], 422);
        }

        $cafe = $user->cafe;
        $taxRate = (int) ($cafe?->tax_percentage ?? 0);
        $serviceRate = (int) ($cafe?->service_charge_percentage ?? 0);

        $subtotal = 0;
        $calculatedDiscount = 0;
        $cartDetails = [];

        foreach ($cart as $item) {
            $product = Product::where('id', $item['id'])
                ->where('cafe_id', $user->cafe_id)
                ->first();

            if (! $product) {
                return response()->json(['message' => "Produk #{$item['id']} tidak ditemukan untuk cafe ini."], 422);
            }

            if (! $product->is_active) {
                return response()->json(['message' => "Produk {$product->name} sedang tidak aktif."], 422);
            }

            $qty = (int) $item['qty'];
            if ($qty > $product->stock) {
                return response()->json(['message' => "Stok tidak cukup untuk produk: {$product->name}."], 422);
            }

            $itemSubtotal = $product->price * $qty;
            $subtotal += $itemSubtotal;

            $itemDiscount = (int) round($product->price * ($product->discount_percentage / 100)) * $qty;
            $calculatedDiscount += $itemDiscount;

            $cartDetails[] = [
                'product' => $product,
                'qty' => $qty,
                'price' => (int) $product->price,
                'discount_pct' => (int) $product->discount_percentage,
                'subtotal' => $itemSubtotal,
                'notes' => $item['notes'] ?? null,
            ];
        }

        $netSubtotal = $subtotal - $calculatedDiscount;
        $taxAmount = (int) round($netSubtotal * $taxRate / 100);
        $serviceAmount = (int) round($netSubtotal * $serviceRate / 100);
        $totalAmount = $netSubtotal + $taxAmount + $serviceAmount;

        $paidAmount = (int) $request->input('paid_amount', 0);

        // Final sanity check on price
        if ($paidAmount < $totalAmount) {
            return response()->json([
                'message' => 'Jumlah pembayaran kurang dari total tagihan.',
                'server_total' => $totalAmount,
                'received_total' => $paidAmount,
            ], 422);
        }

        // Attach validated data to request for controller to use
        $request->merge([
            'pos_validated_data' => [
                'cart_details' => $cartDetails,
                'total_amount' => $totalAmount,
                'discount_amount' => $calculatedDiscount,
                'tax_amount' => $taxAmount,
                'service_charge_amount' => $serviceAmount,
                'tax_rate' => $taxRate,
                'service_rate' => $serviceRate,
                'subtotal' => $subtotal,
            ],
        ]);

        return $next($request);
    }
}
