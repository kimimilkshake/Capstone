# 🎫 Request Ticket Copy - Individual Passenger Feature

## Overview

The "Request Ticket Copy" feature now supports **individual passenger selection** for multi-passenger bookings. Previously, it sent ALL passengers' PDFs to the first passenger. Now each individual passenger can request ONLY their personal copy.

---

## How It Works

### **User Flow:**

```
Step 1: Passenger enters email + departure date
   ↓
Step 2: System searches for matching tickets
   ↓
Step 3A: Single match found?
   └─→ ✅ Auto-send that passenger's ticket

Step 3B: Multiple matches found?
   └─→ 📋 Show list of passengers to select from
       └─→ User clicks their name
           └─→ ✅ Send ONLY their individual ticket

Step 4: Email sent to passenger's own email with:
   • ONLY their personal ticket PDF
   • Booking details
   • Voyage information
```

---

## Technical Implementation

### **Files Modified:**

#### 1. **BookingController.php** - `requestTicketCopy()` method

**Changes:**

- Accept optional `passenger_id` parameter
- First request (email + departure_date only):
    - Find ALL matching passengers
    - If 1 passenger: auto-send their ticket
    - If multiple: return list for selection
- Second request (email + departure_date + passenger_id):
    - Find most recent booking for that passenger
    - Send only that passenger's ticket

**Key Logic:**

```php
// Find all matching tickets
$matchingTickets = DB::query...
    ->where('p.passenger_email', $email)
    ->whereDate('v.voyage_departure_date', $departureDate)
    ->orderByDesc('b.created_at')  // Most recent first
    ->get();

// If no passenger_id -> return options
if (!$passengerId) {
    if ($passengers->count() === 1) {
        // Auto-send
        SendTicketEmail::dispatch($selectedPassenger['booking_ref_no'], $passengerId);
    } else {
        // Return passenger list
        return response()->json(['pending_selection' => true, 'passengers' => $passengers]);
    }
}

// If passenger_id specified -> send individual copy
SendTicketEmail::dispatch($selectedTicket->booking_ref_no, $passengerId);
```

#### 2. **SendTicketEmail.php** - Email job

**Changes:**

- Added `passengerId` parameter (optional)
- If `passengerId` is provided: send only that passenger's ticket
- If not provided: use original behavior (send all passengers)

**Constructor:**

```php
public function __construct($bookingRef, $passengerId = null, $recipientEmail = null)
{
    $this->bookingRef = $bookingRef;
    $this->passengerId = $passengerId;
    $this->recipientEmail = $recipientEmail;
}
```

#### 3. **PassengerTicketConfirmed.php** - Email template

**Changes:**

- Added `filterPassengerEmail` parameter (optional)
- If provided: only include that specific passenger in email
- PDF attachments are filtered to only that passenger's PDF

**Constructor:**

```php
public function __construct($bookingRef, $filterPassengerEmail = null)
{
    $this->bookingRef = $bookingRef;
    $this->filterPassengerEmail = $filterPassengerEmail;
    $this->loadBookingData();
}
```

---

## API Response Examples

### **Scenario 1: Single Passenger Match**

```json
{
    "success": true,
    "message": "Ticket copy for John Doe has been sent to john@example.com",
    "direct_send": true
}
```

### **Scenario 2: Multiple Passengers Match - Show Selection**

```json
{
    "success": true,
    "message": "Multiple passengers found. Please select which passenger you are:",
    "pending_selection": true,
    "passengers": [
        {
            "passenger_id": 101,
            "name": "John Doe",
            "type": "Regular",
            "booking_ref_no": 26
        },
        {
            "passenger_id": 102,
            "name": "Jane Smith",
            "type": "Regular",
            "booking_ref_no": 26
        }
    ]
}
```

### **Scenario 3: Passenger Selected - Individual Ticket Sent**

```json
{
    "success": true,
    "message": "Personal ticket copy for John Doe has been sent to john@example.com."
}
```

---

## Key Features

✅ **Individual Passenger Selection**

- Each passenger can request their own copy only
- No confusion with other passengers' tickets

✅ **Auto-Detection**

- If only one match: sends immediately
- If multiple matches: asks passenger to confirm identity

✅ **Smart Duplicate Handling**

- If same passenger booked multiple times on same date
- System uses: `ORDER BY booking.created_at DESC`
- Result: **MOST RECENT booking** is used

✅ **Email Matching**

- Searches by: email + departure_date
- Returns: all matching passengers
- Filters results to unique passengers

✅ **Backward Compatibility**

- Old code paths still work
- No `passenger_id` = sends all passengers (original behavior)
- `passenger_id` specified = sends only that passenger (new behavior)

---

## Database Queries

### **Find All Matching Passengers**

```sql
SELECT
    pt.passenger_id,
    p.passenger_firstname,
    p.passenger_lastname,
    p.passenger_type,
    pt.booking_ref_no,
    b.created_at
FROM passenger_ticket pt
JOIN passenger p ON pt.passenger_id = p.passenger_id
JOIN voyage v ON pt.voyage_id = v.voyage_id
JOIN booking b ON pt.booking_ref_no = b.booking_ref_no
WHERE p.passenger_email = ?
    AND DATE(v.voyage_departure_date) = ?
    AND b.booking_status = 'Confirmed'
    AND b.payment_status = 'Completed'
ORDER BY b.created_at DESC
```

---

## Use Cases

### **Case 1: Family Booking**

- 3 family members book together
- Mom (recipient) booked for: herself, daughter, son
- Now:
    - **Before**: Son contacts support asking for his ticket
    - **After**: Son enters his email + departure date → selects his name → gets his ticket

### **Case 2: Group Booking**

- 5 coworkers on same flight
- Email: sharedcompany@email.com (all registered with same email)
- Now: Each coworker can enter email + date → select their name → get personal copy

### **Case 3: Duplicate Bookings**

- Passenger booked same flight twice (mistake/rebooking)
- System: Uses most recent booking automatically
- Result: Gets the latest ticket version

---

## Testing

Use `test_individual_ticket_copy.php` to verify:

```bash
php test_individual_ticket_copy.php
```

This shows:

- ✅ Multi-passenger booking detection
- ✅ Passenger list generation
- ✅ Auto-send logic
- ✅ Manual selection workflow
- ✅ Most recent booking logic

---

## Benefits

| Benefit            | Before                               | After                   |
| ------------------ | ------------------------------------ | ----------------------- |
| **Personal Copy**  | All passengers' PDFs                 | Only YOUR ticket        |
| **Email Clarity**  | Confusion on who to send             | Each person's own email |
| **Group Bookings** | Not supported                        | Fully supported         |
| **Duplicates**     | Would send both                      | Uses most recent        |
| **Security**       | Anyone could request anyone's ticket | Must match email + date |
