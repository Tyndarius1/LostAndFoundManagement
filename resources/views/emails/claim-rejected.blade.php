<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claim Request Rejected</title>
</head>
<body>
    <h2>Claim Request Rejected</h2>

    <p>Hello {{ $claimRequest->claimant->name }},</p>

    <p>We regret to inform you that your claim request has been rejected.</p>

    <p><strong>Item:</strong> {{ $claimRequest->foundItem->item_name }}</p>
    <p><strong>Reference Code:</strong> {{ $claimRequest->foundItem->reference_code }}</p>
    <p><strong>Status:</strong> {{ ucfirst($claimRequest->status) }}</p>

    <p>You may contact the office for further clarification if needed.</p>

    <p>Thank you.</p>
</body>
</html>