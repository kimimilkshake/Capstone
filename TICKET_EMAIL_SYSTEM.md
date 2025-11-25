# Ticket Email System

## Overview

The ticket email system automatically sends professional ferry tickets to passengers via email once their payment is confirmed. This feature mimics modern booking platforms where customers receive digital tickets immediately after payment.

## How It Works

### 1. Payment Completion Triggers

-   **PayMongo Webhook**: When PayMongo sends payment success webhooks
-   **Redirect Return**: When users return from PayMongo payment page
-   **Status Updates**: Any time payment status becomes "Completed"

### 2. Email Queue System

-   Uses Laravel's queue system for reliable delivery
-   Automatic retries on failures
-   Prevents blocking the payment confirmation process

### 3. Email Contents

The ticket email includes:

-   **Company Branding**: Lapulapu Shipping Lines header
-   **Booking Reference**: Large, prominent booking number
-   **Voyage Details**: Route, vessel, departure date/time, port
-   **Passenger Information**: All passengers with cot assignments
-   **Payment Summary**: Total amount and payment method
-   **QR Code Placeholder**: For future scanning integration
-   **Important Reminders**: Check-in requirements, arrival times, baggage allowance

## Files Created

### 1. `app/Mail/TicketMailable.php`

-   Main email class extending Laravel's Mailable
-   Loads all booking data (booking, payment, passengers, voyage, vessel, route)
-   Generates subject line with route information
-   Passes data to email template

### 2. `app/Jobs/SendTicketEmail.php`

-   Queued job for sending emails
-   Handles errors and retries automatically
-   Validates booking/payment status before sending
-   Logs success/failure for monitoring

### 3. `resources/views/emails/ticket.blade.php`

-   Professional HTML email template
-   Mobile-responsive design
-   Structured layout with clear sections
-   Company branding and styling
-   Includes all necessary ticket information

### 4. Modified `app/Http/Controllers/PaymentController.php`

-   Added `SendTicketEmail::dispatch()` calls at all payment completion points
-   Webhook handler, charge completion, and redirect return
-   Automatic email sending without manual intervention

## Configuration

### Mail Settings (`.env`)

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@lapulapushipping.com"
MAIL_FROM_NAME="Lapulapu Shipping Lines"
```

### Queue Configuration

-   Uses database queue driver
-   Requires `php artisan queue:work` to be running
-   Jobs are retried automatically on failure

## Testing

### Manual Testing

1. Create a booking with valid passenger email
2. Complete payment via PayMongo
3. Check `storage/logs/laravel.log` for email status
4. Run `php artisan queue:work` to process emails

### Local Development

-   Uses Mailpit (configured in .env) to catch emails locally
-   Access Mailpit web interface at http://localhost:8025
-   All emails are captured for testing

## Email Flow

```
Payment Completed → SendTicketEmail Job Queued → Job Processed → Email Sent → Passenger Receives Ticket
```

## Features

### Professional Ticket Design

-   Clean, modern layout
-   Company branding
-   Clear information hierarchy
-   Print-friendly styling

### Comprehensive Information

-   All passenger details with cot assignments
-   Complete voyage information
-   Payment confirmation
-   Booking reference for check-in

### Reliability

-   Queue-based sending prevents payment delays
-   Automatic retries on failures
-   Comprehensive error logging

### Mobile Responsive

-   Works on all devices
-   Optimized for mobile viewing
-   Consistent experience across email clients

## Future Enhancements

1. **QR Code Integration**: Generate actual QR codes for booking references
2. **PDF Attachments**: Include PDF tickets as attachments
3. **Email Templates**: Multiple templates for different routes/vessels
4. **Notification System**: SMS notifications in addition to emails
5. **Email Tracking**: Read receipts and delivery confirmation

## Monitoring

Check these logs for email system health:

-   `storage/logs/laravel.log` - Email sending status
-   Queue job status via `php artisan queue:failed`
-   Failed job retry via `php artisan queue:retry all`

The system is now ready to automatically send professional ticket emails to passengers upon payment completion!
