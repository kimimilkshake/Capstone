@extends('layouts.app')
@section('page-title', 'PROMOS')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">
      
      <!-- Floating Toast Container - Below navbar on the right side -->
        <div class="toast-container position-fixed p-3" style="z-index: 9999; top: 80px; right: 20px;">
            @if (session('success'))
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="successToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="toast align-items-center text-white bg-danger border-0 show" role="alert"
                    aria-live="assertive" aria-atomic="true" id="errorToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
        </div>

      <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
        <form class="search-bar" action="{{ route('admin.promo_list') }}" method="GET" style="flex: 1;">
          <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}">
          <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
        </form>

        <div class="add-promo">
          <a href="{{ route('admin.create_promo') }}"><i class="fa-solid fa-plus me-2"></i>Add Promo</a>
        </div>
      </div>
      <table class="promo-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Discount Rate</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($promos as $index => $p)
            <tr>
              <td>{{ $p->promo_name }}</td>
              <td>{{ $p->promo_code }}</td>
              <td>{{ rtrim(rtrim($p->promo_discount_rate, '0'), '.') }}%</td>
              <td>{{ $p->promo_start_date }}</td>
              <td>{{ $p->promo_end_date }}</td>
              <td>{{ $p->promo_status }}</td>
              <td>
                <a href="{{ route('admin.promo_edit', $p->promo_id) }}" class="editRouteBtn link-btn" title="Edit Promo">
                  <i class="fa fa-pencil" aria-hidden="true"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center">No promos fuond.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <div class="pagination-container">
        {{ $promos->appends(request()->query())->links('pagination::bootstrap-5') }}
      </div>
    </div>

@endsection