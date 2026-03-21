# Ticket Email System with QR Code Integration

## Overview

The ticket email system automatically sends professional ferry tickets to passengers via email once their payment is confirmed. Each ticket is delivered as a **PDF attachment** with an embedded QR code unique to each passenger. Staff can scan these QR codes at boarding using the built-in QR scanner interface.

---

## How It Works

### 1. Payment Completion Triggers

- **PayMongo Webhook**: When PayMongo sends a payment success webhook
- **Redirect Return**: When users return from the PayMongo payment page
- **Staff Manual Dispatch**: Staff can trigger ticket emails directly from the passenger management panel

### 2. Email Queue System

- Uses Laravel's database queue driver for reliable delivery
- Automatic retries on failure
- Does not block the payment confirmation process

### 3. Email Contents

Each email contains:

- **Company Branding**: Lapulapu Shipping Lines header
- **Booking Reference**: Prominent booking number
- **Voyage Details**: Route, vessel, departure date/time, port
- **Passenger Information**: Cot assignments for each passenger
- **Payment Summary**: Total amount and payment method
- **PDF Attachment**: Individual ticket per passenger with embedded QR code
- **Important Reminders**: Check-in requirements, arrival times, baggage allowance

---

## QR Code Integration (Implemented)

### QR Code Generation

Each passenger receives a unique QR code encoding the string:

```
{booking_ref_no}:{passenger_id}
```

For example: `7:14`

