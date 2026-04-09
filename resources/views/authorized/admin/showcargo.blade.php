@extends('layouts.app')
@section('page-title', 'CARGO BOOKING DETAILS')

@section('content')
	@include('components.authHeader')
	@include('components.admin_nav')

	<div class="staff-body">

		<div class="row">
			<div class="col-lg-6">
				<div class="card shadow-sm p-4 mb-4">
					<h5 class="mb-2">Booking Information</h5>
					@php
						$processedBy = optional(optional($booking->cargoBookings->first())->approvedByStaff)->staff_name;
						$processedLabel = 'N/A';
						if ($processedBy) {
							if ($booking->booking_status === 'Confirmed') {
								$processedLabel = 'Approved by ' . $processedBy;
							} elseif ($booking->booking_status === 'Canceled') {
								$processedLabel = 'Canceled by ' . $processedBy;
							} else {
								$processedLabel = $processedBy;
							}
						}
					@endphp
					<p><strong>Booking Ref #:</strong> {{ $booking->booking_code }}</p>
					<p><strong>Status:</strong> {{ in_array($booking->booking_status, ['Canceled', 'Cancelled']) ? 'Rejected' : $booking->booking_status }}</p>
					<p><strong>Created:</strong> {{ $booking->created_at->format('M d, Y') }}</p>
					<p><strong>Approved By:</strong> {{ $processedLabel }}</p>

					@if ($booking->voyage)
						<p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
						<p><strong>Departure:</strong> {{ $booking->voyage->voyage_departure_date }}</p>
						<p><strong>Arrival:</strong> {{ $booking->voyage->voyage_arrival_date }}</p>
					@else
						<p><strong>Voyage:</strong> N/A</p>
					@endif

					@if ($payment)
						<p><strong>Mode of Payment:</strong> {{ $payment->mode_of_payment }}</p>
						<p><strong>Payment Status:</strong> {{ $payment->payment_status }}</p>
						<p><strong>Amount Paid:</strong> ₱{{ number_format($payment->total_amount, 2) }}</p>
					@endif
				</div>
			</div>

			<div class="col-lg-6">
				<div class="card shadow-sm p-4 mb-4">
					<h6 class="fw-bold">Sender Information</h6>
					<p><strong>Name:</strong> {{ $booking->sender->sender_name }}</p>
					<p><strong>Contact:</strong> {{ $booking->sender->sender_contactno }}</p>
					<p><strong>Email:</strong> {{ $booking->sender->sender_email }}</p>

					<h6 class="fw-bold mt-3">Consignee Information</h6>
					<p><strong>Name:</strong> {{ $booking->consignee->consignee_name }}</p>
					<p><strong>Contact:</strong> {{ $booking->consignee->consignee_contactno }}</p>
				</div>
			</div>
		</div>

		@php
			$cargoBookings = $booking->cargoBookings;
			$cargoWithPhotos = $cargoBookings->filter(fn($c) => $c->cargo_picture)->values();
			$hasPhotos = $cargoWithPhotos->count() > 0;
		@endphp

		<div class="card shadow-sm p-4 mb-4">
			<h5 class="fw-bold mb-3">Cargo Photos</h5>

			@if (!$hasPhotos)
				<p class="text-muted text-center fst-italic">
					No photos were included since the booking was made by the staff
				</p>
			@else
				<div id="cargoCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
					<div class="carousel-indicators">
						@foreach ($cargoWithPhotos as $index => $cargo)
							<button type="button" data-bs-target="#cargoCarousel" data-bs-slide-to="{{ $index }}"
								class="{{ $index === 0 ? 'active' : '' }}"
								aria-label="Slide {{ $index + 1 }}"></button>
						@endforeach
					</div>

					<div class="carousel-inner">
						@foreach ($cargoWithPhotos as $index => $cargo)
							@php
								$imgPath = asset('files/' . $cargo->cargo_picture);
								$cargoDescription = $cargo->cargoItem->cargo_item_description ?? 'Unknown Cargo';
								$cargoClassification = $cargo->cargoClassification->cargo_classification_name ?? '';
							@endphp

							<div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
								<div class="carousel-image-container">
									<img src="{{ $imgPath }}" class="d-block w-100 carousel-img"
										alt="{{ $cargoDescription }}" data-image="{{ $imgPath }}"
										data-description="{{ $cargoDescription }}"
										data-classification="{{ $cargoClassification }}" data-bs-toggle="modal"
										data-bs-target="#photoModal" style="cursor: pointer;">

									<div class="carousel-caption-overlay">
										<h5 class="carousel-cargo-title">{{ $cargoDescription }}</h5>
										<p class="carousel-cargo-classification">{{ $cargoClassification }}</p>
									</div>
								</div>
							</div>
						@endforeach
					</div>

					@if ($cargoWithPhotos->count() > 1)
						<button class="carousel-control-prev" type="button" data-bs-target="#cargoCarousel"
							data-bs-slide="prev">
							<span class="carousel-control-prev-icon" aria-hidden="true"></span>
							<span class="visually-hidden">Previous</span>
						</button>
						<button class="carousel-control-next" type="button" data-bs-target="#cargoCarousel"
							data-bs-slide="next">
							<span class="carousel-control-next-icon" aria-hidden="true"></span>
							<span class="visually-hidden">Next</span>
						</button>
					@endif
				</div>
			@endif
		</div>

		<div class="card shadow-sm p-3 mb-4">
			<h5>Cargo Items</h5>

			<div class="table-responsive">
				<table class="table table-bordered table-striped mt-3 align-middle cargo-items-table">
					<thead class="table-dark">
						<tr>
							<th>Description</th>
							<th>Classification</th>
							<th>Qty</th>
							<th>Length</th>
							<th>Width</th>
							<th>Height</th>
							<th>CBM</th>
							<th>Freight</th>
							<th>Subtotal</th>
						</tr>
					</thead>

					<tbody>
						@php $total = 0; @endphp

						@foreach ($cargoBookings as $c)
							@php
								$freight = $c->cargoItem->cargo_item_freight;
								$cbm = (float) ($c->cbm ?? ($c->length * $c->width * $c->height) / 1000000);
								$subtotal = $freight * $cbm * $c->quantity;
								$total += $subtotal;
								$unit = $c->measurementUnit->measurement_unit_abbreviation ?? 'cm';
							@endphp

							<tr>
								<td>{{ $c->cargoItem->cargo_item_description }}</td>
								<td>{{ $c->cargoClassification->cargo_classification_name ?? 'N/A' }}</td>
								<td class="text-center">{{ $c->quantity }}</td>
								<td class="text-end">{{ number_format($c->length, 2) }}{{ $unit }}</td>
								<td class="text-end">{{ number_format($c->width, 2) }}{{ $unit }}</td>
								<td class="text-end">{{ number_format($c->height, 2) }}{{ $unit }}</td>
								<td class="text-end">{{ number_format($cbm, 4) }}</td>
								<td class="text-end">₱{{ number_format($freight, 2) }}</td>
								<td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
							</tr>
						@endforeach
					</tbody>

					<tfoot>
						<tr>
							<th colspan="8" class="text-end">TOTAL:</th>
							<th class="text-end">
								@if ($payment && $payment->total_amount)
									₱{{ number_format($payment->total_amount, 2) }}
								@else
									₱{{ number_format($total, 2) }}
								@endif
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content bg-dark">
					<div class="modal-body p-0 position-relative" style="height: 600px;">
						<img id="modalCargoPhoto" class="w-100 h-100" style="object-fit: contain;" alt="Cargo Photo">

						<div class="position-absolute bottom-0 start-0 p-3 bg-dark bg-opacity-90 text-white"
							style="border-radius: 0 8px 0 0;">
							<h6 id="modalPhotoCaption" class="mb-1">Cargo Item</h6>
							<small id="modalPhotoClassification" class="text-muted">Classification: --</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="text-center mt-4">
			<a href="{{ route('admin.cargo.bookings.pending') }}" class="btn btn-outline-primary btn-lg px-4">
				Back to Review Cargo
			</a>

			<a href="{{ route('admin.cargo.bookings.bol', $booking->booking_ref_no) }}" target="_blank"
				class="btn btn-secondary btn-lg px-4 ms-3">
				Freight Receipt
			</a>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const photoModal = document.getElementById('photoModal');
			const modalImg = document.getElementById('modalCargoPhoto');
			const modalCaption = document.getElementById('modalPhotoCaption');
			const modalClassification = document.getElementById('modalPhotoClassification');

			if (photoModal) {
				photoModal.addEventListener('show.bs.modal', function(event) {
					const trigger = event.relatedTarget;
					const imgSrc = trigger?.getAttribute('data-image');
					const description = trigger?.getAttribute('data-description') || 'Cargo Item';
					const classification = trigger?.getAttribute('data-classification') || '--';

					if (modalImg) modalImg.src = imgSrc;
					if (modalCaption) modalCaption.textContent = description;
					if (modalClassification) modalClassification.textContent = `Classification: ${classification}`;
				});
			}
		});
	</script>
@endsection
