@extends('layouts.app')
@section('page-title', 'PROMO')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">
      <div class="apl-title">
        <h3>CREATE PROMO</h3>
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

        <form action="{{ route('admin.storePromo') }}" method="POST" class="create-promo-form">
          @csrf
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="promo_name">Promo Name</label>
                <input type="text" id="promo_name" name="promo_name" required>
              </div>

              <div class="form-group">
                <label for="promo_code">Promo Code</label>
                <input type="text" id="promo_code" name="promo_code" required>
              </div>

              <div class="form-group">
                <label for="promo_type">Type</label>
                <select id="promo_type" name="promo_type" required>
                  <option value="">Select</option>
                  <option value="Discount">Discount</option>
                  <option value="Freebie">Freebie</option>
                </select>
              </div>
            </div>

            <!-- Column 2 -->
            <div class="form-col">

              <div class="form-group">
                <label for="promo_start_date">Date Start</label>
                <input type="date" id="promo_start_date" name="promo_start_date" required>
              </div>

              <div class="form-group">
                <label for="promo_end_date">Date End</label>
                <input type="date" id="promo_end_date" name="promo_end_date" required>
              </div>

              <div class="form-group">
                <label for="promo_discount_rate">Discount Rate</label>
                <input type="number" id="promo_discount_rate" name="promo_discount_rate" required>
              </div>

            </div>
          </div>
          <div class="form-row">
            <div class="form-group" id="promo_desc">
                <label for="promo_description">Promo Description</label>
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