QR images are fetched from the free [QR Server API](https://api.qrserver.com/v1/create-qr-code/) and saved as PNG files on disk.

### QR Code Storage

QR codes are persisted in two places:

| Location       | Details                                                        |
| -------------- | -------------------------------------------------------------- |
| **Filesystem** | `storage/app/qr_codes/pdf_qr_{booking_ref}_{passenger_id}.png` |
| **Database**   | `qr_codes` table — one row per booking/passenger pair          |

The `qr_codes` table schema:

| Column                      | Type        | Description                   |
| --------------------------- | ----------- | ----------------------------- |
| `id`                        | BIGINT (PK) | Auto-increment                |
| `booking_ref_no`            | VARCHAR     | Booking reference (indexed)   |
| `passenger_id`              | BIGINT      | Passenger ID (indexed)        |
| `qr_data`                   | VARCHAR     | Encoded string (e.g. `7:14`)  |
| `qr_code_path`              | VARCHAR     | Absolute path to the PNG file |
| `created_at` / `updated_at` | TIMESTAMP   | Auto-managed                  |

A unique constraint on `(booking_ref_no, passenger_id)` prevents duplicate records.

### PDF Ticket Generation

`app/Services/PassengerTicketPdf.php` orchestrates the per-passenger PDF workflow:

1. Loads the booking with all relationships (voyage, vessel, route, passengers, promos)
2. Calls `QrCodeGenerator::generateAndStore()` for each passenger ticket
3. Reads the saved PNG and encodes it as a base64 data URL (avoids dompdf chroot issues)
4. Renders `resources/views/passenger/passenger_ticket_pdf.blade.php`
5. Converts to PDF via **dompdf** (A4 portrait, 96 DPI)

If a QR code image already exists on disk for the booking/passenger pair, it is reused without re-fetching the API.

### QR Scanner (Staff Interface)

Staff access the QR scanner at `/authorized/scanner` (requires staff login at `/authorized/scannerlogin`).

- **Controller**: `app/Http/Controllers/QrScannerController.php`
- **View**: `resources/views/authorized/staff/qr_scanner.blade.php`
- Uses the browser camera (html5-qrcode library) to decode a passenger's QR ticket on boarding

---

## Key Files

### Email & Queue

| File                                    | Purpose                                                                                           |
| --------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `app/Mail/PassengerTicketConfirmed.php` | Primary mailable — loads booking data via raw DB queries, generates per-passenger PDF attachments |
| `app/Mail/TicketMailable.php`           | Legacy mailable (superseded by `PassengerTicketConfirmed`)                                        |
| `app/Jobs/SendTicketEmail.php`          | Queued job — validates booking/payment status, dispatches to `PassengerTicketConfirmed`           |
| `app/Services/TicketCopyService.php`    | Handles ticket-copy requests (searches by email + route + date, dispatches `SendTicketEmail`)     |

### QR Code

| File                                                              | Purpose                                                                     |
| ----------------------------------------------------------------- | --------------------------------------------------------------------------- |
| `app/Services/QrCodeGenerator.php`                                | Fetches QR PNG from API, saves to disk, persists record in `qr_codes` table |
| `app/Services/PassengerTicketPdf.php`                             | Generates PDF ticket with embedded QR code per passenger                    |
| `app/Models/QrCode.php`                                           | Eloquent model for the `qr_codes` table                                     |
| `database/migrations/2026_03_18_015005_create_qr_codes_table.php` | Migration for the `qr_codes` table                                          |

### Scanner

| File                                                    | Purpose                          |
| ------------------------------------------------------- | -------------------------------- |
| `app/Http/Controllers/QrScannerController.php`          | Serves the staff QR scanner page |
| `resources/views/authorized/staff/qr_scanner.blade.php` | Camera-based QR scanner UI       |

### Email Templates

| File                                                          | Purpose                                   |
| ------------------------------------------------------------- | ----------------------------------------- |
| `resources/views/emails/passenger_ticket_confirmed.blade.php` | HTML body of the confirmation email       |
| `resources/views/passenger/passenger_ticket_pdf.blade.php`    | PDF ticket template (rendered via dompdf) |

---

## Email Flow

```
Payment Completed
    → SendTicketEmail::dispatch(bookingRef, passengerId?)
        → Job validates booking status & payment status
            → PassengerTicketConfirmed mailable constructed
                → Per passenger: QrCodeGenerator::generateAndStore()
                    → QR PNG fetched from API (or reused from disk)
                    → Saved to storage/app/qr_codes/
                    → Record upserted in qr_codes table
                → PassengerTicketPdf::generate() embeds QR as base64 in PDF
                → PDF attached to email as ticket_{FirstName}_{LastName}.pdf
            → Email sent to passenger's email address
```

---

## Configuration

### Mail Settings (`.env`)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@lapulapushipping.com"
MAIL_FROM_NAME="Lapulapu Shipping Lines"
```

### Queue Configuration

- Driver: `database` (configured in `.env` as `QUEUE_CONNECTION=database`)
- Run the worker: `php artisan queue:work`
- Jobs are retried automatically on failure

---

## Testing

### Quick Manual Test

```bash
# Send a ticket email for a confirmed booking
php send_qr_emails_simple.php
```

### Dispatch via Tinker

```bash
php artisan tinker
>>> App\Jobs\SendTicketEmail::dispatch('BOOKING_REF_NO');
>>> exit
```

### Local Development

- Configure Mailpit in `.env` to catch emails locally
- Access Mailpit at http://localhost:8025
- All outgoing emails are captured there for inspection

---

## Monitoring

| Command / Log                 | Purpose                                              |
| ----------------------------- | ---------------------------------------------------- |
| `storage/logs/laravel.log`    | Email sending status, QR generation results          |
| `php artisan queue:failed`    | View failed email jobs                               |
| `php artisan queue:retry all` | Retry all failed jobs                                |
| `qr_codes` database table     | Verify which QR codes have been generated and stored |

---

## Routes

| Method | URI                        | Name                 | Description                       |
| ------ | -------------------------- | -------------------- | --------------------------------- |
| GET    | `/authorized/scannerlogin` | `scanner.login.form` | Staff scanner login page          |
| POST   | `/authorized/scannerlogin` | `scanner.login`      | Staff scanner login handler       |
| GET    | `/authorized/scanner`      | `scanner.page`       | QR scanner interface (staff only) |
