<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\VesselRouteController;
use App\Http\Controllers\VoyageBookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContactController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\VesselController;
use App\Http\Controllers\Admin\RoutePortController;
use App\Http\Controllers\NotificationController;

//STAFF CONTROLLERS
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\StaffCargoController;
use App\Http\Controllers\Staff\StaffPassengerController;
use App\Http\Controllers\Staff\SemaphoreController;

// SHARED CONTROLLERS
use App\Http\Controllers\VoyageController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\CargoItemController;
use App\Http\Controllers\CargoAutoPlacementController;

// OCR route
Route::post('/ocr/parse', [OcrController::class, 'parseImage'])->name('ocr.parse');

// Notification API routes
Route::prefix('api/notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/clear-all', [NotificationController::class, 'clearAll']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::post('/create', [NotificationController::class, 'store']);
});

Route::get('/', function () {
    return view('passenger.homepage');
});

Route::get('/passenger/homepage', function () {
    return view('passenger.homepage');
})->name('homepage');

// Passenger booking route - show available voyages within 8 days
Route::get('/passenger/bookingtype', [VoyageBookingController::class, 'index'])->name('bookingtype');
// Passenger Booking Page
Route::get('/passenger/passengerbooking', [PassengerController::class, 'index'])->name('passengerbooking');

// Cargo Booking Page
Route::get('/passenger/cargobooking', [PassengerController::class, 'index'])->name('cargobooking');
// Cargo booking success page
//Route::get('/passenger/cargobooking/success', function () {
// return view('passenger.cargo_success');
//})->name('cargobooking.success');
// Cargo Form Submission
//Route::post('/passenger/cargobooking/store', [PassengerController::class, 'storeCargo'])->name('cargobooking.store');
// Step 1: Submit cargo booking form → POST → shows confirmation page
Route::post('/passenger/cargobooking/confirm', [PassengerController::class, 'confirmCargo'])->name('cargobooking.confirm');
// Step 2: Display the confirmation page → GET
Route::get('/passenger/cargobooking/confirm/{booking_ref_no}', [PassengerController::class, 'showCargoConfirmation'])->name('cargobooking.show');
// Cancel booking → POST
Route::post('/passenger/cargobooking/cancel/{booking_ref_no}', [PassengerController::class, 'cancelCargo'])->name('cargobooking.cancel');
// Finalize booking → POST
Route::post('/passenger/cargobooking/finalize/{booking_ref_no}', [PassengerController::class, 'finalizeCargo'])->name('cargobooking.finalize');
Route::get('/passenger/cargobooking/success', function () {
    return view('passenger.cargo_success');
})->name('cargobooking.success');



// Passenger Form Submission
Route::post('/passenger/store', [PassengerController::class, 'store'])->name('passenger.store');
// Form submission
Route::post('/booking/submit', [BookingController::class, 'store'])->name('booking.submit');
Route::post('/booking/cancel/{booking_ref_no}', [BookingController::class, 'cancel'])->name('booking.cancel');
// API: return unavailable cot numbers for a voyage (by route/date or voyage_id)
Route::get('/voyage/unavailable-cots', [BookingController::class, 'unavailableCots'])->name('voyage.unavailable_cots');
// API: return available cots per accommodation for a voyage
Route::get('/voyage/available-cots-by-accommodation', [BookingController::class, 'getAvailableCotsByAccommodation'])->name('voyage.available_cots_by_accommodation');
// Confirm booking (show booking by reference)
Route::get('/passenger/confirmbooking/{booking_ref_no}', [BookingController::class, 'confirm'])->name('passenger.confirmbooking');
// Backwards-compatible route (no ref) - shows generic page
Route::get('/passenger/confirmbooking', function () {
    return view('passenger.confirmbooking');
});

// PayMongo endpoints
Route::post('/paymongo/create-source', [PaymentController::class, 'createSource'])->name('paymongo.create_source');
Route::post('/paymongo/webhook', [PaymentController::class, 'webhook'])->name('paymongo.webhook')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
Route::get('/paymongo/return', [PaymentController::class, 'redirectReturn'])->name('paymongo.return');
Route::get('/paymongo/failed', function (Request $request) {
    $bookingRef = $request->query('booking_ref_no');
    return view('payments.failed', ['bookingRef' => $bookingRef]);
})->name('paymongo.failed');

Route::get('/passenger/schedules', function () {
    return view('passenger.schedules');
})->name('schedules');

//About Us Page
Route::get('/passenger/about', function () {
    return view('passenger.about');
})->name('about');

