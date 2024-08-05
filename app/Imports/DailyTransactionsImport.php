<?php

namespace App\Imports;

use App\Models\Agent;
use App\Models\DailyTransaction;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DailyTransactionsImport implements ToCollection, SkipsEmptyRows, WithHeadingRow
{
    public function removeDecimal($value)
    {
        return (float) str_replace(',', '', $value);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row)
        {
            if ($index > 0 && $row[1] != null)
            {
                $product = Product::query()->firstOrCreate(
                    [
                        'id' => $row[3],
                    ],
                    [
                        'description' => $row[3],
                    ]
                );

                $agentAccount = Agent::query()->updateOrCreate(
                    [
                        'id' => $row[2],
                    ],
                    [
                        'account' => $row[8],
                    ]
                );

                DailyTransaction::query()->updateOrCreate(
                    [
                        'id' => $row[4],
                    ],
                    [
                        'agent_id' => $row[2],
                        'product_id' => $row[3],
                        'source_account' => $row[7],
                        'nominal' => $this->removeDecimal($row[9]),
                        'admin_fee' => $this->removeDecimal($row[10]),
                        'total' => $this->removeDecimal($row[11]),
                        'status' => $row[6],
                        // CREATE A FORMATTED DATE FROM THE IMPORTED DATE
                        'created_at' => Carbon::create(Str::of($row[5])->replace('/', '-')->toString())->format('Y-m-d H:i:s')
                    ]
                );
            }
        }
    }
}
