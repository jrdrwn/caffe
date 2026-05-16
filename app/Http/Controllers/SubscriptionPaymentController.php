<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\MidtransService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Get Snap token for subscription upgrade.
     */
    public function getSnapToken(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
        ]);

        $user = Auth::user();

        if (! $user || ($user->role !== 'manager' && $user->role !== 'owner') || ! $user->cafe_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $cafe = $user->cafe;

        if (! $cafe) {
            return response()->json(['message' => 'Cafe not found.'], 404);
        }

        $subscription = Subscription::findOrFail($request->input('subscription_id'));

        // Security: Ensure plan is active and available
        if (! $subscription->is_active) {
            return response()->json(['message' => 'Paket langganan ini sedang tidak tersedia.'], 422);
        }

        // Security: Prevent downgrade to free plan if they already have an active paid plan (optional business rule)
        // Or at least prevent re-activating the exact same plan if it's still far from expiry
        if ($subscription->price <= 0) {
            if ($cafe->subscription_id === $subscription->id) {
                return response()->json(['message' => 'Anda sudah menggunakan paket ini.'], 422);
            }

            // Direct activation for free plans
            app(SubscriptionService::class)->activateSubscription($cafe, $subscription, 'free-plan-'.time());

            return response()->json([
                'message' => 'Langganan berhasil diaktifkan.',
                'redirect' => route('filament.manager.pages.manager-panel-dashboard'),
            ]);
        }

        // Security: Check for existing pending payments for this cafe to avoid duplicates
        $existingPending = SubscriptionPayment::where('cafe_id', $cafe->id)
            ->where('subscription_id', $subscription->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if ($existingPending && isset($existingPending->metadata['snap_token'])) {
            return response()->json([
                'token' => $existingPending->metadata['snap_token'],
                'client_key' => $this->midtransService->clientKey(),
                'snap_url' => $this->midtransService->snapUrl(),
                'message' => 'Melanjutkan pembayaran yang tertunda.',
            ]);
        }

        $token = $this->midtransService->createSnapToken($cafe, $subscription);

        return response()->json([
            'token' => $token,
            'client_key' => $this->midtransService->clientKey(),
            'snap_url' => $this->midtransService->snapUrl(),
        ]);
    }

    /**
     * Handle Midtrans notification webhook.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans notification received', $payload);

        try {
            $this->midtransService->handleNotification($payload);

            return response()->json(['message' => 'OK']);
        } catch (\Throwable $e) {
            Log::error('Midtrans notification failed', ['error' => $e->getMessage(), 'payload' => $payload]);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Payment finish callback (redirect after payment).
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');

        Log::info('Midtrans finish callback', ['order_id' => $orderId, 'status_code' => $statusCode]);

        if (app()->environment('local') && $orderId) {
            Log::info('Local fallback triggered for order', ['order_id' => $orderId]);
            try {
                $status = $this->midtransService->checkStatus($orderId);
                Log::info('Midtrans status response', $status);
                $transactionStatus = $status['transaction_status'] ?? '';

                if (in_array($transactionStatus, ['settlement', 'capture'])) {
                    $payment = SubscriptionPayment::where('order_id', $orderId)->first();

                    if ($payment) {
                        Log::info('Payment record found in fallback', ['current_status' => $payment->status]);

                        $payment->update([
                            'status' => 'success',
                            'transaction_id' => $status['transaction_id'] ?? null,
                            'settlement_time' => $status['settlement_time'] ?? now(),
                        ]);

                        Log::info('Activating subscription in fallback');
                        app(SubscriptionService::class)->activateSubscription(
                            $payment->cafe,
                            $payment->subscription,
                            $status['transaction_id'] ?? $orderId
                        );

                        return redirect()->route('filament.manager.pages.manager-panel-dashboard')
                            ->with('success', 'Pembayaran berhasil diverifikasi (Local Fallback). Paket Anda telah diperbarui.');
                    } else {
                        Log::warning('Payment record not found in fallback', ['order_id' => $orderId]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Local fallback status check failed', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('filament.manager.pages.manager-panel-dashboard')
            ->with('success', 'Pembayaran sedang diproses. Status langganan akan diperbarui setelah verifikasi.');
    }

    /**
     * Payment error callback.
     */
    public function error(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');

        Log::warning('Midtrans error callback', ['order_id' => $orderId]);

        return redirect()->route('filament.manager.pages.manager-panel-dashboard')
            ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
    }
}
