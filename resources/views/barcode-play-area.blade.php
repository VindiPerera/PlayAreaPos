<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Entrance Receipt - {{ $session->barcode }}</title>
    <script src="/js/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            color: #000;
            font-size: 12px;
        }
        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 6mm 4mm 8mm 4mm;
        }
        /* Title */
        .title-section {
            text-align: center;
            margin-bottom: 8px;
        }
        .title-section .receipt-type {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .title-section .bill-number {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .title-section .bill-date {
            font-size: 11px;
        }
        /* Dashed divider */
        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 7px 0;
        }
        /* Section header */
        .section-header {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        /* Customer box */
        .customer-box {
            border: 1px solid #000;
            padding: 5px 7px;
        }
        .customer-box .cust-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .customer-box .cust-tel {
            font-size: 11px;
        }
        /* Fee detail rows */
        .fee-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 4px;
        }
        /* Pricing tier box */
        .tier-box {
            border: 1px solid #000;
            padding: 5px 8px;
            margin-top: 6px;
        }
        .tier-box .tier-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 4px;
        }
        .tier-box .tier-item {
            font-size: 10px;
            margin-bottom: 2px;
        }
        /* People section */
        .people-row {
            text-align: center;
            font-size: 11px;
            padding: 4px 0;
        }
        /* Base entrance fee */
        .base-fee-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: bold;
            padding: 5px 0;
        }
        /* Barcode */
        .barcode-section {
            text-align: center;
            padding: 4px 0;
        }
        .barcode-section svg { width: 100%; max-width: 88mm; }
        .barcode-section .barcode-text {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 2px;
        }
        /* Footer */
        .footer {
            text-align: center;
            font-size: 10px;
            padding-top: 6px;
        }
        .footer p { margin-bottom: 3px; }
        @media print {
        @page {
            margin: 0;
        }
        @media print {
            html, body { width: 80mm; }
            .receipt { padding: 4mm 2mm 5mm 2mm; }
        }
    </style>
</head>
<body>
<div class="receipt">

    <!-- Title -->
    <div class="title-section">
        <div class="receipt-type">ENTRANCE RECEIPT</div>
        <div class="bill-number">{{ $session->barcode }}</div>
        <div class="bill-date">{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('n/j/Y, g:i:s A') : now()->format('n/j/Y, g:i:s A') }}</div>
    </div>

    <hr class="divider">

    <!-- Customer Details -->
    <div class="section-header">CUSTOMER DETAILS</div>
    <div class="customer-box">
        <div class="cust-name">{{ $session->customer_name ?? 'Walk-in' }}</div>
        @if($session->customer_contact)
        <div class="cust-tel">Tel: {{ $session->customer_contact }}</div>
        @endif
    </div>

    <hr class="divider">

    <!-- Entrance Fee Details -->
    <div class="section-header">ENTRANCE FEE DETAILS</div>
    <div class="fee-row">
        <span>Started At</span>
        <span>{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('n/j/Y, g:i:s A') : '-' }}</span>
    </div>
    <div class="fee-row">
        <span>Base Duration</span>
        <span>{{ $session->base_time_minutes }} mins</span>
    </div>

    @if($session->package)
    <div class="tier-box">
        <div class="tier-title">Pricing Tiers ({{ ucfirst($session->package->type ?? 'Standard') }})</div>
        <div class="tier-item">&bull; Base: LKR {{ number_format($session->base_price, 2) }} for first {{ $session->base_time_minutes }} mins</div>
        @if($session->extra_charge && $session->extra_charge_per_minutes)
        <div class="tier-item">&bull; 1st Stage (Recurring): +LKR {{ number_format($session->extra_charge, 2) }} every {{ $session->extra_charge_per_minutes }} mins</div>
        @endif
    </div>
    @endif

    <hr class="divider">

    <!-- People -->
    <div class="section-header">PEOPLE</div>
    <hr class="divider">
    @php
        $qty = $session->package_quantity ?? 1;
        $pkgName = $session->package->name ?? 'Package';
        $basePrice = $session->base_price ?? 0;
    @endphp
    <div class="people-row">
        {{ $pkgName }} {{ $qty }} {{ $qty == 1 ? 'person' : 'persons' }} @ LKR {{ number_format($basePrice, 2) }} base
    </div>

    <hr class="divider">

    <!-- Base Entrance Fee -->
    <div class="base-fee-row">
        <span>BASE ENTRANCE FEE</span>
        <span>LKR {{ number_format($session->package_total, 2) }}</span>
    </div>

    <hr class="divider">

    <!-- Barcode -->
    <div class="barcode-section">
        <svg id="barcode"></svg>
        <div class="barcode-text">{{ $session->barcode }}</div>
    </div>

    <hr class="divider">

    <!-- Footer -->
    <div class="footer">
        <p>Timer is running. Additional charges may apply</p>
        <p>based on duration.</p>
        <p style="margin-top: 5px;">Final bill will be printed upon checkout.</p>
    </div>

</div>

<script>
    JsBarcode('#barcode', '{{ $session->barcode }}', {
        format: 'CODE128',
        width: 2,
        height: 80,
        displayValue: false,
        margin: 5
    });
    window.onload = function() { window.print(); };
</script>
</body>
</html>
