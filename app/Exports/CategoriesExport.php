<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Category::query()->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Created At',
            'Updated At',
        ];
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->name,
            $category->description,
            optional($category->created_at)->toDateTimeString(),
            optional($category->updated_at)->toDateTimeString(),
        ];
    }
}
