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

    <p>Please wait for release instructions from the admin or office staff.</p>

    <p>Thank you.</p>
</body>
</html>