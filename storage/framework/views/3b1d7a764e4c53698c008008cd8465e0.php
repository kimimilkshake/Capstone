
<?php $__env->startSection('content'); ?>
    <section class="vh-100 d-flex align-items-center justify-content-center login-bg"
        style="background-image: url('<?php echo e(asset('images/login-bg.png')); ?>');">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card">
                        <div class="card-body py-0 px-5 text-center">

                            <div class="mb-md-4 mt-md-4 py-0">
                                <img src="<?php echo e(asset('images/lslc_logo_name2.png')); ?>" alt="App Logo" class="img-fluid mb-1"
                                    style="height:150px">
                                <h2 class="fw-bold text-uppercase">WELCOME</h2>
                                <p class="mb-2">Scanner login for staff accounts</p>
                                <p class="mb-3">Enter your username and password</p>

                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger mx-auto" style="text-align: center;">
                                        <div><?php echo e($errors->first()); ?></div>
                                    </div>
                                <?php endif; ?>

                                <form action="<?php echo e(route('scanner.login')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>

                                    <div class="form-floating mb-4">
                                        <input type="text" name="username" id="scannerUsername"
                                            class="form-control form-control-lg" placeholder="Username"
                                            value="<?php echo e(old('username')); ?>" />
                                        <label for="scannerUsername" class="#485B8C">Username</label>
                                    </div>

                                    <div class="form-floating mb-4">
                                        <input type="password" name="password" id="scannerPassword"
                                            class="form-control form-control-lg text-white bg-dark border-light"
                                            placeholder="Password" />
                                        <label for="scannerPassword" class="#485B8C">Password</label>
                                    </div>

                                    <button class="loginBtn btn btn-lg px-5 mt-1 mb-4" type="submit">
                                        Login
                                    </button>
                                </form>

                                <a href="<?php echo e(route('authorized.forgot_password')); ?>" class="forgot-password">Forgot
                                    Password?</a>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .alert {
            text-align: center !important;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/scanner_login.blade.php ENDPATH**/ ?>