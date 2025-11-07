<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VesselRouteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OcrController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\VesselController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\PortController;

// SHARED CONTROLLERS
use App\Http\Controllers\VoyageController;

// OCR route
Route::post('/ocr/parse', [OcrController::class, 'parseImage'])->name('ocr.parse');

Route::get('/', function () {
    return view('passenger.homepage');
});

Route::get('/passenger/homepage', function () {
    return view('passenger.homepage');
})->name('homepage');

Route::get('/passenger/book', function () {
    return view('passenger.book');
})->name('book');

// Passenger booking route
// Show the available routes and selection page
Route::get('/passenger/passenger', [VesselRouteController::class, 'index'])->name('passenger');

// Booking page (form)
Route::get('/passenger/passengerbooking', [PassengerController::class, 'index'])->name('passengerbooking');

// Form submission
Route::post('/passenger/store', [PassengerController::class, 'store'])->name('passenger.store');
Route::post('/booking/submit', [BookingController::class, 'store'])->name('booking.submit');

// Confirm booking
Route::get('/passenger/confirmbooking', function () {
    return view('passenger.confirmbooking');
})->name('passenger.confirmbooking');

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

// Forgot password
Route::get('/authorized/forgot_password', function () {
    return view('authorized.forgot_password');
})->name('authorized.forgot_password');

// Admin routes
Route::prefix('authorized/admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Staff
    Route::get('/create_staff', [StaffController::class, 'create'])->name('admin.create_staff');
    Route::post('/create_staff', [StaffController::class, 'store'])->name('admin.storeStaff');
    Route::get('/staff_list', [StaffController::class, 'index'])->name('admin.staff_list');
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('admin.staff_edit');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('admin.staff_update');

    // Promo
    Route::get('/promo_list', [PromoController::class, 'index'])->name('admin.promo_list');
    Route::get('/create_promo', [PromoController::class, 'create'])->name('admin.create_promo');
    Route::post('/create_promo', [PromoController::class, 'store'])->name('admin.storePromo');
    Route::get('/promo/{id}/edit', [PromoController::class, 'edit'])->name('admin.promo_edit');
    Route::put('/promo/{id}', [PromoController::class, 'update'])->name('admin.promo_update');

    // Vessel
    Route::get('/vessels', [VesselController::class, 'index'])->name('admin.vessel_list');
    Route::get('/vessels/create', [VesselController::class, 'create'])->name('admin.create_vessel');
    Route::post('/vessels/store', [VesselController::class, 'store'])->name('admin.store_vessel');
    Route::get('/vessels/{id}/edit', [VesselController::class, 'edit'])->name('admin.vessel_edit');
    Route::post('/vessels/{id}/update', [VesselController::class, 'update'])->name('admin.vessel_update');

    // Voyage
    Route::get('/voyages', [VoyageController::class, 'index'])->name('admin.voyage_list');
    Route::get('/voyages/create', [VoyageController::class, 'create'])->name('admin.create_voyage');
    Route::post('/voyages/store', [VoyageController::class, 'store'])->name('admin.store_voyage');
    Route::get('/voyages/{id}/edit', [VoyageController::class, 'edit'])->name('admin.voyage_edit');
    Route::put('/voyages/{id}/update', [VoyageController::class, 'update'])->name('admin.voyage_update');

    // Route
    Route::get('/routes', [RouteController::class, 'index'])->name('admin.route_list');
    Route::post('/routes', [RouteController::class, 'store'])->name('admin.route_store');
    Route::put('/routes/{id}', [RouteController::class, 'update'])->name('admin.route_update');
    Route::delete('/routes/{id}', [RouteController::class, 'destroy'])->name('admin.route_destroy');

    // Port
    Route::get('/ports', [PortController::class, 'index'])->name('admin.port_list');
    Route::post('/ports', [PortController::class, 'store'])->name('admin.port_store');
    Route::put('/ports/{id}', [PortController::class, 'update'])->name('admin.port_update');
    Route::delete('/ports/{id}', [PortController::class, 'destroy'])->name('admin.port_destroy');
});

Route::middleware(['auth:admin'])->group(function () {
    Route::resource('routes', RouteController::class);
    Route::resource('ports', PortController::class);
});

// Both admin and staff
Route::middleware(['auth:admin,auth:staff'])->group(function () {
    Route::resource('voyages', VoyageController::class);
});

// Staff dashboard
Route::get('/authorized/staff/dashboard', function () {
    return view('authorized.staff.dashboard');
})->name('staff.dashboard');

Route::post('/authorized/logout', [AuthController::class, 'logout'])->name('logout');
