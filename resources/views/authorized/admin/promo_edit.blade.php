@extends('layouts.app')
@section('page-title', 'PROMO')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">
      <div class="apl-title">
        <h3>EDIT PROMO</h3>
      </div>

      <div class="acs-form_container">
        @if ($errors->any())
          <div class="alert alert-danger" style="color: red; text-align: center;">
            <strong>All fields are required.</strong><br>
            @foreach ($errors->all() as $error)
              {{ $error }}<br>
            @endforeach
          </div>
        @endif

        @if (session('success'))
          <div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 1rem;">
            {{ session('success') }}
          </div>
        @endif

        <form action="{{ route('admin.promo_update', $promo->promo_id) }}" method="POST" class="create-promo-form">
          @csrf
          @method('PUT')

          <div class="form-row">
            <!-- Column 1 -->
            <div class="form-col">
              <div class="form-group">
                <label for="promo_name">Promo Name</label>
                <input type="text" id="promo_name" name="promo_name" value="{{ $promo->promo_name }}" required>
              </div>

              <div class="form-group">
                <label for="promo_code">Promo Code</label>
                <input type="text" id="promo_code" name="promo_code" value="{{ $promo->promo_code }}" required>
              </div>

              <div class="form-group">
                <label for="promo_type">Type</label>
                <select id="promo_type" name="promo_type" required>
                  <option value="Discount" {{ $promo->promo_type == 'Discount' ? 'selected' : '' }}>Discount</option>
                  <option value="Freebie" {{ $promo->promo_type == 'Freebie' ? 'selected' : '' }}>Freebie</option>
                </select>
              </div>
            </div>

            <!-- Column 2 -->
            <div class="form-col">
              <div class="form-group">
                <label for="promo_start_date">Date Start</label>
                <input type="date" id="promo_start_date" name="promo_start_date" value="{{ $promo->promo_start_date }}" required>
              </div>

              <div class="form-group">
                <label for="promo_end_date">Date End</label>
                <input type="date" id="promo_end_date" name="promo_end_date" value="{{ $promo->promo_end_date }}" required>
              </div>

              <div class="form-group">
                <label for="promo_discount_rate">Discount Rate</label>
                <input type="number" id="promo_discount_rate" name="promo_discount_rate" value="{{ $promo->promo_discount_rate }}" required>
              </div>
            </div>
          </div>

          <!-- Description & Status side by side -->
          <div class="form-row" style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
              <label for="promo_description">Promo Description</label>
              <textarea id="promo_description" name="promo_description" required>{{ $promo->promo_description }}</textarea>
            </div>

            <div class="form-group" style="flex: 1;">
              <label for="promo_status">Status</label>
              <select id="promo_status" name="promo_status" required>
                <option value="Active" {{ $promo->promo_status == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $promo->promo_status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
            <button type="submit" class="acs-add-btn">
              <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.promo_list') }}" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
@endsection
