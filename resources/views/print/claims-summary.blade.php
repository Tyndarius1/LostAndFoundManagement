<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claims Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
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

        th, td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 14px;
            text-align: left;
        }

        h1, h2 {
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print</button>

    <h1>Lost and Found Management System</h1>
    <h2>Claims Summary Report</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Claimant</th>
                <th>Item Name</th>
                <th>Reference Code</th>
                <th>Status</th>
                <th>Approver</th>
                <th>Approved At</th>
                <th>Released At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($claimRequests as $claimRequest)
                <tr>
                    <td>{{ $claimRequest->id }}</td>
                    <td>{{ $claimRequest->claimant->name ?? 'N/A' }}</td>
                    <td>{{ $claimRequest->foundItem->item_name ?? 'N/A' }}</td>
                    <td>{{ $claimRequest->foundItem->reference_code ?? 'N/A' }}</td>
                    <td>{{ ucfirst($claimRequest->status) }}</td>
                    <td>{{ $claimRequest->approver->name ?? 'N/A' }}</td>
                    <td>{{ $claimRequest->approved_at?->format('Y-m-d h:i A') ?? 'N/A' }}</td>
                    <td>{{ $claimRequest->released_at?->format('Y-m-d h:i A') ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No claim records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
