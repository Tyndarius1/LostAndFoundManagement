<?php

namespace App\Exports;

use App\Models\LostItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LostItemsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return LostItem::with(['user', 'category'])->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Category',
            'Item Name',
            'Description',
            'Color',
            'Last Seen Location',
            'Date Lost',
            'Status',
            'Created At',
        ];
    }

    public function map($lostItem): array
    {
        return [
            $lostItem->id,
            $lostItem->user?->name,
            $lostItem->category?->name,
            $lostItem->item_name,
            $lostItem->description,
            $lostItem->color,
            $lostItem->last_seen_location,
            optional($lostItem->date_lost)->format('Y-m-d'),
            $lostItem->status,
            optional($lostItem->created_at)->toDateTimeString(),
        ];
    }
}