Route::prefix('passenger/about_partials')->group(function () {
    Route::get('/', function () {
        return view('passenger.about_partials');
    });
    Route::get('/{fragment}', function ($fragment) {
        $valid = ['who_we_are', 'what_we_offer', 'vision_mission', 'vessels_about', 'ports_of_call'];
        if (!in_array($fragment, $valid))
            abort(404);
        return view("passenger.about_partials.$fragment");
    });
});

//FAQs Page
Route::get('/passenger/faqs', function () {
    return view('passenger.faqs');
})->name('faqs');

//Contact Us Page
Route::get('/passenger/contact', function () {
    return view('passenger.contact');
})->name('contact');
Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send');

// Show login page
Route::get('/authorized/login', [AuthController::class, 'showLoginForm'])->name('login.form');

// Process login
Route::post('/authorized/login', [AuthController::class, 'login'])->name('login');

// Forgot password
Route::get('/authorized/forgot_password', function () {
    return view('authorized.forgot_password');
})->name('authorized.forgot_password');

//ADMIN ROUTES
Route::prefix('authorized/admin')->middleware('auth:admin')->group(function () {

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

    //Route and Port
    Route::get('/route_port', [RoutePortController::class, 'index'])->name('admin.route_port_list');
    Route::post('/route_port', [RoutePortController::class, 'store'])->name('admin.route_port_store');
    Route::put('/route_port/{id}', [RoutePortController::class, 'update'])->name('admin.route_port_update');
    Route::delete('/route_port/{id}', [RoutePortController::class, 'destroy'])->name('admin.route_port_destroy');

    // Cargo Items
    Route::get('/cargo_items', [CargoItemController::class, 'index'])->name('admin.cargo_item_list');
    Route::get('/cargo_items/create', [CargoItemController::class, 'create'])->name('admin.create_cargo_item');
    Route::post('/cargo_items/store', [CargoItemController::class, 'store'])->name('admin.store_cargo_item');
    Route::get('/cargo_items/{id}/edit', [CargoItemController::class, 'edit'])->name('admin.cargo_item_edit');
    Route::put('/cargo_items/{id}/update', [CargoItemController::class, 'update'])->name('admin.cargo_item_update');
    Route::delete('/cargo_items/{id}/delete', [CargoItemController::class, 'destroy'])->name('admin.cargo_item_delete');

    //Manifest
    Route::get('/adminmanifest/{voyage}', [ManifestController::class, 'show'])->name('admin.manifest');

    // Cargo Auto Placement

    Route::post('/admin_cargo/place', [CargoAutoPlacementController::class, 'place'])
        ->name('admin.cargo.place');

    Route::get('/admin_cargo/placement', [CargoAutoPlacementController::class, 'show'])
        ->name('admin.cargo.placement');

    Route::post('/admin_cargo/place', [CargoAutoPlacementController::class, 'place'])
        ->name('admin.cargo.place');

    Route::post('/admin_cargo/placement/add-row', [CargoAutoPlacementController::class, 'addRow'])
        ->name('admin.cargo.addRow');

    Route::post('/admin_cargo/placement/remove-row', [CargoAutoPlacementController::class, 'removeRow'])
        ->name('admin.cargo.removeRow');
});





