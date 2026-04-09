@extends('layouts.app')
@section('page-title', 'REVIEW CARGO BOOKINGS')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">

<div class="search-filter-row mb-4" style="display:flex; gap:10px;">
    <form class="search-bar d-flex gap-2 align-items-stretch" action="{{ route('cargo.bookings.pending') }}" method="GET" style="flex:1;">
        <input type="text" name="search" class="form-control" style="height: 46px; border-radius: 0;" placeholder="Search by Ref No., Sender, or Consignee..." value="{{ request('search') }}">
        <select name="booking_status" class="form-select" style="max-width: 180px; height: 46px; border-radius: 0;">
            @foreach($allowedStatuses as $status)
                <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                    {{ in_array($status, ['Canceled', 'Cancelled']) ? 'Rejected' : $status }}
                </option>
            @endforeach
        </select>
        <select name="payment_status" class="form-select" style="max-width: 180px; height: 46px; border-radius: 0;">
            @foreach($allowedPaymentStatuses as $status)
                <option value="{{ $status }}" {{ ($selectedPaymentStatus ?? 'All') === $status ? 'selected' : '' }}>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search') || request('booking_status') || request('payment_status'))
            <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </form>
</div>


    <table class="cargo-item-table">
        <thead>
            <tr>
                <th>Booking Ref #</th>
                <th>Sender</th>
                <th>Consignee</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Handled By</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($bookings as $b)
                <tr>
                    <td>{{ $b->booking_code }}</td>
                    <td>{{ optional($b->sender)->sender_name ?? 'N/A' }}</td>
                    <td>{{ optional($b->consignee)->consignee_name ?? 'N/A' }}</td>
                    <td>{{ in_array($b->booking_status, ['Canceled', 'Cancelled']) ? 'Rejected' : $b->booking_status }}</td>
                    <td>{{ optional($b->payment)->payment_status ?? 'N/A' }}</td>
                    @php
                        $processedBy = optional(optional($b->cargoBookings->first())->approvedByStaff)->staff_name;
                        $processedLabel = 'N/A';
                        if ($processedBy) {
                            if ($b->booking_status === 'Confirmed') {
                                $processedLabel =   $processedBy;
                            } elseif ($b->booking_status === 'Canceled') {
                                $processedLabel = $processedBy;
                            } else {
                                $processedLabel = $processedBy;
                            }
                        }
                    @endphp
                    <td>{{ $processedLabel }}</td>
                    <td>{{ $b->created_at->format('M d, Y') }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('cargo.bookings.show', $b->booking_ref_no) }}" class="btn btn-sm btn-primary">View</a>
                        @if($b->booking_status === 'Pending')
                            <a href="{{ route('cargo.bookings.edit', $b->booking_ref_no) }}" class="btn btn-sm btn-warning">Edit</a>
                        @endif
                        @if($b->booking_status === 'Confirmed' && optional($b->payment)->payment_status === 'Pending')
                            <button class="btn btn-sm btn-success pay-btn" data-booking-ref="{{ $b->booking_ref_no }}">Pay Now</button>
                        @endif
                        @if($b->booking_status === 'Confirmed' && optional($b->payment)->payment_status === 'Initial')
                            <button class="btn btn-sm btn-success verify-btn" data-booking-ref="{{ $b->booking_ref_no }}">Verify</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No cargo bookings found for the selected filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container mt-3">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verificationModalLabel">Verify Cargo Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="verificationContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const verifyButtons = document.querySelectorAll('.verify-btn');
    const payButtons = document.querySelectorAll('.pay-btn');
    const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    const verificationContent = document.getElementById('verificationContent');
    const paymentContent = document.getElementById('paymentContent');

    verifyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingRef = this.getAttribute('data-booking-ref');
            
            // Load verification form
            fetch(`/authorized/staff/cargo-bookings/${bookingRef}/verify`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                }
            })
            .then(response => response.text())
            .then(html => {
                verificationContent.innerHTML = html;
                verificationModal.show();
                // Initialize dynamic total calculation after content is loaded
                initializeVerificationForm();
            })
            .catch(error => {
                console.error('Error loading verification form:', error);
                showToast('Error loading verification form', 'danger');
            });
        });
    });

    payButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingRef = this.getAttribute('data-booking-ref');
            
            // Load payment form
            fetch(`/authorized/staff/cargo-bookings/${bookingRef}/pay`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                }
            })
            .then(response => response.text())
            .then(html => {
                paymentContent.innerHTML = html;
                paymentModal.show();
                // Initialize payment form after content is loaded
                initializePaymentForm();
            })
            .catch(error => {
                console.error('Error loading payment form:', error);
                showToast('Error loading payment form', 'danger');
            });
        });
    });

    // Function to initialize verification form after AJAX load
    function initializeVerificationForm() {
        const arrastreInput = document.getElementById('arrastre');
        const totalAmountSpan = document.getElementById('totalAmount');
        
        if (arrastreInput && totalAmountSpan) {
            // Get initial amount from the span's current text
            const initialAmountText = totalAmountSpan.textContent.replace(/[₱,]/g, '');
            const initialAmount = parseFloat(initialAmountText) || 0;
            
            arrastreInput.addEventListener('input', function() {
                const arrastre = parseFloat(this.value) || 0;
                const total = initialAmount + arrastre;
                totalAmountSpan.textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        }

        // Handle submit verification button
        const submitBtn = document.querySelector('#verificationContent .btn-success');
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                submitVerification();
            });
        }

        // Handle cancel booking button
        const cancelBtn = document.querySelector('#verificationContent .btn-danger');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                cancelBooking();
            });
        }
    }

    // Function to handle verification submission
    function submitVerification() {
        const form = document.getElementById('verificationForm');
        if (!form) return;
        
        const formData = new FormData(form);
        formData.append('action', 'verify');
        
        // Ensure arrastre is always sent as a number (default to 0 if empty)
        const arrastreValue = formData.get('arrastre') || '0';
        formData.set('arrastre', arrastreValue);

        const bookingRef = document.querySelector('.verify-btn[style*="display: block"]')?.getAttribute('data-booking-ref') || 
                          document.querySelector('.verify-btn:not([style*="display: none"])')?.getAttribute('data-booking-ref');
        
        if (!bookingRef) {
            showToast('Unable to determine booking reference', 'danger');
            return;
        }

        fetch(`/authorized/staff/cargo-bookings/${bookingRef}/verify`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Payment verified successfully!', 'success');
                verificationModal.hide();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while processing the verification.', 'danger');
        });
    }

    // Function to handle booking cancellation
    function cancelBooking() {
        const bookingRef = document.querySelector('.verify-btn[style*="display: block"]')?.getAttribute('data-booking-ref') || 
                          document.querySelector('.verify-btn:not([style*="display: none"])')?.getAttribute('data-booking-ref');
        
        if (!bookingRef) {
            showToast('Unable to determine booking reference', 'danger');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to cancel this booking? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('verificationForm');
                const formData = new FormData(form);
                formData.append('action', 'cancel');

                fetch(`/authorized/staff/cargo-bookings/${bookingRef}/verify`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Booking has been canceled.', 'success');
                        verificationModal.hide();
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while canceling the booking.', 'danger');
                });
            }
        });
    }

    // Function to initialize payment form after AJAX load
    function initializePaymentForm() {
        // Handle payment buttons
        const cashBtn = document.querySelector('#paymentContent .btn-outline-primary');
        const gcashBtn = document.querySelector('#paymentContent .btn-outline-success');
        
        if (cashBtn) {
            cashBtn.addEventListener('click', function() {
                processPayment('cash');
            });
        }
        
        if (gcashBtn) {
            gcashBtn.addEventListener('click', function() {
                processPayment('gcash');
            });
        }
    }

    // Function to handle payment processing
    function processPayment(method) {
        const bookingRef = document.querySelector('.pay-btn[style*="display: block"]')?.getAttribute('data-booking-ref') || 
                          document.querySelector('.pay-btn:not([style*="display: none"])')?.getAttribute('data-booking-ref');
        
        if (!bookingRef) {
            showToast('Unable to determine booking reference', 'danger');
            return;
        }

        Swal.fire({
            title: 'Confirm Payment',
            text: `Process payment via ${method.toUpperCase()}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, process payment'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('paymentForm');
                const formData = new FormData(form);
                formData.append('payment_method', method);

                fetch(`/authorized/staff/cargo-bookings/${bookingRef}/process-payment`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`Payment processed successfully via ${method.toUpperCase()}!`, 'success');
                        paymentModal.hide();
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while processing payment.', 'danger');
                });
            }
        });
    }
});
</script>
@endsection
