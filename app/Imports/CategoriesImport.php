<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim((string)($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            Category::updateOrCreate(
            ['name' => $name],
            [
                'description' => $row['description'] ?? null,
            ]
            );
        }
    }
}
