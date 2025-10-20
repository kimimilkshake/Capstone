@extends('layouts.app')
@section('page-title', 'PROMO')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">
      <div class="apl-title">
        <h3>PROMO LIST</h3>
      </div>
      <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
          <form class="search-bar" action="{{ route('admin.staff_list') }}" method="GET" style="flex: 1;">
              <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}">
              <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
          </form>

          <div class="add-promo">
            <a href="{{ route('admin.create_promo') }}"><i class="fa-solid fa-plus"></i>Add Promo</a>
          </div>
      </div>
    </div>