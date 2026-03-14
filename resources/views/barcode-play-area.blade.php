<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Play Area Receipt - {{ $session->barcode }}</title>
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
            min-height: 100mm;
            margin: 0 auto;
            padding: 6mm 5mm 8mm 5mm;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header img {
            width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 4px auto;
        }
        .header .shop-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }
        .header .phone {
            font-size: 11px;
        }
        .title-section {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .title-section .bill-type {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .title-section .bill-number {
            font-size: 13px;
            font-weight: bold;
        }
        .title-section .bill-date {
            font-size: 10px;
            color: #444;
            margin-top: 2px;
        }
        .info-section {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }
        .info-row .label { font-weight: bold; }
        .package-section {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 10px;
            text-align: center;
        }
        .package-section .pkg-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 2px;
        }
        .package-section .pkg-detail {
            font-size: 10px;
            color: #555;
        }
        .barcode-section {
            text-align: center;
            padding: 6px 0 4px 0;
            margin-bottom: 6px;
        }
        .barcode-section svg { width: 100%; max-width: 90mm; }
        .barcode-section .barcode-text {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 2px;
        }
        .footer {
            border-top: 1px dashed #000;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #444;
            margin-top: 8px;
        }
        .footer p { margin-bottom: 3px; }
        @media print {
            html, body { width: 80mm; }
            .receipt { padding: 0 2mm 5mm 2mm; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <!-- Header -->
    <div class="header">
        <img src="/images/logo.png" alt="Logo" />
        <div class="shop-name">{{ $company->name ?? 'PLAY AREA' }}</div>
        <div class="phone">Tel : {{ $company->phone ?? '077 306 3000' }}</div>
    </div>

    <!-- Title -->
    <div class="title-section">
        <div class="bill-type">ENTRY BARCODE</div>
        <div class="bill-number">{{ $session->barcode }}</div>
        <div class="bill-date">{{ now()->format('d/m/Y, h:i A') }}</div>
    </div>

    <!-- Customer & session info -->
    <div class="info-section">
        @if($session->customer_name)
        <div class="info-row">
            <span class="label">Customer</span>
            <span>{{ $session->customer_name }}</span>
        </div>
        @endif
        @if($session->customer_contact)
        <div class="info-row">
            <span class="label">Contact</span>
            <span>{{ $session->customer_contact }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="label">Start Time</span>
            <span>{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : '-' }}</span>
        </div>
    </div>

    <!-- Package details -->
    @if($session->package)
    <div class="package-section">
        <div class="pkg-name">{{ $session->package->name }}</div>
        <div class="pkg-detail">
            Duration: {{ $session->base_time_minutes }} mins &nbsp;|&nbsp;
            LKR {{ number_format($session->package_total, 2) }}
        </div>
    </div>
    @endif

    <!-- Barcode -->
    <div class="barcode-section">
        <svg id="barcode"></svg>
        <div class="barcode-text">{{ $session->barcode }}</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>We hope you enjoy your play time!</p>
        <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
    </div>
</div>

<script>
    JsBarcode('#barcode', '{{ $session->barcode }}', {
        format: 'CODE128',
        width: 3,
        height: 80,
        displayValue: false,
        margin: 5
    });
    window.onload = function() { window.print(); };
</script>
</body>
</html>
