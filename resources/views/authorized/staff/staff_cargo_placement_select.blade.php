@extends('layouts.app')
@section('page-title', 'CARGO AUTO PLACEMENT')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <div class="staff-body">

        <div class="scs-form_container">
            <div class="mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h5 class="mb-0"><i class="fas fa-ship"></i> Select a Voyage to View Placement</h5>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        @if ($viewPast)
                            <p style="color: #999; font-size: 0.95rem; margin: 0; font-style: italic;">Past 7 days</p>
                            <a href="{{ route('staff.cargo.placement') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-right"></i> View Upcoming
                            </a>
                        @else
                            <a href="{{ route('staff.cargo.placement', ['view' => 'past']) }}"
                                class="btn btn-secondary btn-sm">
                                <i class="fas fa-history"></i> View Past
                            </a>
                        @endif
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach ($voyages as $voyage)
                        @php
                            // Calculate cargo count first
                            $cargoCount = $voyage->cargoReceipts()->count();

                            // Get hatches and calculate available weight
                            $hatches = $voyage->vessel->hatches ?? collect();
                            $hatchInfo = [];
                            foreach ($hatches->take(2) as $hatch) {
                                $currentWeight =
                                    \Illuminate\Support\Facades\DB::table('cargo_receipt')
                                        ->join(
                                            'cargo_booking',
                                            'cargo_receipt.cargo_booking_id',
                                            '=',
                                            'cargo_booking.cargo_booking_id',
                                        )
                                        ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
                                        ->where('cargo_receipt.voyage_id', $voyage->voyage_id)
                                        ->sum('cargo_booking.weight') ?? 0;

                                $maxCapacity = ((float) $hatch->hatch_capacity_per_hold) * 1000;
                                $availableWeight = $maxCapacity - $currentWeight;
                                $hatchInfo[] = [
                                    'label' => $hatch->hatch_label,
                                    'available' => number_format($availableWeight, 2),
                                ];
                            }
                        @endphp

                        @if ($cargoCount === 0)
                            <div class="card"
                                style="opacity: 0.6; cursor: not-allowed; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-left: auto; width: 100%; max-width: 100%;">
                            @else
                                <a href="{{ route('staff.cargo.placement', ['voyage_id' => $voyage->voyage_id]) }}"
                                    class="card text-decoration-none"
                                    style="transition: all 0.3s ease; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-left: auto; width: 100%; max-width: 100%;"
                                    onmouseover="this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15); this.style.transform='translateY(-2px)'"
                                    onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1); this.style.transform='translateY(0)'">
                        @endif
                        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
                            <!-- Left Side: Voyage Info -->
                            <div style="flex: 1;">
                                <h6 class="card-title mb-2" style="color: #485b8c; font-weight: bold;">
                                    {{ $voyage->voyage_code }}</h6>
                                <p class="card-text mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-route"></i>
                                    <strong>{{ $voyage->routePort->route_origin }} →
                                        {{ $voyage->routePort->route_destination }}</strong>
                                </p>
                                <p class="card-text mb-2" style="font-size: 0.85rem; color: #666;">
                                    <i class="fas fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y') }}
                                </p>
                                <p class="card-text mb-0" style="font-size: 0.85rem; color: #666;">
                                    <i class="fas fa-ship"></i>
                                    Vessel: {{ $voyage->vessel->vessel_name ?? 'N/A' }}
                                </p>
                            </div>

                            <!-- Center: Departure Info -->
                            <div
                                style="flex: 0 0 auto; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; margin: 0 2rem; padding: 1rem; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #485b8c;">
                                <p
                                    style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; font-weight: 600;">
                                    Departure</p>
                                <p style="font-size: 1.1rem; color: #485b8c; font-weight: bold; margin: 0;">
                                    {{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y') }}
                                </p>
                                <p style="font-size: 0.95rem; color: #666; margin: 0; font-weight: 600;">
                                    <i class="fas fa-clock" style="color: #485b8c; margin-right: 0.3rem;"></i>
                                    {{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A') }}
                                </p>
                            </div>

                            <!-- Right Side: Hatch Columns or Empty Message -->
                            <div style="flex: 0 0 auto; display: flex; gap: 2rem; margin-left: 2rem; align-items: center;">
                                @if ($cargoCount === 0)
                                    <div style="text-align: center; width: 250px;">
                                        <p style="font-size: 2.5rem; color: #dc3545; font-weight: bold; margin: 0;">
                                            EMPTY
                                        </p>
                                    </div>
                                @else
                                    @foreach ($hatchInfo as $hatch)
                                        <div style="text-align: center;">
                                            <p class="mb-1"
                                                style="font-size: 1.5rem; color: #485b8c; font-weight: bold; margin-bottom: 0.5rem;">
                                                Hatch {{ $hatch['label'] }}
                                            </p>
                                            <p style="font-size: 0.85rem; color: #666; margin: 0.3rem 0;">
                                                Available Weight
                                            </p>
                                            <p style="font-size: 1rem; color: #485b8c; font-weight: bold; margin: 0;">
                                                {{ $hatch['available'] }} kg
                                            </p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @if ($cargoCount === 0)
                </div>
            @else
                </a>
                @endif
                @endforeach

                @if ($voyages->isEmpty())
                    <div class="text-center" style="background-color: transparent; border: none; width: 100%; margin: 0;">
                        <p style="color: #999; font-size: 0.95rem; letter-spacing: 0.5px;">
                            NO VOYAGES AVAILABLE AT THIS TIME.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
