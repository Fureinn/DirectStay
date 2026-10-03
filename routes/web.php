<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerSettingsController;
use App\Http\Controllers\Host\AuthController as HostAuthController;
use App\Http\Controllers\Host\CheckoutController as HostCheckoutController;
use App\Http\Controllers\Host\DashboardController as HostDashboardController;
use App\Http\Controllers\Host\UnitManagementController as HostUnitManagementController;
use App\Http\Controllers\Host\VerificationController as HostVerificationController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Direct-Booking Catalog & Compliance Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [BookingController::class, 'index'])->name('units.index');
Route::get('/units/{unit}', [BookingController::class, 'show'])->name('units.show');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::post('/bookings/{booking}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

Route::prefix('compliance')->name('compliance.')->group(function () {
    Route::get('/{bookingCode}', [ComplianceController::class, 'portal'])->name('portal');
    Route::post('/{bookingCode}/payment', [ComplianceController::class, 'uploadPayment'])->name('payment');
    Route::post('/{bookingCode}/identity', [ComplianceController::class, 'uploadIdentity'])->name('identity');
    Route::post('/{bookingCode}/waiver', [ComplianceController::class, 'acceptWaiver'])->name('waiver');
    Route::get('/{bookingCode}/status', [ComplianceController::class, 'status'])->name('status');
    Route::get('/{bookingCode}/gate-pass', [ComplianceController::class, 'downloadGatePass'])->name('downloadGatePass');
    Route::get('/{bookingCode}/rental-agreement', [ComplianceController::class, 'downloadRentalAgreement'])->name('downloadRentalAgreement');
});

/*
|--------------------------------------------------------------------------
| Customer / Guest Authentication & My Bookings
|--------------------------------------------------------------------------
*/
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.post');
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.post');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

Route::middleware('auth')->group(function () {
    Route::get('/my-bookings', [CustomerBookingController::class, 'index'])->name('customer.bookings');
    Route::get('/account/settings', [CustomerSettingsController::class, 'edit'])->name('customer.settings.edit');
    Route::put('/account/settings', [CustomerSettingsController::class, 'update'])->name('customer.settings.update');
});

/*
|--------------------------------------------------------------------------
| Host Portal Routes (Authentication & Management)
|--------------------------------------------------------------------------
*/
Route::prefix('host')->name('host.')->group(function () {
    Route::get('/login', [HostAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [HostAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [HostAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'host'])->group(function () {
        Route::get('/', [HostDashboardController::class, 'index'])->name('dashboard');

        // Unit Management & Photo Uploads
        Route::get('/units', [HostUnitManagementController::class, 'index'])->name('units.index');
        Route::get('/units/{unit}/edit', [HostUnitManagementController::class, 'edit'])->name('units.edit');
        Route::put('/units/{unit}', [HostUnitManagementController::class, 'update'])->name('units.update');
        Route::post('/units/{unit}/photos', [HostUnitManagementController::class, 'uploadPhotos'])->name('units.photos.upload');
        Route::post('/units/{unit}/cover', [HostUnitManagementController::class, 'setCover'])->name('units.photos.cover');
        Route::post('/units/{unit}/photos/delete', [HostUnitManagementController::class, 'deletePhoto'])->name('units.photos.delete');

        // Verification Triage & Private Document Viewing
        Route::get('/verifications', [HostVerificationController::class, 'index'])->name('verifications.index');
        Route::get('/verifications/{booking}', [HostVerificationController::class, 'show'])->name('verifications.show');
        Route::post('/verifications/{booking}/approve', [HostVerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('/verifications/{booking}/reject', [HostVerificationController::class, 'reject'])->name('verifications.reject');
        Route::get('/documents/{document}', [HostVerificationController::class, 'streamDocument'])->name('documents.stream');

        // Post-Checkout Digital Inventory Inspection & Penalty Engine
        Route::get('/checkouts/{booking}', [HostCheckoutController::class, 'show'])->name('checkouts.show');
        Route::post('/checkouts/{booking}', [HostCheckoutController::class, 'store'])->name('checkouts.store');
    });
});
