<?php

namespace App\Exports;

use App\Models\FoundItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FoundItemsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return FoundItem::with(['staff', 'category'])->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Staff',
            'Category',
            'Item Name',
            'Description',
            'Color',
            'Found Location',
            'Date Found',
            'Reference Code',
            'Storage Location',
            'Status',
            'Created At',
        ];
    }

    public function map($foundItem): array
    {
        return [
            $foundItem->id,
            $foundItem->staff?->name,
            $foundItem->category?->name,
            $foundItem->item_name,
            $foundItem->description,
            $foundItem->color,
            $foundItem->found_location,
            optional($foundItem->date_found)->format('Y-m-d'),
            $foundItem->reference_code,
            $foundItem->storage_location,
            $foundItem->status,
            optional($foundItem->created_at)->toDateTimeString(),
        ];
    }
}
