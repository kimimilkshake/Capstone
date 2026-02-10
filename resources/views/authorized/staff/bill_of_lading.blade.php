<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill of Lading - {{ $booking->booking_ref_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: white;
            padding: 20px;
        }

        .page {
            width: 8.5in;
            height: 11in;
            margin: 0 auto 20px;
            background: white;
            border: 1px solid #ccc;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .company-info {
            font-size: 11px;
            line-height: 1.3;
            color: #333;
        }

        .header .lslc {
            font-size: 9px;
            margin-top: 5px;
            color: #666;
        }

        .section-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .section-row.full {
            grid-template-columns: 1fr;
        }

        .section-box {
            border: 1px solid #333;
            padding: 10px;
        }

        .section-label {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
        }

        .section-content {
            font-size: 10px;
            line-height: 1.5;
        }

        .section-content p {
            margin: 3px 0;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 10px;
        }

        .info-item {
            font-size: 10px;
        }

        .info-item label {
            font-weight: bold;
        }

        .info-item span {
            display: block;
            margin-left: 10px;
            border-bottom: 1px dotted #999;
            min-height: 16px;
        }

        .cargo-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }

        .cargo-table th {
            background: #f0f0f0;
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        .cargo-table td {
            border: 1px solid #333;
            padding: 8px;
        }

        .cargo-description {
            width: 100%;
            margin: 15px 0;
            border: 1px solid #333;
        }

        .cargo-description-header {
            background: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #333;
        }

        .cargo-items {
            padding: 10px;
            min-height: 150px;
        }

        .cargo-item {
            margin-bottom: 8px;
            font-size: 10px;
            line-height: 1.4;
        }

        .cargo-item strong {
            display: block;
        }

        .footer-section {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 10px;
        }

        .signature-block {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .signature-line {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9px;
        }

        .signature-line .label {
            margin-top: 30px;
        }

        .charges-table {
            width: 100%;
            border: 1px solid #333;
            margin-top: 15px;
        }

        .charges-table tr {
            border-bottom: 1px solid #333;
        }

        .charges-table td {
            padding: 8px;
            font-size: 10px;
        }

        .charges-table .label {
            font-weight: bold;
            width: 150px;
        }

        .charges-table .amount {
            text-align: right;
            width: 80px;
            border-left: 1px solid #333;
        }

        .notes {
            font-size: 9px;
            margin-top: 10px;
            font-style: italic;
            line-height: 1.3;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .page {
                margin: 0;
                border: none;
                box-shadow: none;
                padding: 40px;
                page-break-after: always;
                height: auto;
            }

            .no-print {
                display: none;
            }
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .no-print button {
            padding: 10px 20px;
            font-size: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
        }

        .no-print button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ Print</button>
    <button onclick="window.history.back()">← Back</button>
</div>

<div class="page">
    <!-- Header -->
    <div class="header">
        <h1>LAPULAPU SHIPPING LINES CORPORATION</h1>
        <div class="company-info">
            TEL. 232-8864, 232-8865 • TIN: 200-008-128-099 • VAT
        </div>
        <div class="lslc">LSLC # 0900189760</div>
    </div>

    <!-- Top Info Section -->
    <div class="info-row">
        <div class="info-item">
            <label>Vessel:</label>
            <span>{{ $booking->voyage->vessel_name ?? 'Not specified' }}</span>
        </div>
        <div class="info-item">
            <label>B/L No.:</label>
            <span>{{ $booking->booking_ref_no }}</span>
        </div>
    </div>

    <div class="info-row">
        <div class="info-item">
            <label>Voyage No.:</label>
            <span>{{ $booking->voyage->voyage_code ?? 'N/A' }}</span>
        </div>
        <div class="info-item">
            <label>Sailing Date:</label>
            <span>{{ $booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('F d, Y') : 'N/A' }}</span>
        </div>
    </div>

    <!-- Shipper & Consignee -->
    <div class="section-row">
        <div class="section-box">
            <div class="section-label">SHIPPER</div>
            <div class="section-content">
                <p><strong>{{ $booking->sender->sender_name ?? '' }}</strong></p>
                <p>{{ $booking->sender->sender_contactno ?? '' }}</p>
                <p>{{ $booking->sender->sender_email ?? '' }}</p>
            </div>
        </div>
        <div class="section-box">
            <div class="section-label">CONSIGNEE</div>
            <div class="section-content">
                <p><strong>{{ $booking->consignee->consignee_name ?? '' }}</strong></p>
                <p>{{ $booking->consignee->consignee_contactno ?? '' }}</p>
            </div>
        </div>
    </div>

    <!-- Ports -->
    <div class="section-row">
        <div class="section-box">
            <div class="section-label">Loading Port</div>
            <div class="section-content">
                <p>{{ $booking->voyage->loading_port ?? 'Not specified' }}</p>
            </div>
        </div>
        <div class="section-box">
            <div class="section-label">Unloading Port</div>
            <div class="section-content">
                <p>{{ $booking->voyage->unloading_port ?? 'Not specified' }}</p>
            </div>
        </div>
    </div>

    <!-- Cargo Description Table -->
    <div style="margin-top: 15px; border: 1px solid #333;">
        <div style="background: #f0f0f0; padding: 8px; font-weight: bold; font-size: 11px; border-bottom: 1px solid #333;">DESCRIPTION OF ARTICLES</div>
        <table class="cargo-table" style="margin: 0; border-top: none;">
            <thead>
                <tr>
                    <th style="width: 10%;">QTY</th>
                    <th style="width: 20%;">Classification</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 25%;">Dimensions (cm)</th>
                    <th style="width: 20%;">Weight (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->cargoBookings as $cargo)
                    <tr>
                        <td>{{ $cargo->quantity }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_classification ?? '' }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_description ?? '' }}</td>
                        <td>{{ $cargo->length }} × {{ $cargo->width }} × {{ $cargo->height }}</td>
                        <td>{{ $cargo->weight }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Charges Table -->
    <table class="charges-table">
        <tr>
            <td class="label">FREIGHT CHARGES</td>
            <td class="amount">₱__________</td>
        </tr>
        <tr>
            <td class="label">Tax</td>
            <td class="amount">₱__________</td>
        </tr>
        <tr>
            <td class="label">Stamp</td>
            <td class="amount">₱__________</td>
        </tr>
        <tr>
            <td class="label"><strong>Total</strong></td>
            <td class="amount"><strong>₱__________</strong></td>
        </tr>
    </table>

    <!-- Signature Section -->
    <div class="signature-block">
        <div class="signature-line">
            <div style="height: 50px;"></div>
            <div class="label">Shipper/Agent</div>
        </div>
        <div class="signature-line">
            <div style="height: 50px;"></div>
            <div class="label">Carrier's Agent</div>
        </div>
        <div class="signature-line">
            <div style="height: 50px;"></div>
            <div class="label">Quartermaster</div>
        </div>
    </div>

    <!-- Footer Notes -->
    <div class="notes">
        <p>Received the merchandise specified herein in good order and condition.</p>
        <p style="margin-top: 10px;">SUBJECT TO CONDITIONS & QUOTATIONS WHETHER PRINTED OR STAMPED OR WRITTEN ON THE ORIGINAL OF THIS BILL OF LADING.</p>
    </div>

</div>

</body>
</html>
