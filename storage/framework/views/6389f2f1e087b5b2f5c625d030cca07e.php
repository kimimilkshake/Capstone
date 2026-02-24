<?php $__env->startSection('content'); ?>
  <section class="vh-100 d-flex align-items-center justify-content-center login-bg"
    style="background-image: url('<?php echo e(asset('images/login-bg.png')); ?>');">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card">
            <div class="card-body py-0 px-5 text-center">
              <div class="mb-md-4 mt-md-4 py-0">
                <a href="<?php echo e(route('login')); ?>" class="back-icon">
                  <i class="fa-solid fa-chevron-left"></i>
                </a>
                <img src="<?php echo e(asset('images/lslc_logo_name2.png')); ?>" alt="App Logo" class="img-fluid mb-1" style="height:150px">
                  <h2 class="fw-bold text-uppercase">FORGOT PASSWORD</h2>
                  <div class="form-floating mt-5 mb-4">
                    <input type="text" name="username" id="typeEmailX" class="form-control form-control-lg" placeholder="Username" value="<?php echo e(old('username')); ?>" />
                    <label for="typeEmailX" class="#485B8C">Username</label>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/forgot_password.blade.php ENDPATH**/ ?>