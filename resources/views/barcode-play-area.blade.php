<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Area Barcode</title>
    <script src="/js/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 16px;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            max-width: 420px;
        }

        .label {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .barcode-wrap {
            margin-top: 14px;
            text-align: center;
        }

        .print-btn {
            margin-top: 16px;
            border: 0;
            background: #1d4ed8;
            color: #fff;
            border-radius: 6px;
            padding: 8px 14px;
            cursor: pointer;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="label">Play Area Entry Barcode</div>
        <div class="row"><strong>Package:</strong> {{ $session->package->name ?? '-' }}</div>
        <div class="row"><strong>Customer:</strong> {{ $session->customer_name ?? '-' }}</div>
        <div class="row"><strong>Start Time:</strong> {{ $session->start_time }}</div>
        <div class="row"><strong>Valid Base Time:</strong> {{ $session->base_time_minutes }} mins</div>

        <div class="barcode-wrap">
            <svg id="barcode"></svg>
            <div style="margin-top: 6px; font-weight: 700;">{{ $session->barcode }}</div>
        </div>

        <button class="print-btn" onclick="window.print()">Print Barcode</button>
    </div>

    <script>
        JsBarcode('#barcode', '{{ $session->barcode }}', {
            format: 'CODE128',
            width: 2,
            height: 70,
            displayValue: false,
            margin: 5
        });
    </script>
</body>

</html>
