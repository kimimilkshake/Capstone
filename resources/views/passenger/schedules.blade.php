@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="schedules-page">
        <div class="container">
            <h2 class="schedules-page-title">Sailing Schedules & Rates</h2>

            @forelse ($routeCategories as $category)
                @php
                    $routePorts = $category->routePorts;
                    // Separate into two directions (left = first, right = second)
                    $leftRoute = $routePorts->first();
                    $rightRoute = $routePorts->count() > 1 ? $routePorts->last() : null;
                @endphp

                <div class="route-card">
                    <div class="route-card-header">
                        <span><i class="fas fa-ship"></i> {{ $category->route_category_name }}</span>
                        <span class="route-card-header-right"><i class="fas fa-calendar-alt"></i> Weekly Sailing
                            Schedules</span>
                    </div>

                    <div class="row">
                        {{-- LEFT DIRECTION --}}
                        <div class="{{ $rightRoute ? 'col-md-6' : 'col-md-12' }}">
                            @if ($leftRoute)
                                @php
                                    $leftVoyages = $schedulesByRoutePort[$leftRoute->route_port_id] ?? collect();
                                @endphp
                                <div class="direction-block">
                                    <div class="direction-label">
                                        {{ $leftRoute->route_origin }} <i class="fas fa-long-arrow-alt-right"></i>
                                        {{ $leftRoute->route_destination }}
                                    </div>

                                    @if ($leftVoyages->isNotEmpty())
                                        @php
                                            $dayOrder = [
                                                'Sunday',
                                                'Monday',
                                                'Tuesday',
                                                'Wednesday',
                                                'Thursday',
                                                'Friday',
                                                'Saturday',
                                            ];
                                            $weeklySchedule = $leftVoyages
                                                ->map(function ($v) {
                                                    $dep = \Carbon\Carbon::parse($v->voyage_estimated_TD);
                                                    $arr = \Carbon\Carbon::parse($v->voyage_estimated_TA);
                                                    // Handle overnight: if arrival is before departure, it's the next day
        $diff = $dep->copy()->setDateFrom($dep);
        $arrTime = $arr->copy()->setDateFrom($dep);
        if ($arrTime->lte($diff)) {
            $arrTime->addDay();
        }
        $totalMinutes = $diff->diffInMinutes($arrTime);
        $hours = intdiv($totalMinutes, 60);
        $mins = $totalMinutes % 60;
        $eta = $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');

        return [
            'vessel' => $v->vessel->vessel_name ?? 'TBA',
            'day' => \Carbon\Carbon::parse(
                $v->voyage_departure_date,
            )->format('l'),
            'departure' => $dep->format('g:i A'),
            'arrival' => $arr->format('g:i A'),
            'eta' => $eta,
            'dep_raw' => $v->voyage_estimated_TD,
            'arr_raw' => $v->voyage_estimated_TA,
        ];
    })
    ->unique(function ($item) {
        return $item['vessel'] . $item['day'] . $item['departure'];
    })
    ->sort(function ($a, $b) use ($dayOrder) {
        $dayDiff =
            array_search($a['day'], $dayOrder) -
            array_search($b['day'], $dayOrder);
        if ($dayDiff !== 0) {
            return $dayDiff;
        }
        $depDiff = strcmp($a['dep_raw'], $b['dep_raw']);
        if ($depDiff !== 0) {
            return $depDiff;
        }
        return strcmp($a['arr_raw'], $b['arr_raw']);
                                                })
                                                ->values();
                                        @endphp
                                        <div class="schedule-table-wrapper">
                                            <table class="table schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th>Day</th>
                                                        <th>Departure</th>
                                                        <th>Arrival</th>
                                                        <th>Duration</th>
                                                        <th>Vessel</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $grouped = $weeklySchedule
                                                            ->groupBy('day')
                                                            ->sortBy(function ($items, $day) use ($dayOrder) {
                                                                return array_search($day, $dayOrder);
                                                            });
                                                    @endphp
                                                    @foreach ($grouped as $day => $rows)
                                                        @foreach ($rows as $idx => $sched)
                                                            <tr>
                                                                @if ($idx === 0)
                                                                    <td rowspan="{{ $rows->count() }}" class="align-middle">
                                                                        {{ $day }}</td>
                                                                @endif
                                                                <td>{{ $sched['departure'] }}</td>
                                                                <td>{{ $sched['arrival'] }}</td>
                                                                <td>{{ $sched['eta'] }}</td>
                                                                <td>{{ $sched['vessel'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="no-schedule">No schedule data available</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- RIGHT DIRECTION (reverse trip) --}}
                        @if ($rightRoute)
                            @php
                                $rightVoyages = $schedulesByRoutePort[$rightRoute->route_port_id] ?? collect();
                            @endphp
                            <div class="col-md-6">
                                <div class="direction-block">
                                    <div class="direction-label">
                                        {{ $rightRoute->route_origin }} <i class="fas fa-long-arrow-alt-right"></i>
                                        {{ $rightRoute->route_destination }}
                                    </div>

                                    @if ($rightVoyages->isNotEmpty())
                                        @php
                                            $dayOrder = [
                                                'Sunday',
                                                'Monday',
                                                'Tuesday',
                                                'Wednesday',
                                                'Thursday',
                                                'Friday',
                                                'Saturday',
                                            ];
                                            $weeklyScheduleRight = $rightVoyages
                                                ->map(function ($v) {
                                                    $dep = \Carbon\Carbon::parse($v->voyage_estimated_TD);
                                                    $arr = \Carbon\Carbon::parse($v->voyage_estimated_TA);
                                                    $diff = $dep->copy()->setDateFrom($dep);
                                                    $arrTime = $arr->copy()->setDateFrom($dep);
                                                    if ($arrTime->lte($diff)) {
                                                        $arrTime->addDay();
                                                    }
                                                    $totalMinutes = $diff->diffInMinutes($arrTime);
                                                    $hours = intdiv($totalMinutes, 60);
                                                    $mins = $totalMinutes % 60;
                                                    $eta = $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');

                                                    return [
                                                        'vessel' => $v->vessel->vessel_name ?? 'TBA',
                                                        'day' => \Carbon\Carbon::parse(
                                                            $v->voyage_departure_date,
                                                        )->format('l'),
                                                        'departure' => $dep->format('g:i A'),
                                                        'arrival' => $arr->format('g:i A'),
                                                        'eta' => $eta,
                                                        'dep_raw' => $v->voyage_estimated_TD,
                                                        'arr_raw' => $v->voyage_estimated_TA,
                                                    ];
                                                })
                                                ->unique(function ($item) {
                                                    return $item['vessel'] . $item['day'] . $item['departure'];
                                                })
                                                ->sort(function ($a, $b) use ($dayOrder) {
                                                    $dayDiff =
                                                        array_search($a['day'], $dayOrder) -
                                                        array_search($b['day'], $dayOrder);
                                                    if ($dayDiff !== 0) {
                                                        return $dayDiff;
                                                    }
                                                    $depDiff = strcmp($a['dep_raw'], $b['dep_raw']);
                                                    if ($depDiff !== 0) {
                                                        return $depDiff;
                                                    }
                                                    return strcmp($a['arr_raw'], $b['arr_raw']);
                                                })
                                                ->values();
                                        @endphp
                                        <div class="schedule-table-wrapper">
                                            <table class="table schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th>Day</th>
                                                        <th>Departure</th>
                                                        <th>Arrival</th>
                                                        <th>Duration</th>
                                                        <th>Vessel</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $groupedRight = $weeklyScheduleRight
                                                            ->groupBy('day')
                                                            ->sortBy(function ($items, $day) use ($dayOrder) {
                                                                return array_search($day, $dayOrder);
                                                            });
                                                    @endphp
                                                    @foreach ($groupedRight as $day => $rows)
                                                        @foreach ($rows as $idx => $sched)
                                                            <tr>
                                                                @if ($idx === 0)
                                                                    <td rowspan="{{ $rows->count() }}"
                                                                        class="align-middle">{{ $day }}</td>
                                                                @endif
                                                                <td>{{ $sched['departure'] }}</td>
                                                                <td>{{ $sched['arrival'] }}</td>
                                                                <td>{{ $sched['eta'] }}</td>
                                                                <td>{{ $sched['vessel'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="no-schedule">No schedule data available</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="no-routes-card">
                    <i class="fas fa-info-circle"></i>
                    <p>No schedules available at this time. Please check back later.</p>
                </div>
            @endforelse

            {{-- PASSAGE RATES SECTION (grouped by route, then by vessel) --}}
            <div class="route-card">
                <div class="route-card-header">
                    <span><i class="fas fa-tags"></i> Passage Rates</span>
                </div>

                @foreach ($routeCategories as $category)
                    @php
                        $vessels = $vesselsByCategory[$category->route_category_id] ?? collect();
                        // Order discounts by the canonical passenger type order
                        $typeOrder = [
                            'Regular',
                            'Senior Citizen',
                            'PWD',
                            'Student',
                            'Uniformed Personnel',
                            '3 to 11 years old',
                            'Below 3 years old',
                        ];
                        $discountMap = $category->passengerDiscounts
                            ->sortBy(fn($d) => array_search($d->passenger_type, $typeOrder))
                            ->values();
                        $typeLabels = [
                            'Regular' => 'Regular',
                            'Senior Citizen' => 'Senior Citizen',
                            'PWD' => 'PWD',
                            'Student' => 'Student',
                            'Uniformed Personnel' => 'Uniformed Personnel',
                            '3 to 11 years old' => 'Child (3-11 yrs)',
                            'Below 3 years old' => 'Infant (<3 yrs)',
                        ];
                    @endphp

                    @if ($vessels->isNotEmpty()) <div class="rates-route-group">
                            <div class="rates-route-name">{{ $category->route_category_name }}</div>

                            @foreach ($vessels as $vessel)
                                @if ($vessel->accommodations->isNotEmpty())
                                    <div class="rates-vessel-block">
                                        <div class="rates-vessel-name">{{ $vessel->vessel_name }}</div>
                                        <div class="rates-table-wrapper">
                                            <table class="table rates-table">
                                                <thead>
                                                    <tr>
                                                        <th>Accommodation</th>
                                                        @foreach ($discountMap as $disc)
                                                            <th>{{ $typeLabels[$disc->passenger_type] ?? $disc->passenger_type }}
                                                                @if ($disc->discount_rate > 0)
                                                                    <small style="color:#fff;font-weight:normal;opacity:0.85;"> -{{ rtrim(rtrim(number_format($disc->discount_rate, 2), '0'), '.') }}%</small> @endif
                    </th>
                @endforeach
                @if ($discountMap->isEmpty())
                    <th>Regular</th>
                @endif
                </tr>
                </thead>
                <tbody>
                    @foreach ($vessel->accommodations as $accommodation)
                        @php
                            $base =
                                (float) $accommodation->accommodation_regular_price *
                                (1 + ($category->route_rate ?? 0) / 100);
                        @endphp
                        <tr>
                            <td>{{ $accommodation->accommodation_name }}</td>
                            @if ($discountMap->isNotEmpty())
                                @foreach ($discountMap as $disc)
                                    @php
                                        $discounted = $base * ((100 - $disc->discount_rate) / 100);
                                    @endphp
                                    <td>
                                        @if ($disc->discount_rate >= 100)
                                            <span class="text-success fw-bold">FREE</span>
                                        @else
                                            &#8369;{{ number_format($discounted, 2) }}
                                        @endif
                                    </td>
                                @endforeach
                            @else
                                <td>&#8369;{{ number_format($base, 2) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
        @endif
        @endforeach

        {{-- Discount notes for this route category --}}
        @if ($discountMap->isNotEmpty())
            <p class="rates-note text-center">
                @foreach ($discountMap->where('discount_rate', '>', 0) as $disc)
                    * {{ $disc->passenger_type }}:
                    @if ($disc->discount_rate >= 100)
                        FREE
                    @else
                        {{ rtrim(rtrim(number_format($disc->discount_rate, 2), '0'), '.') }}% discount
                    @endif
                    @if (!$loop->last) &bull; @endif
                @endforeach
            </p>
        @endif
    </div>
    @endif
    @endforeach
    </div>
    </div>
    </div>

    @include('components.footer')
@endsection
