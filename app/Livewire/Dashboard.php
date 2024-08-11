<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DailyTransactionsImport;
use App\Models\Agent;
use App\Models\DailyTransaction;

class Dashboard extends Component
{
    use WithFileUploads;

    // CHART VARIABLES
    public $chartList = ['Transaksi Harian', 'Transaksi Produk Terbesar', 'Transaksi Agen Terbesar', 'Status Transaksi'];
    public $selectedChart;
    public $currentDate;
    public $currentMonth;
    public array $chartLabels = [];
    public array $chartDatasets = [];

    // DAILY TRANSACTIONS PROPERTIES
    public $totalToday = [
        'transactions' => 0,
        'nominals' => 0,
    ];
    public $todayTopTransaction = [
        'product_name' => '',
        'product_count' => 0,
        'agent_branch' => '',
        'agent_count' => 0,
        'agent_nominals' => 0,
        'agent_total' => 0,
    ];
    public $totalDailyNominals = [];
    public $totalDailyTransactions = [];
    public bool $isChartStonk = false;
    public bool $isThereNewAgent = false;
    public array $topTenAgentTransactions = [];

    #[Validate('required', message: 'Please provide a file')]
    #[Validate('mimes:xlsx,xls', message: 'Please provide a valid .xlsx file')]
    public $file;

    public $isFileValidated = false;

    public function mount()
    {
        $this->selectedChart = $this->chartList[0];
        $this->currentMonth = now()->format('m');
        $this->currentDate = now()->month(intval($this->currentMonth))->subDay(4);

        // dd($this->currentDate->translatedFormat('l, d F Y'));

        $this->getDailyTransactions();
    }

    public function render()
    {
        $title = "Dashboard";

        return view('livewire.dashboard')->layout('components.layouts.app', compact('title'));
    }

    public function updated($property)
    {
        $this->validateOnly($property);

        $this->{$property . 'Validated'} = true;
    }

    public function setChart($value)
    {
        $this->selectedChart = $value;

        if ($value === 'Transaksi Harian')
        {
            $this->getDailyTransactions();

            $this->dispatch('update-daily-transactions-chart', [
                'labels' => $this->chartLabels,
                'datasets' => $this->chartDatasets,
                'currentDate' => $this->currentDate,
                'selectedChart' => $this->selectedChart,
            ]);
        }
        elseif ($value === 'Transaksi Produk Terbesar')
        {
            $this->getTopTenProductTransactions();

            $this->dispatch('update-top-ten-product-transactions-chart', [
                'labels' => $this->chartLabels,
                'datasets' => $this->chartDatasets,
                'currentDate' => $this->currentDate,
                'selectedChart' => $this->selectedChart,
            ]);
        }
        elseif ($value === 'Transaksi Agen Terbesar')
        {
            $this->topTenAgentTransactions = [];
            $this->getTopTenAgentTransactions();
        }
        elseif ($value === 'Status Transaksi')
        {
            $this->getTodayTransactionsStatus();

            $this->dispatch('update-today-transactions-status-chart', [
                'labels' => $this->chartLabels,
                'datasets' => $this->chartDatasets,
                'currentDate' => $this->currentDate,
                'selectedChart' => $this->selectedChart,
            ]);
        }
    }

    public function submitFile()
    {
        try
        {
            $this->validateOnly('file');

            $this->file->storeAs('files', $this->file->getClientOriginalName());

            Excel::import(new DailyTransactionsImport, storage_path('app/files/' . $this->file->getClientOriginalName()));

            $this->getDailyTransactions();

            session()->flash('success', 'Berhasil mengimpor data transaksi!');

            $this->dispatch('hide-import-daily-transaction-modal');

            $this->dispatch('update-daily-transactions-chart', [
                'labels' => $this->chartLabels,
                'datasets' => $this->chartDatasets,
                'currentDate' => $this->currentDate,
                'selectedChart' => $this->chartList[0],
            ]);

            $this->reset('file', 'isFileValidated');
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data transaksi. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert');

            throw $th;
        }
    }

