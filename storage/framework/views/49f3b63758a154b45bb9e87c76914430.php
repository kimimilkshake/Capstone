<?php $__env->startSection('page-title', 'CANCEL VOYAGE SMS'); ?>
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
          <button type="button" class="sms-template-button template-typhoon" data-template="Dear Valued Passengers and Cargo Senders,\n\nDue to the impending arrival of Typhoon [Name] and the corresponding safety advisories issued by local authorities, we regret to inform you that the voyage from <?php echo e(optional($voyage->routePort)->route_origin ?? '-'); ?> to <?php echo e(optional($voyage->routePort)->route_destination ?? '-'); ?> scheduled for <?php echo e($voyage->voyage_departure_date); ?> at <?php echo e($voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-'); ?> has been cancelled in the interest of ensuring the safety and well-being of our passengers, cargo, and staff." title="Typhoon Template" aria-label="Use typhoon template"><i class="fas fa-cloud-showers-heavy"></i></button>
          <button type="button" class="sms-template-button template-technical" data-template="Dear Valued Passengers and Cargo Senders,\n\nDue to an unexpected technical issue affecting our vessel, we regret to inform you that the voyage from <?php echo e(optional($voyage->routePort)->route_origin ?? '-'); ?> to <?php echo e(optional($voyage->routePort)->route_destination ?? '-'); ?> scheduled for <?php echo e($voyage->voyage_departure_date); ?> at <?php echo e($voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-'); ?> has been cancelled until the issue is fully resolved. We sincerely apologize for any inconvenience caused and will provide updates as they become available." title="Technical Template" aria-label="Use technical issue template"><i class="fas fa-wrench"></i></button>
        </div>

        <label for="message" class="sms-label">Message:</label>
        <textarea id="message" name="message" class="sms-textarea" rows="5" required><?php echo e(old('message')); ?></textarea>
        <button type="submit" class="sms-button">Send Message</button>
      </form>

      <div id="smsConfirmModal" class="confirm-dialog-overlay" aria-hidden="true">
        <div class="confirm-dialog-panel" role="dialog" aria-modal="true" aria-labelledby="smsConfirmModalLabel">
          <div class="confirm-dialog-header">
            <h5 class="confirm-dialog-title" id="smsConfirmModalLabel">Confirm Message Send</h5>
            <button type="button" class="confirm-dialog-close" id="closeSmsConfirmModal" aria-label="Close">&times;</button>
          </div>
          <div class="confirm-dialog-body">
            Are you sure you want to send this message to all selected recipients?
          </div>
          <div class="confirm-dialog-footer">
            <button type="button" class="btn" id="cancelSmsSendButton" style="background: #dc3545; color: #ffffff;">Cancel</button>
            <button type="button" class="btn" id="confirmSmsSendButton" style="background: #1E2541; color: #ffffff;">Send Message</button>
          </div>
        </div>
      </div>

      <div id="smsLoadingOverlay" class="confirm-dialog-overlay" aria-hidden="true">
        <div class="confirm-dialog-panel loading-dialog-panel" role="status" aria-live="polite">
          <div class="loading-spinner" aria-hidden="true"></div>
          <div class="confirm-dialog-title">Sending messages...</div>
          <div class="loading-text">Please wait while the message is being queued for all recipients.</div>
        </div>
      </div>
    </div>

  </div>

  <style>
    .sms-template-row {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .sms-template-label {
      margin: 0;
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      color: #485b8c;
    }

    .sms-template-button {
      width: 34px;
      height: 34px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      border: 1px solid #d9dee8;
      background: #ffffff;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .sms-template-button i {
      font-size: 0.9rem;
    }

    .sms-template-button:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 22px rgba(15, 23, 42, 0.12);
    }

    .sms-template-button:focus-visible {
      outline: 2px solid #1E2541;
      outline-offset: 2px;
    }

    .sms-template-button.template-typhoon {
      color: #d97706 !important;
      border-color: rgba(217, 119, 6, 0.28);
      background: linear-gradient(180deg, #fff8eb 0%, #ffffff 100%);
    }

    .sms-template-button.template-technical {
      color: #2563eb !important;
      border-color: rgba(37, 99, 235, 0.28);
      background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
    }

    .confirm-dialog-overlay {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(15, 23, 42, 0.35);
      z-index: 3000;
      padding: 16px;
    }

    .confirm-dialog-overlay.is-open {
      display: flex;
    }

    .confirm-dialog-panel {
      width: min(100%, 480px);
      background: #ffffff;
      color: inherit;
      border: 1px solid #d9dee8;
      border-radius: 14px;
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
      pointer-events: auto;
    }

    .confirm-dialog-header,
    .confirm-dialog-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 16px 20px;
    }

    .confirm-dialog-header {
      border-bottom: 1px solid #d9dee8;
    }

    .confirm-dialog-footer {
      border-top: 1px solid #d9dee8;
      justify-content: flex-end;
    }

    .confirm-dialog-title {
      margin: 0;
      font-size: 1.05rem;
      font-weight: 700;
    }

    .confirm-dialog-body {
      padding: 20px;
      line-height: 1.5;
    }

    .confirm-dialog-close {
      border: none;
      background: transparent;
      color: inherit;
      font-size: 1.6rem;
      line-height: 1;
      cursor: pointer;
      padding: 0;
    }

    .loading-dialog-panel {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      text-align: center;
      padding: 28px 24px;
    }

    .loading-spinner {
      width: 44px;
      height: 44px;
      border: 4px solid #d9dee8;
      border-top-color: #1E2541;
      border-radius: 50%;
      animation: semaphore-spin 0.85s linear infinite;
    }

    .loading-text {
      color: #4b5563;
      line-height: 1.5;
    }

    @keyframes semaphore-spin {
      to {
        transform: rotate(360deg);
      }
    }
  </style>

  <script>
    function showSemaphoreError(message) {
      if (typeof showToast === 'function') {
        showToast(message, 'danger');
        return;
      }

      alert(message);
    }

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

    const smsForm = document.getElementById('smsForm');
    const smsConfirmModalElement = document.getElementById('smsConfirmModal');
    const confirmSmsSendButton = document.getElementById('confirmSmsSendButton');
    const cancelSmsSendButton = document.getElementById('cancelSmsSendButton');
    const closeSmsConfirmModal = document.getElementById('closeSmsConfirmModal');
    const smsLoadingOverlay = document.getElementById('smsLoadingOverlay');

    function showSmsConfirmModal() {
      smsConfirmModalElement.classList.add('is-open');
      smsConfirmModalElement.setAttribute('aria-hidden', 'false');
    }

    function hideSmsConfirmModal() {
      smsConfirmModalElement.classList.remove('is-open');
      smsConfirmModalElement.setAttribute('aria-hidden', 'true');
    }

    function showSmsLoadingOverlay() {
      smsLoadingOverlay.classList.add('is-open');
      smsLoadingOverlay.setAttribute('aria-hidden', 'false');
      confirmSmsSendButton.disabled = true;
      cancelSmsSendButton.disabled = true;
      closeSmsConfirmModal.disabled = true;
    }

    smsForm.addEventListener('submit', function(e) {
      const msg = document.getElementById('message').value.trim();
      
      if (!msg) {
        e.preventDefault();
        showSemaphoreError('Message cannot be empty.');
        return;
      }

      if (smsForm.dataset.confirmed === 'true') {
        delete smsForm.dataset.confirmed;
        return;
      }

      e.preventDefault();
      showSmsConfirmModal();
    });

    confirmSmsSendButton.addEventListener('click', function() {
      smsForm.dataset.confirmed = 'true';
      hideSmsConfirmModal();
      showSmsLoadingOverlay();
      if (typeof smsForm.requestSubmit === 'function') {
        smsForm.requestSubmit();
        return;
      }

      smsForm.submit();
    });

    cancelSmsSendButton.addEventListener('click', hideSmsConfirmModal);
    closeSmsConfirmModal.addEventListener('click', hideSmsConfirmModal);

    smsConfirmModalElement.addEventListener('click', function(e) {
      if (e.target === smsConfirmModalElement) {
        hideSmsConfirmModal();
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && smsConfirmModalElement.classList.contains('is-open')) {
        hideSmsConfirmModal();
      }
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/semaphore.blade.php ENDPATH**/ ?>