<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main pages
Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Form submissions
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/reseller', [ResellerController::class, 'store'])->name('reseller.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');

// API routes for AJAX requests
Route::prefix('api')->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
    Route::post('/reseller', [ResellerController::class, 'store']);
    Route::post('/newsletter', [NewsletterController::class, 'store']);
});

// Test route to verify Resend mailer
Route::get('/test-email', function () {
    try {
        Mail::raw('<h1>RWAMP Test Email</h1><p>This is a test via Resend.</p>', function ($message) {
            $message->to('suresh.kumar@markproperties.pk')
                ->subject('RWAMP Test Email');
        });
        Log::info('Test email sent via Resend');
        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        Log::error('Test email failed', ['error' => $e->getMessage()]);
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
});
