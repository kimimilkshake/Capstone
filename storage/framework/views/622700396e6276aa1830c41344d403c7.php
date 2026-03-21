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

        <div class="sms-template-row">
          <span class="sms-template-label">Quick Templates:</span>
          <button type="button" class="sms-template-button" data-template="Dear Valued Passengers and Cargo Senders,\n\nDue to the impending arrival of Typhoon [Name] and the corresponding safety advisories issued by local authorities, we regret to inform you that the voyage from <?php echo e(optional($voyage->routePort)->route_origin ?? '-'); ?> to <?php echo e(optional($voyage->routePort)->route_destination ?? '-'); ?> scheduled for <?php echo e($voyage->voyage_departure_date); ?> at <?php echo e($voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-'); ?> has been cancelled in the interest of ensuring the safety and well-being of our passengers, cargo, and staff." title="Typhoon Template"><i class="fas fa-cloud-showers-heavy"></i></button>
          <button type="button" class="sms-template-button" data-template="Dear Valued Passengers and Cargo Senders,\n\nDue to an unexpected technical issue affecting our vessel, we regret to inform you that the voyage from <?php echo e(optional($voyage->routePort)->route_origin ?? '-'); ?> to <?php echo e(optional($voyage->routePort)->route_destination ?? '-'); ?> scheduled for <?php echo e($voyage->voyage_departure_date); ?> at <?php echo e($voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-'); ?> has been cancelled until the issue is fully resolved. We sincerely apologize for any inconvenience caused and will provide updates as they become available." title="Technical Template"><i class="fas fa-wrench"></i></button>
        </div>

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
    function setTemplateMessage(text) {
      const textarea = document.getElementById('message');
      const normalized = text.replace(/\\n/g, '\n');
      textarea.value = normalized;
      textarea.focus();
    }

    document.querySelectorAll('.sms-template-button[data-template]').forEach(button => {
      button.addEventListener('click', () => {
        setTemplateMessage(button.dataset.template);
      });
    });

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