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
                                <p class="mb-3">Enter your username and password</p>

                                
                                <?php if(session('success')): ?>
                                    <div class="alert alert-success mx-auto" style="text-align: center;">
                                        <?php echo e(session('success')); ?>

                                    </div>
                                <?php endif; ?>

                                
                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger mx-auto" style="text-align: center;">
                                        <div><?php echo e($errors->first()); ?></div>
                                    </div>
                                <?php endif; ?>

                                
                                <form action="<?php echo e(route('login')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>

                                    <!-- Username -->
                                    <div class="form-floating mb-4">
                                        <input type="text" name="username" id="typeEmailX"
                                            class="form-control form-control-lg" placeholder="Username"
                                            value="<?php echo e(old('username')); ?>" />
                                        <label for="typeEmailX" class="#485B8C">Username <span class="text-danger">*</span></label>
                                    </div>

                                    <!-- Password -->
                                    <div class="form-floating mb-4">
                                        <input type="password" name="password"
                                            id="typePasswordX"class="form-control form-control-lg"
                                            class="form-control form-control-lg text-white bg-dark border-light"
                                            placeholder="Password" />
                                        <label for="typePasswordX" class="#485B8C">Password <span class="text-danger">*</span></label>
                                    </div>

                                    <button class="loginBtn btn btn-lg px-5 mt-1 mb-4" type="submit">
                                        Login
                                    </button>

                                </form>

                                <a href="<?php echo e(route('authorized.forgot_password')); ?>" class="forgot-password">Forgot
                                    Password?</a>

                                <div class="scanner-access mt-4">
                                    <p class="scanner-access-label mb-2">Need the camera scanner?</p>
                                    <a href="<?php echo e(route('scanner.login.form')); ?>" class="scanner-access-link">
                                        Open QR Scanner Login
                                    </a>
                                </div>

                            </div> <!-- mb-md-5 mt-md-4 py-5 -->

                        </div> <!-- card-body -->
                    </div> <!-- card -->
                </div> <!-- col -->
            </div> <!-- row -->
        </div> <!-- container -->
    </section>

    <style>
        .alert {
            text-align: center !important;
        }

        .scanner-access {
            border-top: 1px solid rgba(72, 91, 140, 0.12);
            padding-top: 1rem;
        }

        .scanner-access-label {
            color: #6b7390;
            font-size: 0.92rem;
        }

        .scanner-access-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 220px;
            padding: 0.75rem 1.25rem;
            border: 1px solid rgba(72, 91, 140, 0.2);
            border-radius: 999px;
            color: #485B8C;
            background: rgba(72, 91, 140, 0.05);
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .scanner-access-link:hover {
            background: #485B8C;
            border-color: #485B8C;
            color: #fff;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/login.blade.php ENDPATH**/ ?>