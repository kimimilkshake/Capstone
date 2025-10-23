<?php

use App\Http\Controllers\Admin\PromoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VesselRouteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\StaffController;

Route::get('/', function () {
    return view('passenger.homepage');
});

Route::get('/passenger/homepage', function () {
    return view('passenger.homepage');
})->name('homepage');

Route::get('/passenger/book', function () {
    return view('passenger.book');
})->name('book');

//passenger booking route
Route::get('/passenger/passenger', [VesselRouteController::class, 'index'])->name('passenger');

Route::get('/passenger/schedules', function () {
    return view('passenger.schedules');
})->name('schedules');

Route::get('/passenger/about', function () {
    return view('passenger.about');
})->name('about');

Route::get('/passenger/faqs', function () {
    return view('passenger.faqs');
})->name('faqs');

Route::get('/passenger/contact', function () {
    return view('passenger.contact');
})->name('contact');


// Show login page
Route::get('/authorized/login', [AuthController::class, 'showLoginForm'])->name('login.form');

// Process login
Route::post('/authorized/login', [AuthController::class, 'login'])->name('login');

// Dashboard (protected)
//Route::get('/authorized/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

//ADMIN PAGES
// Admin Dashboard
/*
Route::get('/authorized/admin/dashboard', function () {
    return view('authorized.admin.dashboard');
})->name('admin.dashboard');
*/

Route::prefix('authorized/admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('authorized.admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/create_staff', [StaffController::class, 'create'])->name('admin.create_staff');
    Route::post('/create_staff', [StaffController::class, 'store'])->name('admin.storeStaff');
    Route::get('/staff_list', [StaffController::class, 'index'])->name('admin.staff_list');
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('admin.staff_edit');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('admin.staff_update');
    Route::get('/promo_list', [PromoController::class, 'index'])->name('admin.promo_list');
    Route::get('/create_promo', [PromoController::class, 'create'])->name('admin.create_promo');
    Route::post('/create_promo', [PromoController::class, 'store'])->name('admin.storePromo');
    Route::get('/promo/{id}/edit', [PromoController::class, 'edit'])->name('admin.promo_edit');
    Route::put('/promo/{id}', [PromoController::class, 'update'])->name('admin.promo_update');
});




//STAFF PAGES
// Staff Dashboard
Route::get('/authorized/staff/dashboard', function () {
    return view('authorized.staff.dashboard');
})->name('staff.dashboard');


Route::post('/authorized/logout', [AuthController::class, 'logout'])->name('logout');


