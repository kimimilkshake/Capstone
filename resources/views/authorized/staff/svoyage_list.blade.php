@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')
    <div class="staff-body">

        <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end;">
            <form class="search-bar" action="{{ route('staff.voyage_list') }}" method="GET"
                style="flex: 1; display: flex; gap: 10px; align-items: flex-end;">

                <!-- SEARCH -->
                <div style="flex: 2; display: flex; flex-direction: column;">
                    <label style="font-size: 12px;">Search</label>
                    <input type="text" name="search" placeholder="Search by name, code, route, vessel, status..."
                        value="{{ request('search') }}" style="height: 38px;">
                </div>

                <!-- FROM -->
                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 12px;">From</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" style="height: 38px;">
                </div>

                <!-- TO -->
                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 12px;">To</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" style="height: 38px;">
                </div>

                <!-- BUTTON -->
                <div>
                    <button type="submit" style="height: 38px; padding: 0 15px;">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
                    </button>
                </div>

            </form>

        </div>

        <table class="voyage-table">
            <thead>
                <tr>
                    <th>Voyage Code</th>
                    <th>Route</th>
                    <th>Departure Date</th>
                    <th>ETD</th>
                    <th>Arrival Date</th>
                    <th>ETA</th>
                    <th>PAX/CAP</th>
                    <th>Vessel</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($voyages as $voyage)
                    <tr>
                        <td>{{ $voyage->voyage_code }}</td>
                        <td>
                            {{ $voyage->routePort->route_origin }} →
                            {{ $voyage->routePort->route_destination }}
                        </td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y, D') }}</td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M j, Y, D') }}</td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
                        <td>{{ $voyage->passenger_tickets_count }}/{{ $voyage->vessel->vessel_total_passenger_capacity }}
                        </td>
                        <td>{{ $voyage->vessel->vessel_name }}</td>
                        <td>{{ $voyage->voyage_status }}</td>
                        <td>
                            @if ($voyage->voyage_status === 'At Sea')
                                <a href="#" class="link-btn disabled-voyage"
                                    style="opacity: 0.5; cursor: not-allowed;" title="Cannot edit while At Sea">
                                    <i class="fa fa-pencil me-1"></i>
                                </a>
                            @else
                                <a href="{{ route('staff.voyage_edit', $voyage->voyage_id) }}" class="link-btn">
                                    <i class="fa fa-pencil me-1"></i>
                                </a>
                            @endif
                            <a href="{{ route('staff.manifest', $voyage->voyage_id) }}" title="View Manifest"
                                class="editRouteBtn link-btn"><i class="fa-solid fa-file me-1"></i></a>
                            <button type="button" title="Send Message" class="editRouteBtn link-btn open-sms-modal"
                                data-voyage-id="{{ $voyage->voyage_id }}"
                                data-route-origin="{{ optional($voyage->routePort)->route_origin ?? '-' }}"
                                data-route-destination="{{ optional($voyage->routePort)->route_destination ?? '-' }}"
                                data-departure-date="{{ $voyage->voyage_departure_date }}"
                                data-etd="{{ $voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-' }}"
                                style="border:none; background:transparent; padding:0; cursor:pointer;">
                                <i class="fa-solid fa-message"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No voyages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination links --}}
        <div class="mt-3">
            {{ $voyages->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- SMS Modal Overlay --}}
    <div id="smsModalOverlay" class="sms-modal-overlay" aria-hidden="true">
        <div class="sms-modal-panel">
            <div class="sms-modal-header">
                <span>SMS Message for Cancellation of Trips</span>
                <button type="button" class="sms-modal-close" id="closeSmsModal" aria-label="Close">&times;</button>
            </div>
            <div class="sms-modal-body">
                <form method="POST" action="{{ route('staff.semaphore.send') }}" id="smsForm">
                    @csrf
                    <input type="hidden" name="voyage_id" id="smsVoyageId" value="">

                    <div class="sms-template-row">
                        <span class="sms-template-label">Quick Templates:</span>
                        <button type="button" class="sms-template-button template-typhoon" id="templateTyphoon"
                            title="Typhoon Template" aria-label="Use typhoon template"><i
                                class="fas fa-cloud-showers-heavy"></i></button>
                        <button type="button" class="sms-template-button template-technical" id="templateTechnical"
                            title="Technical Template" aria-label="Use technical issue template"><i
                                class="fas fa-wrench"></i></button>
                    </div>

    .sms-modal-panel {
      position: relative;
      width: min(100%, 650px);
      max-height: 90vh;
      background: #f9f9f9;
      border: 1px solid #ccc;
      border-radius: 12px;
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
      transform: scale(0git.85) translateY(30px);
      opacity: 0;
      transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
      overflow: hidden;
    }

            {{-- Confirm dialog --}}
            <div id="smsConfirmModal" class="confirm-dialog-overlay" aria-hidden="true">
                <div class="confirm-dialog-panel" role="dialog" aria-modal="true" aria-labelledby="smsConfirmModalLabel">
                    <div class="confirm-dialog-header">
                        <h5 class="confirm-dialog-title" id="smsConfirmModalLabel">Confirm Message Send</h5>
                        <button type="button" class="confirm-dialog-close" id="closeSmsConfirmModal"
                            aria-label="Close">&times;</button>
                    </div>
                    <div class="confirm-dialog-body">
                        Are you sure you want to send this message to all selected recipients?
                    </div>
                    <div class="confirm-dialog-footer">
                        <button type="button" class="btn" id="cancelSmsSendButton"
                            style="background: #dc3545; color: #ffffff;">Cancel</button>
                        <button type="button" class="btn" id="confirmSmsSendButton"
                            style="background: #1E2541; color: #ffffff;">Send Message</button>
                    </div>
                </div>
            </div>

            {{-- Loading overlay --}}
            <div id="smsLoadingOverlay" class="confirm-dialog-overlay" aria-hidden="true">
                <div class="confirm-dialog-panel loading-dialog-panel" role="status" aria-live="polite">
                    <div class="loading-spinner" aria-hidden="true"></div>
                    <div class="confirm-dialog-title">Sending messages...</div>
                    <div class="loading-text">Please wait while the message is being queued for all recipients.</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .sms-modal-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0);
            z-index: 2500;
            padding: 16px;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: background 0.3s ease, opacity 0.3s ease, visibility 0.3s ease;
        }

        .sms-modal-overlay.is-open {
            background: rgba(15, 23, 42, 0.45);
            pointer-events: auto;
            opacity: 1;
            visibility: visible;
        }

        .sms-modal-panel {
            position: relative;
            width: min(100%, 650px);
            max-height: 90vh;
            overflow-y: auto;
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
            transform: scale(0.85) translateY(30px);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
        }

        .sms-modal-overlay.is-open .sms-modal-panel {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .sms-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background-color: #485b8c;
            color: white;
            font-size: 20px;
            font-weight: bold;
            border-radius: 12px 12px 0 0;
        }

        .sms-modal-close {
            border: none;
            background: transparent;
            color: white;
            font-size: 1.6rem;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .sms-modal-close:hover {
            opacity: 0.7;
        }

        .sms-modal-body {
            padding: 20px 30px 24px;
        }

        .sms-modal-body .sms-template-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .sms-modal-body .sms-template-label {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #485b8c;
        }

        .sms-modal-body .sms-template-button {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid #d9dee8;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            padding: 0;
        }

        .sms-modal-body .sms-template-button i {
            font-size: 0.9rem;
        }

        .sms-modal-body .sms-template-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.12);
            background: #ffffff;
        }

        .sms-modal-body .sms-template-button:focus-visible {
            outline: 2px solid #1E2541;
            outline-offset: 2px;
        }

        .sms-modal-body .sms-template-button.template-typhoon {
            color: #d97706 !important;
            border-color: rgba(217, 119, 6, 0.28);
            background: linear-gradient(180deg, #fff8eb 0%, #ffffff 100%) !important;
        }

        .sms-modal-body .sms-template-button.template-technical {
            color: #2563eb !important;
            border-color: rgba(37, 99, 235, 0.28);
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%) !important;
        }

        .confirm-dialog-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.35);
            z-index: 3000;
            padding: 16px;
        }

        .confirm-dialog-overlay.is-open {
            display: flex;
        }

        .confirm-dialog-panel {
            width: min(100%, 480px);
            background: #ffffff;
            color: inherit;
            border: 1px solid #d9dee8;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
            pointer-events: auto;
        }

        .confirm-dialog-header,
        .confirm-dialog-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
        }

        .confirm-dialog-header {
            border-bottom: 1px solid #d9dee8;
        }

        .confirm-dialog-footer {
            border-top: 1px solid #d9dee8;
            justify-content: flex-end;
        }

        .confirm-dialog-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .confirm-dialog-body {
            padding: 20px;
            line-height: 1.5;
        }

        .confirm-dialog-close {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 1.6rem;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .loading-dialog-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            text-align: center;
            padding: 28px 24px;
        }

        .loading-spinner {
            width: 44px;
            height: 44px;
            border: 4px solid #d9dee8;
            border-top-color: #1E2541;
            border-radius: 50%;
            animation: semaphore-spin 0.85s linear infinite;
        }

        .loading-text {
            color: #4b5563;
            line-height: 1.5;
        }

        @keyframes semaphore-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const smsModalOverlay = document.getElementById('smsModalOverlay');
            const closeSmsModal = document.getElementById('closeSmsModal');
            const smsVoyageId = document.getElementById('smsVoyageId');
            const messageTextarea = document.getElementById('message');
            const templateTyphoon = document.getElementById('templateTyphoon');
            const templateTechnical = document.getElementById('templateTechnical');

            let currentVoyageData = {};

            function openSmsModal(btn) {
                currentVoyageData = {
                    voyageId: btn.dataset.voyageId,
                    origin: btn.dataset.routeOrigin,
                    destination: btn.dataset.routeDestination,
                    departureDate: btn.dataset.departureDate,
                    etd: btn.dataset.etd
                };
                smsVoyageId.value = currentVoyageData.voyageId;
                messageTextarea.value = '';
                smsModalOverlay.classList.add('is-open');
                smsModalOverlay.setAttribute('aria-hidden', 'false');
            }

            function closeSmsModalFn() {
                smsModalOverlay.classList.remove('is-open');
                smsModalOverlay.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('.open-sms-modal').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openSmsModal(this);
                });
            });

            closeSmsModal.addEventListener('click', closeSmsModalFn);

            smsModalOverlay.addEventListener('click', function(e) {
                if (e.target === smsModalOverlay) {
                    closeSmsModalFn();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && smsModalOverlay.classList.contains('is-open')) {
                    closeSmsModalFn();
                }
            });

            // Templates
            templateTyphoon.addEventListener('click', function() {
                var text =
                    "Dear Valued Passengers and Cargo Senders,\n\nDue to the impending arrival of Typhoon [Name] and the corresponding safety advisories issued by local authorities, we regret to inform you that the voyage from " +
                    currentVoyageData.origin + " to " + currentVoyageData.destination + " scheduled for " +
                    currentVoyageData.departureDate + " at " + currentVoyageData.etd +
                    " has been cancelled in the interest of ensuring the safety and well-being of our passengers, cargo, and staff.";
                messageTextarea.value = text;
                messageTextarea.focus();
            });

            templateTechnical.addEventListener('click', function() {
                var text =
                    "Dear Valued Passengers and Cargo Senders,\n\nDue to an unexpected technical issue affecting our vessel, we regret to inform you that the voyage from " +
                    currentVoyageData.origin + " to " + currentVoyageData.destination + " scheduled for " +
                    currentVoyageData.departureDate + " at " + currentVoyageData.etd +
                    " has been cancelled until the issue is fully resolved. We sincerely apologize for any inconvenience caused and will provide updates as they become available.";
                messageTextarea.value = text;
                messageTextarea.focus();
            });

            // Form submission with confirm dialog
            const smsForm = document.getElementById('smsForm');
            const smsConfirmModalElement = document.getElementById('smsConfirmModal');
            const confirmSmsSendButton = document.getElementById('confirmSmsSendButton');
            const cancelSmsSendButton = document.getElementById('cancelSmsSendButton');
            const closeSmsConfirmModal = document.getElementById('closeSmsConfirmModal');
            const smsLoadingOverlay = document.getElementById('smsLoadingOverlay');

            function showSmsConfirmModal() {
                smsConfirmModalElement.classList.add('is-open');
                smsConfirmModalElement.setAttribute('aria-hidden', 'false');
            }

            function hideSmsConfirmModal() {
                smsConfirmModalElement.classList.remove('is-open');
                smsConfirmModalElement.setAttribute('aria-hidden', 'true');
            }

            function showSmsLoadingOverlay() {
                smsLoadingOverlay.classList.add('is-open');
                smsLoadingOverlay.setAttribute('aria-hidden', 'false');
                confirmSmsSendButton.disabled = true;
                cancelSmsSendButton.disabled = true;
                closeSmsConfirmModal.disabled = true;
            }

            function showSemaphoreError(message) {
                if (typeof showToast === 'function') {
                    showToast(message, 'danger');
                    return;
                }
                alert(message);
            }

            smsForm.addEventListener('submit', function(e) {
                var msg = messageTextarea.value.trim();
                if (!msg) {
                    e.preventDefault();
                    showSemaphoreError('Message cannot be empty.');
                    return;
                }
                if (smsForm.dataset.confirmed === 'true') {
                    delete smsForm.dataset.confirmed;
                    return;
                }
                e.preventDefault();
                showSmsConfirmModal();
            });

            confirmSmsSendButton.addEventListener('click', function() {
                smsForm.dataset.confirmed = 'true';
                hideSmsConfirmModal();
                showSmsLoadingOverlay();
                if (typeof smsForm.requestSubmit === 'function') {
                    smsForm.requestSubmit();
                    return;
                }
                smsForm.submit();
            });

            cancelSmsSendButton.addEventListener('click', hideSmsConfirmModal);
            closeSmsConfirmModal.addEventListener('click', hideSmsConfirmModal);

            smsConfirmModalElement.addEventListener('click', function(e) {
                if (e.target === smsConfirmModalElement) {
                    hideSmsConfirmModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && smsConfirmModalElement.classList.contains('is-open')) {
                    hideSmsConfirmModal();
                }
            });
        });
    </script>

@endsection
