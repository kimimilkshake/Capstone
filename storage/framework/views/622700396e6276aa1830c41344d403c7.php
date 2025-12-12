<?php $__env->startSection('page-title', 'VOYAGES'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  
  <div class="staff-body">
    <h2 class="sms-header">SMS Message for Cancellation of Trips</h2>

    <div class="sms-container">
      <form method="POST" action="<?php echo e(route('staff.semaphore.send')); ?>" id="smsForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="voyage_id" value="<?php echo e($voyage_id); ?>">
        <label for="message" class="sms-label">Message:</label>
        <textarea id="message" name="message" class="sms-textarea" rows="5" required><?php echo e(old('message')); ?></textarea>
        <button type="submit" class="sms-button">Send Message</button>
      </form>
    </div>

    <?php if(session('status')): ?>
      <div class="alert alert-success alert-autodismiss">
        <?php echo e(session('status')); ?>

      </div>
    <?php endif; ?>

    <?php $__errorArgs = ['recipients'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
      <div class="alert alert-danger alert-autodismiss">
        <?php echo e($message); ?>

      </div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
  </div>

  <script>
    document.getElementById('smsForm').addEventListener('submit', function(e) {
      const msg = document.getElementById('message').value.trim();
      
      if (!msg) {
        e.preventDefault();
        alert('Message cannot be empty.');
        return;
      }
      
      if (!confirm('Are you sure you want to send this message to all selected recipients?')) {
        e.preventDefault();
      }
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/semaphore.blade.php ENDPATH**/ ?>