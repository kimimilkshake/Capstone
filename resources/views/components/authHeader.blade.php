<header class="auheader-main main-header d-flex align-items-center justify-content-between px-4">
  <div class="header-left d-flex align-items-center gap-3">
    <img src="{{ asset('images/lslc_logo_name2.png') }}" alt="Logo" class="auheader-logo">
  </div>

  <div class="auheader-title header-title text-center">
    <h2 class="m-0 fw-bold">@yield('page-title')</h2>
  </div>

  <div class="auheader-user d-flex align-items-center gap-3 position-relative">
    <!-- Notification Bell -->
    <div class="notification-wrapper position-relative">
      <button type="button" class="notification-bell-button" id="notificationToggle" aria-label="Open notifications">
        <i class="fa-regular fa-bell notification-bell"></i>
      </button>
      <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
      
      <!-- Notification Dropdown -->
      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
          <h6 class="m-0">Notifications</h6>
          <button class="btn-mark-all-read" id="markAllReadBtn">
            Mark all as read
          </button>
        </div>
        <div class="notification-tabs" id="notificationTabs">
          <button class="notification-tab active" data-filter="all">All</button>
          <button class="notification-tab" data-filter="unread">Unread</button>
          <button class="notification-tab" data-filter="read">Read</button>
        </div>
        <div class="notification-list" id="notificationList">
          <div class="notification-empty">
            <div class="notification-empty-icon">
              <i class="fa-regular fa-bell-slash"></i>
            </div>
            <p>No notifications</p>
            <span>New updates will appear here.</span>
          </div>
        </div>
      </div>
    </div>

    <div class="user-dropdown" id="userDropdownToggle">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-user auheader-profile"></i>
        <span class="fw-semibold">{{ Session::get('user_name') }}</span>
      </div>

        <div class="dropdown-menu">
          <form action="{{ route('logout') }}" method="POST">
            @csrf
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
    let currentFilter = 'all';
    let allNotifications = [];

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
            allNotifications = data.notifications;
            renderNotifications(filterNotifications(allNotifications, currentFilter));
            updateBadge(data.unread_count);
          }
        })
        .catch(error => {
          console.error('Error loading notifications:', error);
          notificationList.innerHTML = `
            <div class="notification-error">
              <div class="notification-empty-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
              </div>
              <p>Failed to load notifications</p>
              <span>Please try again in a moment.</span>
            </div>
          `;
        });
    }

    // Filter notifications by tab
    function filterNotifications(notifications, filter) {
      if (filter === 'unread') {
        return notifications.filter(n => !['read', 'archived', 'Read', 'Archived'].includes(n.notification_status));
      }
      if (filter === 'read') {
        return notifications.filter(n => ['read', 'Read'].includes(n.notification_status));
      }
      return notifications;
    }

    // Tab click handlers
    document.querySelectorAll('.notification-tab').forEach(tab => {
      tab.addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelectorAll('.notification-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentFilter = tab.dataset.filter;
        renderNotifications(filterNotifications(allNotifications, currentFilter));
      });
    });

    // Render notifications
    function renderNotifications(notifications) {
      if (notifications.length === 0) {
        notificationList.innerHTML = `
          <div class="notification-empty">
            <div class="notification-empty-icon">
              <i class="fa-regular fa-bell-slash"></i>
            </div>
            <p>No notifications</p>
            <span>New updates will appear here.</span>
          </div>
        `;
        return;
      }

      notificationList.innerHTML = notifications.map(notification => {
        const isUnread = !['read', 'archived', 'Read', 'Archived'].includes(notification.notification_status);
        const notificationType = (notification.notification_type || '').toLowerCase();
        const notificationMsg = (notification.notification_message || '').toLowerCase();
        const notificationStatus = (notification.notification_status || '').toLowerCase();
        const isPaymentNotification = notificationType.includes('payment') && !notificationType.includes('cargo payment');
        const isCargoPaymentNotification = notificationType === 'cargo payment';
        const isCargoVerificationNotification = notificationType === 'cargo payment verification';
        const isCargoNotification = notificationType.includes('cargo') && !isCargoPaymentNotification && !isCargoVerificationNotification;  
        const isPassengerNotification = notificationType.includes('passenger');
        const isPendingReview = isCargoNotification && notificationMsg.includes('pending review');
        const isApprovedCargo = isCargoNotification && notificationMsg.includes('has been approved');
        const needsVerification = isCargoVerificationNotification && notificationStatus !== 'verified';
        const typeIcon = isCargoPaymentNotification
          ? 'fa-money-bill-wave'
          : isCargoVerificationNotification
            ? 'fa-clipboard-check'
            : isPaymentNotification
              ? 'fa-money-bill-wave'
              : isCargoNotification
                ? 'fa-box-archive'
                : isPassengerNotification
                  ? 'fa-user-check'
                  : 'fa-bell';
        const typeClass = isPendingReview
          ? 'pending'
          : isApprovedCargo
            ? 'payment'
            : needsVerification
            ? 'pending'
            : isCargoPaymentNotification
              ? 'payment'
              : isCargoVerificationNotification
                ? 'payment'
                : isPaymentNotification
                  ? 'payment'
                  : isCargoNotification
                    ? 'cargo'
                    : isPassengerNotification
                      ? 'passenger'
                      : 'general';
        let typeLabel = notification.notification_type || 'Update';
        if (isCargoVerificationNotification) typeLabel = 'Cargo Payment';
        const timeAgo = formatTimeAgo(notification.notification_created);
        
        // Get booking reference from the field
        const bookingRef = notification.booking_ref_no;
        console.log('Notification:', notification.notification_id, 'Booking Ref:', bookingRef, 'Type:', notification.notification_type);
        
        // Make cargo notifications clickable if they have a booking reference
        // Check case-insensitively for cargo booking approval, payment, and verification
        const isAnyCargo = isCargoNotification || isCargoPaymentNotification || isCargoVerificationNotification;
        const clickHandler = bookingRef && isAnyCargo
          ? `onclick="markAsRead(${notification.notification_id}); navigateToCargo(${bookingRef})"` 
          : (isUnread ? `onclick="markAsRead(${notification.notification_id})"` : '');
        const cursorStyle = (bookingRef || isUnread) ? 'cursor: pointer;' : '';
        const unreadMarker = isUnread ? '<span class="notification-unread-dot"></span>' : '';

        return `
          <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.notification_id}" ${clickHandler} style="${cursorStyle}">
            <div class="notification-content">
              <div class="notification-meta-row">
                <span class="notification-type-pill ${typeClass}">${typeLabel}</span>
                <span class="notification-time">${timeAgo}</span>
              </div>
              <p class="notification-message">${notification.notification_message}</p>
            </div>
            ${unreadMarker}
            <button class="btn-archive-notification" onclick="event.stopPropagation(); archiveNotification(${notification.notification_id})" aria-label="Archive" title="Remove">
              <i class="fa-solid fa-xmark"></i>
            </button>
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
      // Immediately remove dot and unread styling so user sees instant feedback
      const item = document.querySelector(`.notification-item[data-id="${id}"]`);
      if (item) {
        item.classList.remove('unread');
        const dot = item.querySelector('.notification-unread-dot');
        if (dot) dot.remove();
      }

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
    const cargoBookingsBaseUrl = @json(
      Session::get('user_role') === 'admin'
        ? url('/authorized/admin/cargo-bookings')
        : url('/authorized/staff/cargo-bookings')
    );

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

