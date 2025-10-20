<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VesselRouteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\BookingController;


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
// Show the available routes and selection page
Route::get('/passenger/passenger', [VesselRouteController::class, 'index'])->name('passenger');

// Booking page (form)
Route::get('/passenger/passengerbooking', [PassengerController::class, 'index'])->name('passengerbooking');

// Form submission
Route::post('/passenger/store', [PassengerController::class, 'store'])->name('passenger.store');
Route::post('/booking/submit', [BookingController::class, 'store'])->name('booking.submit');

//Confirm booking
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

// Dashboard (protected)
//Route::get('/authorized/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

// Admin Dashboard
Route::get('/authorized/admin/dashboard', function () {
    return view('authorized.admin.dashboard');
})->name('admin.dashboard');

// Staff Dashboard
Route::get('/authorized/staff/dashboard', function () {
    return view('authorized.staff.dashboard');
})->name('staff.dashboard');


Route::post('/authorized/logout', [AuthController::class, 'logout'])->name('logout');


