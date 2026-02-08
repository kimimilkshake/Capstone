# Cargo Notification Integration - Summary

## What Was Implemented

I've successfully integrated the notification system with the cargo booking workflow. Now whenever cargo bookings are created, approved, or rejected, notifications will automatically appear in the authHeader notification bell. **Cargo notifications are now clickable** - clicking on a notification will navigate you directly to the cargo booking details page (showcargo.blade.php).

## Changes Made

### 1. **PassengerController.php**
- Added `Notification` model import
- Added notification creation in `confirmCargo()` method when passengers submit new cargo bookings
- Notification message: "New cargo booking #{booking_ref_no} from {sender_name} is pending review"

### 2. **StaffCargoController.php**
- Added `Notification` model import (already present)
- Added notification in `store()` method when staff create cargo bookings
- Added notification in `approve()` method when cargo is approved
  - Message: "Cargo booking #{booking_ref_no} from {sender_name} has been approved"
- Added notification in `reject()` method when cargo is rejected
  - Message: "Cargo booking #{booking_ref_no} from {sender_name} has been rejected"

## How It Works

### When New Cargo is Submitted
1. Passenger or staff submits a cargo booking form
2. The booking is saved with status "Pending"
3. A notification is automatically created with:
   - Type: "cargo booking approval"
   - Status: "approved" (meaning it's an active notification to read)
   - Message includes booking reference number and sender name

### When Cargo is Approved
1. Staff clicks approve on a pending cargo booking
2. Booking status changes to "Confirmed"
3. Cargo items are moved to cargo_receipt table
4. A notification is created confirming the approval

### When Cargo is Rejected
1. Staff clicks reject on a pending cargo booking
2. Booking status changes to "Canceled"
3. A notification is created with status "rejected"

## Viewing Notifications

Staff can view all cargo-related notifications by:
1. Looking at the bell icon in the authHeader - it shows a badge with unread count
2. Clicking the bell icon to see the notification dropdown
3. Each notification shows:
   - An icon (box icon for cargo)
   - The message about the cargo booking
   - Time ago (e.g., "5m ago", "2h ago")
   - Mark as read button
   - Delete/archive button
4. **Clicking on a cargo notification** takes you directly to the cargo booking details page
   - The entire notification is clickable (not just the buttons)
   - Hovering over a clickable notification shows a subtle highlight effect
   - The booking reference is automatically extracted and used for navigation

## Notification Types Created

All cargo notifications use:
- **Type**: `cargo booking approval`
- **Status**: 
  - `approved` for new/pending bookings (shows as unread)
  - `approved` for confirmed bookings (shows as unread)
  - `rejected` for canceled bookings (shows as unread)

## Testing

To test the system:

1. **Create a new cargo booking** (as passenger or staff)
   - Check the bell icon - you should see a notification badge
   - Click the bell - you should see "New cargo booking #X is pending review"

2. **Approve a cargo booking** (as staff)
   - Go to pending cargo page
   - Approve a booking
   - Check notifications - you should see "Cargo booking #X has been approved"

3. **Reject a cargo booking** (as staff)
   - Go to pending cargo page
   - Reject a booking
   - Check notifications - you should see "Cargo booking #X has been rejected"

## Auto-Refresh

The notification system automatically:
- Updates the badge count every 30 seconds
- Refreshes the notification list when you open the dropdown
- No page reload required

## Technical Implementation

### Notification Message Format
Cargo notifications use a special format to enable clickable navigation:
```
"Display message|booking_ref_no"
```

Example:
```php
'notification_message' => "New cargo booking #123 from Juan Cruz is pending review|123"
```

The JavaScript automatically:
1. Splits the message by the pipe (`|`) character
2. Displays only the first part (before the pipe) to the user
3. Uses the second part (after the pipe) as the booking reference for navigation
4. Makes the notification clickable if a booking reference is present

### Navigation Function
When a cargo notification is clicked:
```javascript
window.navigateToCargo = function(bookingRef) {
  window.location.href = `/authorized/staff/cargo-bookings/${bookingRef}`;
};
```

### Styling
Clickable notifications have:
- Pointer cursor on hover
- Subtle highlight effect (light blue background)
- Slight transform animation (moves 2px to the right)
- Box shadow for depth

## Future Enhancements

You can easily extend this system to add notifications for:
- Payment confirmations (already supported)
- Voyage schedule changes
- Ticket status updates
- Cargo loading/unloading status
- And more!

### For Clickable Notifications
Use this pattern with the pipe separator:
```php
use App\Models\Notification;

// Clickable cargo notification
Notification::create([
    'notification_message' => 'Your message here|' . $booking_ref_no,
    'notification_type' => 'cargo booking approval',
    'notification_status' => 'approved',
    'notification_created' => now(),
]);
```

### For Non-Clickable Notifications
Just use the message without the pipe separator:
```php
Notification::create([
    'notification_message' => 'Your message here',
    'notification_type' => 'payment received',
    'notification_status' => 'approved',
    'notification_created' => now(),
]);
```
