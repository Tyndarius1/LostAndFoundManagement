<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Item Released Successfully</title>
</head>
<body>
    <h2>Item Released Successfully</h2>

    <p>Hello {{ $claimRequest->claimant->name }},</p>

    <p>Your claimed item has been successfully released to you.</p>

    <p><strong>Item:</strong> {{ $claimRequest->foundItem->item_name }}</p>
    <p><strong>Reference Code:</strong> {{ $claimRequest->foundItem->reference_code }}</p>
    <p><strong>Status:</strong> {{ ucfirst($claimRequest->status) }}</p>

    <p>Thank you for using the Lost and Found Management System.</p>
</body>
</html>