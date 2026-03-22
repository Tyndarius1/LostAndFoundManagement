<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lost Item Report</title>
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
        <h2>Lost Item Report</h2>

        <p><span class="label">Reported By:</span> {{ $lostItem->user->name ?? 'N/A' }}</p>
        <p><span class="label">Email:</span> {{ $lostItem->user->email ?? 'N/A' }}</p>
        <p><span class="label">Item Name:</span> {{ $lostItem->item_name }}</p>
        <p><span class="label">Category:</span> {{ $lostItem->category?->name ?? 'N/A' }}</p>
        <p><span class="label">Description:</span> {{ $lostItem->description }}</p>
        <p><span class="label">Color:</span> {{ $lostItem->color ?? 'N/A' }}</p>
        <p><span class="label">Last Seen Location:</span> {{ $lostItem->last_seen_location }}</p>
        <p><span class="label">Date Lost:</span> {{ $lostItem->date_lost?->format('Y-m-d') }}</p>
        <p><span class="label">Status:</span> {{ ucfirst($lostItem->status) }}</p>

        @if($lostItem->image_path)
            <div>
                <p><span class="label">Item Image:</span></p>
                <img src="{{ asset('storage/' . $lostItem->image_path) }}" alt="Lost Item Image">
            </div>
        @endif
    </div>
</body>
</html>
