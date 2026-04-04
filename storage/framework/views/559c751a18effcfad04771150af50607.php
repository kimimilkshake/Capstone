<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- ICON IMAGE -->
    <link rel="icon" href="<?php echo e(asset('images/lslc_logo2.png')); ?>?v=<?php echo e(time()); ?>" type="image/png">

    <!-- Bootstrap CSS is bundled via Vite (app.scss) — CDN copy removed -->

    <!-- Bootstrap Icons — deferred -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    </noscript>

    <!-- MDB CSS — deferred -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.css" rel="stylesheet">
    </noscript>

    <!-- Font Awesome — deferred -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </noscript>

    <!-- SweetAlert2 — deferred -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    </noscript>

    <title>LSIS</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/sass/app.scss', 'resources/js/app.js']); ?>
</head>

<body>

    <!-- ICON IMAGE -->
    <link rel="icon" href="<?php echo e(asset('images/lslc_logo2.png')); ?>?v=<?php echo e(time()); ?>" type="image/png">


    <!-- jQuery (optional if you still need it) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 JS is bundled via Vite (app.js) — CDN copy removed -->

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toast Include -->
    <?php echo $__env->make('components.toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div id=id>

        <main>
            <?php echo $__env->yieldContent('content'); ?>
        </main>

    </div>
</body>

</html>
<?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/layouts/app.blade.php ENDPATH**/ ?>