<header class="auheader-main main-header d-flex align-items-center justify-content-between px-4">
  <div class="header-left d-flex align-items-center gap-3">
    <img src="<?php echo e(asset('images/lslc_logo_name2.png')); ?>" alt="Logo" class="auheader-logo">
  </div>

  <div class="auheader-title header-title text-center">
    <h2 class="m-0 fw-bold"><?php echo $__env->yieldContent('page-title'); ?></h2>
  </div>

  <div class="auheader-user d-flex align-items-center gap-3 position-relative">
    <!-- Notification Bell -->
    <div class="notification-wrapper position-relative">
      <i class="fa-regular fa-bell notification-bell" id="notificationToggle"></i>
      <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
      
      <!-- Notification Dropdown -->
      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
          <h6 class="m-0">Notifications</h6>
          <button class="btn-mark-all-read" id="markAllReadBtn">Mark all as read</button>
        </div>
        <div class="notification-list" id="notificationList">
          <div class="notification-empty">
            <i class="fa-regular fa-bell-slash"></i>
            <p>No notifications</p>
          </div>
        </div>
      </div>
    </div>

    <div class="user-dropdown" id="userDropdownToggle">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-user auheader-profile"></i>
        <span class="fw-semibold"><?php echo e(Session::get('user_name')); ?></span>
      </div>

        <div class="dropdown-menu">
          <a href="#" class="dropdown-item">
            <i class="fa-solid fa-user"></i> View Profile
          </a>
          <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="dropdown-item logout">
              <i class="fa-solid fa-right-from-bracket"></i> Log out
            </button>
          </form>
        </div>
    </div>


  </div>
</header>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // User Dropdown
    const toggle = document.getElementById('userDropdownToggle');
    const dropdown = document.querySelector('.user-dropdown');

    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdown.classList.toggle('active');
      // Close notification dropdown
      document.querySelector('.notification-dropdown').classList.remove('active');
    });

    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });

    // Notification System
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');

    // Toggle notification dropdown
    notificationToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      notificationDropdown.classList.toggle('active');
      // Close user dropdown
      dropdown.classList.remove('active');
      
      if (notificationDropdown.classList.contains('active')) {
        loadNotifications();
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
        notificationDropdown.classList.remove('active');
      }
    });

    // Load notifications
    function loadNotifications() {
      fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            renderNotifications(data.notifications);
            updateBadge(data.unread_count);
          }
        })
        .catch(error => {
          console.error('Error loading notifications:', error);
          notificationList.innerHTML = '<div class="notification-error">Failed to load notifications</div>';
        });
    }

    // Render notifications
    function renderNotifications(notifications) {
      if (notifications.length === 0) {
        notificationList.innerHTML = `
          <div class="notification-empty">
            <i class="fa-regular fa-bell-slash"></i>
            <p>No notifications</p>
          </div>
        `;
        return;
      }

      notificationList.innerHTML = notifications.map(notification => {
        const isUnread = !['read', 'archived'].includes(notification.notification_status);
        const typeIcon = notification.notification_type === 'payment received' 
          ? 'fa-money-bill-wave' 
          : 'fa-box';
        const timeAgo = formatTimeAgo(notification.notification_created);
        
        // Get booking reference from the field
        const bookingRef = notification.booking_ref_no;
        console.log('Notification:', notification.notification_id, 'Booking Ref:', bookingRef, 'Type:', notification.notification_type);
        
        // Make cargo notifications clickable if they have a booking reference
        // Check case-insensitively for cargo booking approval
        const isCargoNotification = notification.notification_type && 
          notification.notification_type.toLowerCase().includes('cargo');
        const clickHandler = bookingRef && isCargoNotification
          ? `onclick="navigateToCargo(${bookingRef})"` 
          : '';
        const cursorStyle = bookingRef ? 'cursor: pointer;' : '';

        return `
          <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.notification_id}" ${clickHandler} style="${cursorStyle}">
            <div class="notification-icon">
              <i class="fa-solid ${typeIcon}"></i>
            </div>
            <div class="notification-content">
              <p class="notification-message">${notification.notification_message}</p>
              <span class="notification-time">${timeAgo}</span>
            </div>
          </div>
        `;
      }).join('');
    }

    // Update badge
    function updateBadge(count) {
      if (count > 0) {
        notificationBadge.textContent = count > 99 ? '99+' : count;
        notificationBadge.style.display = 'flex';
      } else {
        notificationBadge.style.display = 'none';
      }
    }

    // Format time ago
    function formatTimeAgo(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const seconds = Math.floor((now - date) / 1000);

      if (seconds < 60) return 'Just now';
      if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
      if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
      if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
      return date.toLocaleDateString();
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Mark as read
    window.markAsRead = function(id) {
      fetch(`/api/notifications/${id}/read`, { 
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            loadNotifications();
          }
        })
        .catch(error => console.error('Error marking as read:', error));
    };

    // Archive notification
    window.archiveNotification = function(id) {
      fetch(`/api/notifications/${id}`, { 
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            loadNotifications();
          }
        })
        .catch(error => console.error('Error archiving notification:', error));
    };

    // Mark all as read
    markAllReadBtn.addEventListener('click', () => {
      fetch('/api/notifications/mark-all-read', { 
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            loadNotifications();
          }
        })
        .catch(error => console.error('Error marking all as read:', error));
    });

    // Load unread count on page load
    function updateUnreadCount() {
      fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            updateBadge(data.unread_count);
          }
        })
        .catch(error => console.error('Error loading unread count:', error));
    }

    // Navigate to cargo booking page based on authenticated role
    const cargoBookingsBaseUrl = <?php echo json_encode(
      Session::get('user_role') === 'admin'
        ? url('/authorized/admin/cargo-bookings')
        : url('/authorized/staff/cargo-bookings')
    , 15, 512) ?>;

    window.navigateToCargo = function(bookingRef) {
      console.log('Navigating to cargo booking:', bookingRef);
      const url = `${cargoBookingsBaseUrl}/${bookingRef}`;
      console.log('Navigation URL:', url);
      window.location.href = url;
    };

    // Initial load
    updateUnreadCount();
    
    // Poll for new notifications every 30 seconds
    setInterval(updateUnreadCount, 30000);
  });
</script>

<?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/components/authHeader.blade.php ENDPATH**/ ?>