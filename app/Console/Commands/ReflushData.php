<?php

namespace App\Console\Commands;

use App\Models\DailyTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReflushData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reflush-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus data transaksi harian bulan sebelumnya.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Berhasil menghapus data transaksi harian bulan sebelumnya √');

        DB::beginTransaction();

        try
        {
            $obsolete_transaction = DailyTransaction::query()
                ->whereDate('created_at', '<', now()->startOfMonth())
                ->chunkById(1000, function ($transactions) use (&$deleted_count)
                {
                    foreach ($transactions as $transaction)
                    {
                        // Perform any additional operations here if needed
                        $transaction->delete();
                    }
                });

            DB::commit();
        }
        catch (\Throwable $th)
        {
            throw $th;
        }
    }
}
