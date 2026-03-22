<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claim Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #000;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1, h2, h3 {
            margin-bottom: 10px;
        }

        .section {
            margin-bottom: 25px;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="print-btn" onclick="window.print()">Print</button>

        <h1>Lost and Found Management System</h1>
        <h2>Claim Receipt</h2>

        <div class="section">
            <p><span class="label">Claim ID:</span> {{ $claimRequest->id }}</p>
            <p><span class="label">Status:</span> {{ ucfirst($claimRequest->status) }}</p>
            <p><span class="label">Date Submitted:</span> {{ $claimRequest->created_at?->format('Y-m-d h:i A') }}</p>
        </div>

        <div class="section">
            <h3>Claimant Information</h3>
            <p><span class="label">Name:</span> {{ $claimRequest->claimant->name }}</p>
            <p><span class="label">Email:</span> {{ $claimRequest->claimant->email }}</p>
        </div>

        <div class="section">
            <h3>Found Item Information</h3>
            <p><span class="label">Item Name:</span> {{ $claimRequest->foundItem->item_name }}</p>
            <p><span class="label">Category:</span> {{ $claimRequest->foundItem->category?->name ?? 'N/A' }}</p>
            <p><span class="label">Reference Code:</span> {{ $claimRequest->foundItem->reference_code }}</p>
            <p><span class="label">Found Location:</span> {{ $claimRequest->foundItem->found_location }}</p>
            <p><span class="label">Storage Location:</span> {{ $claimRequest->foundItem->storage_location }}</p>
        </div>

        <div class="section">
            <h3>Proof Details</h3>
            <p>{{ $claimRequest->proof_details }}</p>
        </div>

        <div class="section">
            <h3>Approval Information</h3>
            <p><span class="label">Approved By:</span> {{ $claimRequest->approver->name ?? 'N/A' }}</p>
            <p><span class="label">Approved At:</span> {{ $claimRequest->approved_at?->format('Y-m-d h:i A') ?? 'N/A' }}</p>
            <p><span class="label">Released At:</span> {{ $claimRequest->released_at?->format('Y-m-d h:i A') ?? 'N/A' }}</p>
        </div>
    </div>
</body>
</html>
