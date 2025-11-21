@extends('layouts.app')
@section('page-title', 'Cargo Auto Placement')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')


<div class="staff-body container">
  <h3>Cargo Auto Placement</h3>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('result'))
    <div class="alert alert-info">
      <h5 class="mb-2">Packing Result</h5>
      <pre class="mb-0" style="white-space:pre-wrap;word-break:break-word;">{{ json_encode(session('result'), JSON_PRETTY_PRINT) }}</pre>
    </div>
  @endif

  <form id="placementForm" method="POST" action="{{ route('cargo.place') }}">
    @csrf

    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Hatch Dimensions</h5>
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label"><strong>Width</strong></label>
            <input name="hatch_width" class="form-control" required value="{{ old('hatch_width') }}" />
          </div>
          <div class="col-md-3">
            <label class="form-label"><strong>Height</strong></label>
            <input name="hatch_height" class="form-control" required value="{{ old('hatch_height') }}" />
          </div>
          <div class="col-md-3">
            <label class="form-label"><strong>Depth</strong></label>
            <input name="hatch_depth" class="form-control" required value="{{ old('hatch_depth') }}" />
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Cargo Items</h5>

         @php
        // Do not create a default empty row here.
        // If there is old input (after addRow/removeRow or validation), use it; otherwise start with no items.
        $oldItems = old('items', []);
        @endphp

            <div id="items">
  @foreach($oldItems as $index => $it)
    <div class="item row g-2 align-items-center mb-2" data-index="{{ $index }}">
      <div class="col-md-3">
        <input name="items[{{ $index }}][id]" class="form-control" placeholder="Item id" required value="{{ $it['id'] ?? '' }}" />
      </div>
      <div class="col-md-2">
        <input name="items[{{ $index }}][w]" class="form-control" placeholder="Width" required value="{{ $it['w'] ?? '' }}" />
      </div>
      <div class="col-md-2">
        <input name="items[{{ $index }}][h]" class="form-control" placeholder="Height" required value="{{ $it['h'] ?? '' }}" />
      </div>
      <div class="col-md-2">
        <input name="items[{{ $index }}][d]" class="form-control" placeholder="Depth" required value="{{ $it['d'] ?? '' }}" />
      </div>
      <div class="col-md-1">
        <input name="items[{{ $index }}][q]" class="form-control" placeholder="Qty" value="{{ $it['q'] ?? 1 }}" required />
      </div>
    </div>
  @endforeach
</div>

<div class="mt-2">
  {{-- Add Item posts to addRow route and returns the page with one more row --}}
  <button type="submit" formaction="{{ route('cargo.addRow') }}" formmethod="post" class="btn btn-secondary">Add Item</button>
</div>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Items Summary</h5>
        <div class="table-responsive">
          <table class="table table-bordered" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th style="width:48px">#</th>
                <th>Item id</th>
                <th>Width</th>
                <th>Height</th>
                <th>Depth</th>
                <th style="width:80px">Qty</th>
                <th style="width:110px">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($oldItems as $index => $it)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $it['id'] ?? '' }}</td>
                  <td>{{ $it['w'] ?? '' }}</td>
                  <td>{{ $it['h'] ?? '' }}</td>
                  <td>{{ $it['d'] ?? '' }}</td>
                  <td>{{ $it['q'] ?? 1 }}</td>
                  <td>
                    {{-- Remove button posts to removeRow with the index value --}}
                    <button type="submit"
                            name="remove_index"
                            value="{{ $index }}"
                            formaction="{{ route('cargo.removeRow') }}"
                            formmethod="post"
                            class="btn btn-sm btn-danger"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Check Placement</button>
      <button type="reset" class="btn btn-outline-secondary">Reset</button>
    </div>
  </form>
</div>
@endsection
