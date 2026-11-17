<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/resultats', [HomeController::class, 'rechercher'])->name('resultats');

Route::get('/reserver/{trip}', [ReservationController::class, 'create'])->name('reservation.create');
Route::post('/reserver/{trip}', [ReservationController::class, 'store'])->name('reservation.store');

Route::get('/paiement/{reservation}', [PaymentController::class, 'form'])->name('payment.form');
Route::post('/paiement/{reservation}', [PaymentController::class, 'payer'])->name('payment.payer');
Route::get('/billet/{reservation}', [PaymentController::class, 'billet'])->name('billet');

Route::get('/document/{passager}', [DocumentController::class, 'show'])->name('document');

Route::get('/mes-reservations', [ReservationController::class, 'mesReservations'])
    ->middleware('auth')
    ->name('reservations.mes');

Route::post('/annuler/{reservation}', [ReservationController::class, 'annuler'])
    ->middleware('auth')
    ->name('reservation.annuler');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::match(['get', 'post'], '/bus', [AdminController::class, 'bus'])->name('bus');

        Route::match(['get', 'post'], '/trajets', [AdminController::class, 'trajets'])->name('trajets');
        Route::post('/trajets/{trip}/supprimer', [AdminController::class, 'trajetsDestroy'])->name('trajets.destroy');

        Route::get('/reservations', [AdminController::class, 'reservations'])->name('reservations');
        Route::get('/reservation/{reservation}', [AdminController::class, 'reservation'])->name('reservation');
        Route::post('/reservation/{reservation}/documents', [AdminController::class, 'documents'])->name('documents');
    });
