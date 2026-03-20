@extends('layouts.app')
@section('page-title', 'CREATE PROMO')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">

      <div class="acs-form_container">
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

        <form action="{{ route('admin.storePromo') }}" method="POST" class="create-promo-form">
          @csrf
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="promo_name">Promo Name <span class="text-danger">*</span></label>
                <input type="text" id="promo_name" name="promo_name" required>
              </div>

              <div class="form-group">
                <label for="promo_code">Promo Code <span class="text-danger">*</span></label>
                <input type="text" id="promo_code" name="promo_code" required>
              </div>

              <div class="form-group">
                <label for="promo_discount_rate">Discount Rate</label>
                <input type="number" id="promo_discount_rate" name="promo_discount_rate">
              </div>

            </div>

            <!-- Column 2 -->
            <div class="form-col">

              <div class="form-group">
                <label for="promo_start_date">Date Start <span class="text-danger">*</span></label>
                <input type="date" id="promo_start_date" name="promo_start_date" required min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
              </div>

              <div class="form-group">
                <label for="promo_end_date">Date End <span class="text-danger">*</span></label>
                <input type="date" id="promo_end_date" name="promo_end_date" required>
              </div>

              <div class="form-group">
                <label for="promo_status">Promo Status <span class="text-danger">*</span></label>
                <select id="promo_status" name="promo_status" required>
                  <option value="">Select Status</option>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>

            </div>
          </div>
          <div class="form-row">
            <div class="form-group" id="promo_desc">
                <label for="promo_description">Promo Description <span class="text-danger">*</span></label>
                <textarea id="promo_description" name="promo_description" placeholder="Enter promo description here"></textarea>
              </div>
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
            <button type="submit" class="acs-add-btn">
              <i class="fa-solid fa-plus me-2"></i>Add Promo
            </button>
            <a href="{{ route('admin.promo_list') }}" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </form>
      </div>
    </div>

    <script>
      const startDateInput = document.getElementById('promo_start_date');
      const endDateInput = document.getElementById('promo_end_date');

      startDateInput.addEventListener('change', function() {
          const startDate = new Date(this.value);
          if (startDate) {
              // Set min of end date to one day after start date
              const minEndDate = new Date(startDate);
              minEndDate.setDate(minEndDate.getDate() + 1);
              endDateInput.min = minEndDate.toISOString().split('T')[0];

              // Optional: if end date is before new min, reset it
              if (endDateInput.value && new Date(endDateInput.value) <= startDate) {
                  endDateInput.value = '';
              }
          }
      });

      // Optional: set end date min on page load if start date has a value
      if (startDateInput.value) {
          const startDate = new Date(startDateInput.value);
          const minEndDate = new Date(startDate);
          minEndDate.setDate(minEndDate.getDate() + 1);
          endDateInput.min = minEndDate.toISOString().split('T')[0];
      }
    </script>
@endsection