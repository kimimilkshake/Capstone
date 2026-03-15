# Booking Issues - Status Report

## Issues Found & Fixed

### Issue 1: ❌ MISSING ACCOMMODATIONS IN PDF

**Root Cause:** The `voyage_id` field was not being saved when creating a booking in BookingController.php

**Location:** `app/Http/Controllers/BookingController.php:95-101`

**Before:**

```php
$bookingId = DB::table('booking')->insertGetId([
    'booking_date' => now(),
    'booking_status' => 'Pending',
    'booking_type' => 'passenger',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

**After (FIXED):**

```php
$bookingId = DB::table('booking')->insertGetId([
    'voyage_id' => $voyage->voyage_id,  // ← ADDED THIS LINE
    'booking_date' => now(),
    'booking_status' => 'Pending',
    'booking_type' => 'passenger',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

**Why This Fixes It:**

- The PDF generation code needs the voyage_id to link to the vessel
- The vessel stores accommodation configurations (Aircon, Economy A, B, C, etc.)
- Without voyage_id, the PDF couldn't find any accommodations to display
- Now PDFs will show the proper accommodation name for each COT number

**Verification:**
✅ Booking #27 created with:

- voyage_id: 1 (now saved!)
- 3 passengers with COTs #201, #202, #203
- Accommodations available: Aircon (141-248), Economy A/B/C
- Email sent with 3 PDFs - should now show accommodations!

---

### Issue 2: ⚠️ NO SUCCESS MESSAGE AFTER PAYMENT

**Root Cause:** Timing issue in PaymentController - page redirects before webhook completes

**Location:** `app/Http/Controllers/PaymentController.php:212-299`

**How It Works:**

1. User pays via PayMongo
2. PayMongo redirects to `/paymongo/return`
3. Code attempts to verify payment status immediately (lines 223-290)
4. If webhook hasn't fired yet, payment status is still 'Pending'
5. It redirects to confirmation page WITHOUT success message (line 298)
6. User sees 5-sec loading as system waits for webhook to process

**Solution Needed:**
The confirmation page should display a message like "Payment Processing..." and poll for status update, OR the success message should be shown even on the confirmation page.

**Current Redirect Logic (lines 293-298):**

```php
if (!empty($status) && in_array($status, ['paid', 'succeeded'])) {
    // Only shows if payment confirmed immediately
    return redirect()->route('homepage')->with('success', "Payment successful!");
}
// Falls back to this if not confirmed yet
return redirect()->to(url('/passenger/confirmbooking/' . $bookingRef));
```

---

## Test Results

✅ **Booking #27 - 3 Passenger Test**

- Booking created with voyage_id: 1 ✓
- 3 passengers added (Jane Smith, John Doe, Maria Garcia) ✓
- 3 tickets with COT #201, #202, #203 ✓
- Accommodations available for matching ✓
- Email sent with 3 PDFs ✓

```
[2026-03-15 23:18:05] PassengerTicketPdf::generate - Generating PDF for booking 27
[2026-03-15 23:18:05] PassengerTicketConfirmed: PDF generated for Jane Smith
[2026-03-15 23:18:05] PassengerTicketPdf::generate - Generating PDF for booking 27
[2026-03-15 23:18:05] PassengerTicketConfirmed: PDF generated for John Doe
[2026-03-15 23:18:06] PassengerTicketPdf::generate - Generating PDF for booking 27
[2026-03-15 23:18:06] PassengerTicketConfirmed: PDF generated for Maria Garcia
[2026-03-15 23:18:28] SendTicketEmail: Successfully sent ticket email for booking 27 to jane.smith@example.com with 3 ticket(s)
```

---

## Files Modified

- ✅ `app/Http/Controllers/BookingController.php` - Added voyage_id to booking creation

## Next Steps

1. Test with a new booking through the UI to confirm accommodations appear
2. Address success message timing issue if needed
