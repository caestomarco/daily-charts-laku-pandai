<?php

namespace App\Imports;

use App\Models\Branch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BranchImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row)
        {
            if ($index > 0 && $row[0] != null)
            {
                Branch::query()->updateOrCreate(
                    [
                        'id' => $row[0],
                    ],
                    [
                        'name' => $row[1],
                        'status' => $row[2],
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
