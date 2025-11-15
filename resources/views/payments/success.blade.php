<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Successful</title>
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
            color: #2b8a3e;
            margin-bottom: 8px
        }

        p {
            margin: 0 0 16px
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #2b8a3e;
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
        <h1>Booking confirmed ✅</h1>
        <p>Your booking (ref: <strong>{{ $bookingRef }}</strong>) has been successfully paid and confirmed.</p>
        <p class="small">You'll be redirected to the homepage in a few seconds.</p>
        <p><a class="btn" href="{{ url('/') }}">Go to homepage now</a></p>
    </div>

    <script>
        // Auto-redirect to homepage after 4 seconds
        setTimeout(function() {
            window.location.href = '{{ url('/') }}';
        }, 4000);
    </script>
</body>

</html>