//STAFF
Route::prefix('authorized/staff')->middleware('auth:staff')->group(function () {

    //Dashboard
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])
        ->name('staff.dashboard');

    //Voyages
    Route::get('/voyages', [VoyageController::class, 'index'])->name('staff.voyage_list');
    Route::get('/voyages/create', [VoyageController::class, 'create'])->name('staff.create_voyage');
    Route::post('/voyages/store', [VoyageController::class, 'store'])->name('staff.store_voyage');
    Route::get('/voyages/{id}/edit', [VoyageController::class, 'edit'])->name('staff.voyage_edit');
    Route::put('/voyages/{id}/update', [VoyageController::class, 'update'])->name('staff.voyage_update');

    // Cargo Items
    Route::get('/cargo_items', [CargoItemController::class, 'index'])->name('staff.cargo_item_list');
    Route::get('/cargo_items/create', [CargoItemController::class, 'create'])->name('staff.create_cargo_item');
    Route::post('/cargo_items/store', [CargoItemController::class, 'store'])->name('staff.store_cargo_item');
    Route::get('/cargo_items/{id}/edit', [CargoItemController::class, 'edit'])->name('staff.cargo_item_edit');
    Route::put('/cargo_items/{id}/update', [CargoItemController::class, 'update'])->name('staff.cargo_item_update');
    Route::delete('/cargo_items/{id}/delete', [CargoItemController::class, 'destroy'])->name('staff.cargo_item_delete');

    //Cargo Booking
    // Show cargo booking form
    Route::get('/cargo-bookings/create', [StaffCargoController::class, 'create'])->name('staff.cargo_booking.create');
    // Save new cargo booking
    Route::post('/cargo-bookings/store', [StaffCargoController::class, 'store'])->name('cargo.bookings.store');
    // Show only pending cargo bookings
    Route::get('/cargo-bookings/pending', [StaffCargoController::class, 'pending'])->name('cargo.bookings.pending');

    //Passenger Booking (Staff)
    // Show passenger booking form for staff
    Route::get('/passenger-booking/create', [StaffPassengerController::class, 'create'])->name('staff.passenger_booking.create');
    // Save new passenger booking (creates reservation)
    Route::post('/passenger-booking/store', [StaffPassengerController::class, 'store'])->name('staff.passenger_booking.store');
    // Show reservation page
    Route::get('/passenger-booking/reservation/{bookingRef}', [StaffPassengerController::class, 'showReservation'])->name('staff.passenger_booking.reservation');
    // AJAX endpoint for available cots
    Route::get('/passenger-booking/available-cots', [StaffPassengerController::class, 'getAvailableCots'])->name('staff.passenger_booking.available_cots');
    // Cancel reservation
    Route::post('/passenger-booking/{bookingRef}/cancel', [StaffPassengerController::class, 'cancelReservation'])->name('staff.passenger_booking.cancel');
    // Complete booking with Cash
    Route::post('/passenger-booking/{bookingRef}/complete-cash', [StaffPassengerController::class, 'completeCash'])->name('staff.passenger_booking.complete_cash');
    // Complete booking with GCash
    Route::post('/passenger-booking/{bookingRef}/complete-gcash', [StaffPassengerController::class, 'completeGcash'])->name('staff.passenger_booking.complete_gcash');

    // View booking details
    Route::get('/cargo-bookings/{id}', [StaffCargoController::class, 'show'])->name('cargo.bookings.show');
    Route::get('/cargo-items/voyage/{id}', [StaffCargoController::class, 'getCargoItemsByVoyage']);
    // Approve booking
    Route::post('/cargo-bookings/{id}/approve', [StaffCargoController::class, 'approve'])->name('cargo.bookings.approve');
    Route::post('/cargo-bookings/{id}/reject', [StaffCargoController::class, 'reject'])->name('cargo.bookings.reject');


    // Edit cargo items
    Route::get('/cargo-bookings/{id}/edit', [StaffCargoController::class, 'edit'])
        ->name('cargo.bookings.edit');

    // Update cargo items
    Route::put('/cargo-bookings/{id}/update', [StaffCargoController::class, 'update'])
        ->name('cargo.bookings.update');

    // Semaphore Text SMS
    Route::get('/semaphore/{voyage}', [SemaphoreController::class, 'show'])->name('staff.semaphore');
    Route::post('/semaphore/send', [SemaphoreController::class, 'send'])->name('staff.semaphore.send')->middleware('auth');

    //MANIFEST
    Route::get('/staffmanifest/{voyage}', [ManifestController::class, 'show'])->name('staff.manifest');

    // Cargo Auto Placement

    Route::post('/staff_cargo/place', [CargoAutoPlacementController::class, 'place'])
        ->name('staff.cargo.place');

    Route::get('/staff_cargo/placement', [CargoAutoPlacementController::class, 'show'])
        ->name('staff.cargo.placement');

    Route::post('/staff_cargo/place', [CargoAutoPlacementController::class, 'place'])
        ->name('staff.cargo.place');

    Route::post('/staff_cargo/placement/add-row', [CargoAutoPlacementController::class, 'addRow'])
        ->name('staff.cargo.addRow');

    Route::post('/staff_cargo/placement/remove-row', [CargoAutoPlacementController::class, 'removeRow'])
        ->name('staff.cargo.removeRow');
});

Route::post('/authorized/logout', [AuthController::class, 'logout'])->name('logout');

// CARGO BOOKING
Route::get('/staff/cargobooking', function () {
    return view('authorized.staff.cargobooking');
});

/*
Route::prefix('authorized/staff')->group(function () {
    Route::get('/dashboard', fn() => view('authorized.staff.dashboard'))->name('staff.dashboard');

    // Shared voyage access for staff
    Route::get('/voyages', [VoyageController::class, 'index'])->name('staff.voyage_list');
    Route::get('/voyages/create', [VoyageController::class, 'create'])->name('staff.create_voyage');
    Route::post('/voyages/store', [VoyageController::class, 'store'])->name('staff.store_voyage');
    Route::get('/voyages/{id}/edit', [VoyageController::class, 'edit'])->name('staff.voyage_edit');
    Route::post('/voyages/{id}/update', [VoyageController::class, 'update'])->name('staff.voyage_update');
});
*/
