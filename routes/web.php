<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// POS checkout endpoint used by the Filament Cashier POS page
Route::post('/cashier/pos/checkout', [PosController::class, 'checkout'])
    ->name('pos.checkout')
    ->middleware(['auth', 'pos.validate']);

Route::get('/cashier/pos/check-status/{transactionNumber}', [PosController::class, 'checkStatus'])
    ->name('pos.check-status')
    ->middleware(['auth']);

Route::get('/cashier/pos/finish', [PosController::class, 'finish'])
    ->name('pos.finish');

Route::post('/cashier/pos/cancel/{transactionNumber}', [PosController::class, 'cancelOrder'])
    ->name('pos.cancel')
    ->middleware(['auth']);

// Receipt printing route
Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
    ->name('transactions.receipt')
    ->middleware(['auth']);

// Subscription payment routes
Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::post('/snap-token', [SubscriptionPaymentController::class, 'getSnapToken'])
        ->name('snap-token')
        ->middleware(['auth']);

    Route::post('/notification', [SubscriptionPaymentController::class, 'handleNotification'])
        ->name('notification');

    Route::post('/ipaymu/notification', [SubscriptionPaymentController::class, 'handleNotification'])
        ->name('ipaymu.notification');

    Route::get('/finish', [SubscriptionPaymentController::class, 'finish'])
        ->name('finish');

    Route::get('/error', [SubscriptionPaymentController::class, 'error'])
        ->name('error');
});

Route::post('/cashier/pos/ipaymu-notification', [PosController::class, 'handleIpaymuNotification'])
    ->name('pos.ipaymu.notification');

// Root redirect based on role
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('filament.manager.auth.login');
    }

    $user = auth()->user();

    return match ($user->role) {
        'super_admin' => redirect('/admin'),
        'manager' => redirect('/manajer'),
        'cashier' => redirect('/cashier'),
        default => redirect('/manajer/login'),
    };
});

require __DIR__.'/settings.php';

// Public routes (no auth required)
Route::get('/public', function () {
    return view('public.landing');
})->name('public.landing');

Route::get('/doc/{slug}', [PublicController::class, 'showDoc'])->name('public.doc');
