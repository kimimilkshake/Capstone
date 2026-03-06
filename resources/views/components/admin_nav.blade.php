<nav class="admin-nav">
  <ul class="anav justify-content-start">

    <li class="anav-item">
      <a class="anav-link" id="adash" href="{{ route('admin.dashboard') }}">
        <i class="fa-solid fa-chart-simple me-1"></i>
        <span class="anav-label">Dashboard</span>
      </a>
    </li>

    <li class="anav-item">
      <i class="fa-solid fa-map me-3"></i></i><span class="anav-label">Voyages</span>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.route_port_list') }}">
        <i class="fa-solid fa-route"></i>
        <span class="anav-label">Routes and Ports</span>
      </a>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.voyage_list') }}">
        <i class="fa-solid fa-map-location-dot"></i>
        <span class="anav-label">Voyage List</span>
      </a>
    </li>

    <li class="anav-item">
      <i class="fa-solid fa-box me-3"></i>
      <span class="anav-label">Cargo</span>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.cargo.placement') }}">
        <i class="fa-solid fa-cubes me-1"></i>
        <span class="anav-label">Auto Placement</span>
      </a>
    </li>

    <li class="anav-item">
      <a href="#" class="anav-link">
        <i class="fa-solid fa-eye me-1"></i>
        <span class="snav-label">Review Cargo</span>
      </a>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.create_cargo_item') }}">
        <i class="fa-solid fa-boxes-stacked me-1"></i>
        <span class="anav-label">Create Cargo Item</span>
      </a>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.cargo_item_list') }}">
        <i class="fa-solid fa-rectangle-list me-1"></i>
        <span class="anav-label">View Rates</span>
      </a>
    </li>

    <li class="anav-item">
      <i class="fa-solid fa-ship me-3"></i>
      <span class="anav-label">Vessel</span>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.vessel_list') }}">
        <i class="fa-solid fa-ferry me-1"></i>
        <span class="anav-label">Vessel List</span>
      </a>
    </li>

    <li class="anav-item">
      <i class="fa-solid fa-percent me-3"></i>
      <span class="anav-label">Promos</span>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.promo_list') }}">
        <i class="fa-solid fa-tags me-1"></i>
        <span class="anav-label">Promo List</span>
      </a>
    </li>

    <li class="anav-item">
      <i class="fa-solid fa-user-gear me-3"></i>
      <span class="anav-label">Staff</span>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.create_staff') }}">
        <i class="fa-solid fa-user-plus me-1"></i>
        <span class="anav-label">Create Staff</span>
      </a>
    </li>

    <li class="anav-item">
      <a class="anav-link" href="{{ route('admin.staff_list') }}">
        <i class="fa-solid fa-users me-1"></i>
        <span class="anav-label">Staff List</span>
      </a>
    </li>

    <li class="anav-item" id="collapseBtn" style="cursor: pointer">
      <i class="fa-solid fa-bars me-3"></i>
      <span class="anav-label">Collapse Menu</span>
    </li>

  </ul>
</nav>

