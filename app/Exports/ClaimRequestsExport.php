<?php

namespace App\Exports;

use App\Models\ClaimRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimRequestsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return ClaimRequest::with(['claimant', 'foundItem', 'approver'])->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Claimant',
            'Found Item',
            'Reference Code',
            'Approver',
            'Status',
            'Approved At',
            'Released At',
            'Created At',
        ];
    }

    public function map($claimRequest): array
    {
        return [
            $claimRequest->id,
            $claimRequest->claimant?->name,
            $claimRequest->foundItem?->item_name,
            $claimRequest->foundItem?->reference_code,
            $claimRequest->approver?->name,
            $claimRequest->status,
            optional($claimRequest->approved_at)->toDateTimeString(),
            optional($claimRequest->released_at)->toDateTimeString(),
            optional($claimRequest->created_at)->toDateTimeString(),
        ];
    }
}
