# Gmail SMTP Setup for Ticket Emails

## Quick Setup Instructions

To receive **real ticket emails** at shemcardoza7@gmail.com, follow these steps:

### Step 1: Get Gmail App Password

1. Go to your Google Account settings: https://myaccount.google.com/
2. Click "Security" in the left sidebar
3. Enable "2-Step Verification" if not already enabled
4. Search for "App passwords" or go to: https://myaccount.google.com/apppasswords
5. Select "Mail" as the app and "Windows Computer" as device
6. Click "Generate" - you'll get a 16-character password like: `abcd efgh ijkl mnop`

### Step 2: Update .env File

Open `.env` file and update these lines:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=shemcardoza7@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@lapulapushipping.com"
MAIL_FROM_NAME="Lapulapu Shipping Lines"
```

**Replace `abcd efgh ijkl mnop` with your actual app password (remove spaces)**

### Step 3: Clear Cache & Test

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Start Queue Worker

```bash
php artisan queue:work
```

Keep this terminal open - it processes email jobs.

### Step 5: Test Email

Create a new booking and complete payment, or run:

```bash
php artisan tinker
>>> App\Jobs\SendTicketEmail::dispatch(15);
>>> exit
```

## Alternative: Mailtrap (Easier for Testing)

If you prefer not to use your Gmail:

1. Sign up free at: https://mailtrap.io/
2. Get SMTP credentials from your inbox
3. Update .env with Mailtrap settings
4. All emails will go to Mailtrap inbox (safe for testing)

## What You'll Receive

Once configured, you'll get professional emails with:

-   ✅ Booking confirmation
-   ✅ Ferry ticket details
-   ✅ Voyage information
-   ✅ Passenger details with cot assignments
-   ✅ QR code for check-in
-   ✅ Travel instructions

## Troubleshooting

-   **"Authentication failed"**: Check app password is correct
-   **"Connection refused"**: Verify SMTP settings
-   **No emails**: Ensure queue worker is running
-   **Gmail blocked**: Try enabling "Less secure app access" (not recommended)

The email system is ready - just needs your Gmail app password! 🎫📧
