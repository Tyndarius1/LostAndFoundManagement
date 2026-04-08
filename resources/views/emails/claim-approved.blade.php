<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claim Request Approved</title>
</head>
<body>
    <h2>Claim Request Approved</h2>

    <p>Hello {{ $claimRequest->claimant->name }},</p>

    <p>Your claim request has been approved.</p>

    <p><strong>Item:</strong> {{ $claimRequest->foundItem->item_name }}</p>
    <p><strong>Reference Code:</strong> {{ $claimRequest->foundItem->reference_code }}</p>
    <p><strong>Status:</strong> {{ ucfirst($claimRequest->status) }}</p>

    @if($claimRequest->pickup_code)
        <p><strong>Pickup Code:</strong> {{ $claimRequest->pickup_code }}</p>
        @if($claimRequest->pickup_code_expires_at)
            <p><strong>Code Expiry:</strong> {{ $claimRequest->pickup_code_expires_at->format('Y-m-d h:i A') }}</p>
        @endif
    @endif

    <p>Please bring the pickup code and a valid ID when collecting the item.</p>

    <p>Thank you.</p>
</body>
</html>