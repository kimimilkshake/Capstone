<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Failed</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f9fb;
            color: #222;
        }

        .container {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            text-align: center
        }

        h1 {
            color: #b02a37;
            margin-bottom: 8px
        }

        p {
            margin: 0 0 16px
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #0d6efd;
            color: #fff;
            border-radius: 6px;
            text-decoration: none
        }

        .small {
            color: #666;
            font-size: 0.9rem
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Payment failed or canceled ❌</h1>
        <p>For booking ref: <strong>{{ $bookingRef }}</strong>.</p>
        <p class="small">You can try again or return to your booking confirmation page.</p>
        <p>
            <a class="btn" href="{{ url('/passenger/confirmbooking/' . $bookingRef) }}">Back to confirmation</a>
            &nbsp;
            <a class="btn" href="{{ url('/') }}" style="background:#2b8a3e">Go to homepage</a>
        </p>
    </div>
</body>

</html>
