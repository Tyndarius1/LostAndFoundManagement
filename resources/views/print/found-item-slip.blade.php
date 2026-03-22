<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Found Item Slip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .label {
            font-weight: bold;
        }

        .print-btn {
            margin-bottom: 20px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                margin: 0;
            }
        }

        img {
            max-width: 220px;
            margin-top: 10px;
            border: 1px solid #ccc;
            padding: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="print-btn" onclick="window.print()">Print</button>

        <h1>Lost and Found Management System</h1>
        <h2>Found Item Slip</h2>

        <p><span class="label">Reference Code:</span> {{ $foundItem->reference_code }}</p>
        <p><span class="label">Item Name:</span> {{ $foundItem->item_name }}</p>
        <p><span class="label">Category:</span> {{ $foundItem->category?->name ?? 'N/A' }}</p>
        <p><span class="label">Description:</span> {{ $foundItem->description }}</p>
        <p><span class="label">Color:</span> {{ $foundItem->color ?? 'N/A' }}</p>
        <p><span class="label">Found Location:</span> {{ $foundItem->found_location }}</p>
        <p><span class="label">Date Found:</span> {{ $foundItem->date_found?->format('Y-m-d') }}</p>
        <p><span class="label">Storage Location:</span> {{ $foundItem->storage_location }}</p>
        <p><span class="label">Encoded By:</span> {{ $foundItem->staff?->name ?? 'N/A' }}</p>
        <p><span class="label">Status:</span> {{ ucfirst($foundItem->status) }}</p>

        @if($foundItem->image_path)
            <div>
                <p><span class="label">Item Image:</span></p>
                <img src="{{ asset('storage/' . $foundItem->image_path) }}" alt="Found Item Image">
            </div>
        @endif

        @if($foundItem->qr_code_path)
            <div>
                <p><span class="label">QR Code:</span></p>
                <img src="{{ asset('storage/' . $foundItem->qr_code_path) }}" alt="QR Code">
            </div>
        @endif
    </div>
</body>
</html>
