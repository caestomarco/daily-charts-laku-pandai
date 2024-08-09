<?php

namespace App\Imports;

use DateTime;
use App\Models\Agent;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;

class AgentImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row)
        {
            if ($index > 0 && $row[0] != null)
            {
                $branch = Branch::query()->firstOrCreate(
                    [
                        'id' => $row[1],
                    ],
                    [
                        'name' => $row[1],
                    ]
                );

                Agent::query()->updateOrCreate(
                    [
                        'id' => $row[0],
                    ],
                    [
                        'branch_id' => $row[1],
                        'name' => $row[2] ?? "Agen Tanpa Nama",
                        'status' => $row[4],
                        // CREATE A FORMATTED DATE FROM THE IMPORTED DATE
                        'created_at' => Carbon::create(Str::of($row[3])->replace('/', '-')->toString())->format('Y-m-d H:i:s')
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
