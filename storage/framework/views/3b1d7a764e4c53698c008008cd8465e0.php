
<?php $__env->startSection('content'); ?>
    <section class="vh-100 d-flex align-items-center justify-content-center login-bg scanner-login-shell"
        style="background-image: url('<?php echo e(asset('images/login-bg.png')); ?>');">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-9 col-lg-7 col-xl-5">
                    <div class="card scanner-login-card shadow-lg border-0">
                        <div class="card-body p-0 text-center">
                            <div class="scanner-login-top px-4 px-md-5 pt-4 pb-3">
                                <div class="text-start mb-3">
                                    
                                </div>

                                

                              

                                <p class="scanner-eyebrow mb-2">Staff Access Only</p>
                                <h2 class="fw-bold text-uppercase mb-2">QR Scanner Portal</h2>
                                <p class="scanner-subtitle mb-0">Sign in with an active staff account to open the camera-based scanner.</p>
                            </div>

                            <div class="px-4 px-md-5 pt-4 pb-4">
                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger mx-auto" style="text-align: center;">
                                        <div><?php echo e($errors->first()); ?></div>
                                    </div>
                                <?php endif; ?>

                                <div class="scanner-note mb-4">
                                    <i class="fa-solid fa-camera me-2"></i>
                                    Camera access will be requested after login.
                                </div>

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

                                    <button class="loginBtn btn btn-lg px-5 mt-1 mb-3 scanner-login-btn" type="submit">
                                        Open Scanner
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

        .scanner-login-shell {
            position: relative;
        }

        .scanner-login-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(72, 91, 140, 0.18), rgba(255, 255, 255, 0.04));
        }

        .scanner-login-card {
            position: relative;
            overflow: hidden;
        }

        .scanner-login-top {
            background: linear-gradient(180deg, rgba(72, 91, 140, 0.10), rgba(72, 91, 140, 0));
            border-bottom: 1px solid rgba(72, 91, 140, 0.14);
        }

        .scanner-badge {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #485B8C, #6075A8);
            color: #fff;
            font-size: 1.85rem;
            box-shadow: 0 16px 35px rgba(72, 91, 140, 0.25);
        }

        .scanner-eyebrow {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.22rem;
            text-transform: uppercase;
            color: #6C7BA2;
        }

        .scanner-subtitle {
            max-width: 350px;
            margin: 0 auto;
            color: #66738F;
        }

        .scanner-note {
            border: 1px dashed rgba(72, 91, 140, 0.28);
            background: rgba(72, 91, 140, 0.05);
            border-radius: 14px;
            padding: 0.9rem 1rem;
            color: #5C6D96;
            font-size: 0.95rem;
        }

        .scanner-back-link {
            color: #485B8C;
            text-decoration: none;
            font-weight: 600;
        }

        .scanner-back-link:hover {
            color: #31456F;
        }

        .scanner-login-btn {
            min-width: 220px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/scanner_login.blade.php ENDPATH**/ ?>