    private function getDailyTransactions()
    {
        $thisMonthDailyTransactions = DailyTransaction::query()
            ->whereMonth('created_at', $this->currentMonth)
            ->whereYear('created_at', now()->year)
            ->get()
            ->sortBy('created_at')
            ->groupBy(function ($transaction)
            {
                return $transaction->created_at->format('d/m/Y');
            });

        foreach ($thisMonthDailyTransactions as $transaction)
        {
            $this->totalDailyTransactions[] = $transaction->count();
            $this->totalDailyNominals[] = $transaction->sum('total');
        }

        // NEED TO MAKE SURE THAT THE INDEX IS NOT NULL BY USING NULL COALESCING OPERATOR (?->)
        $getTodayTransaction = $thisMonthDailyTransactions->get($this->currentDate->format('d/m/Y'));

        $this->totalToday['transactions'] = $getTodayTransaction?->count();
        $this->totalToday['nominals'] = $getTodayTransaction?->sum('total');
        $this->todayTopTransaction['product_name'] = $getTodayTransaction?->groupBy('product_id')->sortDesc()->first()->first()->product->description;
        $this->todayTopTransaction['product_count'] = $getTodayTransaction?->groupBy('product_id')->sortDesc()->first()->count();
        $this->todayTopTransaction['agent_branch'] = $getTodayTransaction?->groupBy('agent_id')->sortDesc()->first()->first()->agent->branch->name;
        $this->todayTopTransaction['agent_count'] = $getTodayTransaction?->groupBy('agent_id')->sortDesc()->first()->count();
        $this->todayTopTransaction['agent_nominals'] = $getTodayTransaction?->groupBy('agent_id')->sortDesc()->first()->sum('total');
        $this->todayTopTransaction['agent_total'] = $getTodayTransaction?->groupBy('agent_id')->count();

        // CHECK IF TODAY's CHART IS STONK OR NOT. copy() TO AVOID MUTATING THE ORIGINAL DATE
        $this->isChartStonk = $getTodayTransaction?->count() > $thisMonthDailyTransactions->get($this->currentDate->copy()->subDay(1)->format('d/m/Y'))?->count();

        // CHECK IF THERE IS A NEW AGENT REGISTERED TODAY
        $this->isThereNewAgent = Agent::query()->whereDate('created_at', $this->currentDate)->exists();

        $this->chartLabels = $thisMonthDailyTransactions->keys()->toArray();
        $this->chartDatasets = [
            [
                'type' => 'bar',
                'label' => 'Nominal',
                'backgroundColor' => '#00B050',
                'borderColor' => '#00B050',
                'borderWidth' => 2,
                'yAxisID' => 'y',
                'order' => 2,
                'data' => $this->totalDailyNominals,
            ],
            [
                'type' => 'line',
                'label' => 'Transaksi',
                // 'backgroundColor' => '#00a25099',
                'borderColor' => '#FF0000',
                // 'pointStyle' => 'rect',
                // 'pointRadius' => 0,
                // 'pointHoverRadius' => 0,
                'borderWidth' => 5,
                'yAxisID' => 'y1',
                'data' => $this->totalDailyTransactions,
            ],
        ];
    }

    private function getTopTenProductTransactions()
    {
        $todayTopTenProductTransactions = DailyTransaction::query()
            ->with(['product'])
            ->whereDay('created_at', $this->currentDate)
            ->whereMonth('created_at', $this->currentMonth)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($transaction)
            {
                return $transaction->product->description;
            })->sortDesc()->take(10)->sort();

        $this->chartLabels = collect($todayTopTenProductTransactions)->keys()->toArray();
        $this->chartDatasets = [
            [
                'label' => 'Transaksi',
                'backgroundColor' => '#FF0000',
                'borderColor' => '#FF0000',
                'borderWidth' => 2,
                'barThickness' => 30,
                'yAxisID' => 'y',
                'data' => collect($todayTopTenProductTransactions)->map(function ($item)
                {
                    return $item->count();
                })->values()->all(),
            ],
        ];
    }

    private function getTopTenAgentTransactions()
    {
        $todayTopTenAgentTransactions = DailyTransaction::query()
            ->with(['agent'])
            ->whereDay('created_at', $this->currentDate)
            ->whereMonth('created_at', $this->currentMonth)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($transaction)
            {
                return $transaction->agent->name;
            })->sortDesc()->take(10);

        foreach (collect($todayTopTenAgentTransactions) as $transaction)
        {
            $this->topTenAgentTransactions[] = [
                'branch' => $transaction->first()->agent->branch,
                'account' => $transaction->first()->agent->account,
                'name' => $transaction->first()->agent->name,
                'transaction' => $transaction->count(),
                'nominal' => number_format($transaction->sum('total'), 0, ',', '.'), // FORMAT NOMINAL
            ];
        }
    }

    private function getTodayTransactionsStatus()
    {
        $todayTransactionStatus = DailyTransaction::query()
            ->whereDay('created_at', $this->currentDate)
            ->whereMonth('created_at', $this->currentMonth)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($transaction)
            {
                return $transaction->status;
            });

        $this->chartLabels = collect($todayTransactionStatus)->keys()->toArray();
        $this->chartDatasets = [
            [
                'label' => 'Total Transaksi',
                'backgroundColor' =>
                [
                    'rgba(50, 220, 50, 0.8)', // SUCCESS
                    'rgba(255, 0, 0, 1)', // FAILED
                    'rgba(18, 18, 18, 0.5)', // SUSPECT
                ],
                'data' => collect($todayTransactionStatus)->map(function ($item)
                {
                    return $item->count();
                })->values()->all(),
            ],
        ];
    }
}
