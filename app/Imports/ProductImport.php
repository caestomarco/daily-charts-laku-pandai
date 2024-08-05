<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row)
        {
            if ($index > 0 && $row[0] != null)
            {
                Product::query()->updateOrCreate(
                    [
                        'id' => $row[0],
                    ],
                    [
                        'description' => $row[1],
                    ]
                );
            }
            else
            {
                continue;
            }
        }
    }
}
