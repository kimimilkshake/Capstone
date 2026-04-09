<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Restricted</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            background-color: #1a1a1a; /* dark background */
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center; /* vertical center */
            align-items: center;     /* horizontal center */
            text-align: center;
        }

        h1 {
            font-size: 100px; /* as per your change */
            color: #ff4c4c; /* big red */
            margin: 0;
        }

        h3 {
            font-size: 32px;
            margin: 10px 0;
        }

        p {
            font-size: 18px;
            margin: 5px 0;
        }

        #redirect {
            font-size: 14px;
            color: #ccc;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>403 | ACCESS RESTRICTED</h1>
    <h3>You do not have permission to access this page.</h3>
    <p id="redirect">Redirecting you back to home page in <span id="countdown">3</span>...</p>

    <script>
        let seconds = 3;
        const countdown = document.getElementById('countdown');

        const interval = setInterval(() => {
            seconds--;
            countdown.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "<?php echo e(route('homepage')); ?>"; // redirect to homepage
            }
        }, 1000);
    </script>
</body>
</html><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/errors/403.blade.php ENDPATH**/ ?>