<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DailyTransactionsImport;
use App\Models\DailyTransaction;

class Dashboard extends Component
{
    use WithFileUploads;

    public $filterList = ['Transaksi Harian', 'Transaksi Produk Terbesar', 'Transaksi Agen Terbesar', 'Status Transaksi'];
    public $selectedChart = 'Transaksi Harian';
    public $selectedDate;
    public array $labels = [];
    public array $datasets = [];
    public $mostFrequentAgentTransactions = [];

    #[Validate('required', message: 'Please provide a file')]
    #[Validate('mimes:xlsx,xls', message: 'Please provide a valid .xlsx file')]
    public $file;

    public $fileValidated = false;

    public function mount()
    {
        $this->selectedDate = now()->format('d/m/Y');

        $this->setTransaksiHarianChart();
    }

    public function render()
    {
        $title = "Dashboard";

        return view('livewire.dashboard')->layout('components.layouts.app', compact('title'));
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        $this->{$propertyName . 'Validated'} = true;
    }

    public function setFilter($value)
    {
        $this->selectedChart = $value;

        if ($value === 'Transaksi Harian')
        {
            $this->setTransaksiHarianChart();

            $this->dispatch('update-transaksi-harian-chart', [
                'labels' => $this->labels,
                'datasets' => $this->datasets,
                'selectedDate' => $this->selectedDate,
                'selectedChart' => $this->selectedChart,
            ]);
        }
        elseif ($value === 'Transaksi Produk Terbesar')
        {
            $this->setTransaksiProdukTerbesarChart();

            $this->dispatch('update-transaksi-produk-terbesar-chart', [
                'labels' => $this->labels,
                'datasets' => $this->datasets,
                'selectedDate' => $this->selectedDate,
                'selectedChart' => $this->selectedChart,
            ]);
        }
        elseif ($value === 'Transaksi Agen Terbesar')
        {
            $this->mostFrequentAgentTransactions = [];
            $this->setTransaksiAgenTerbesarChart();
        }
        elseif ($value === 'Status Transaksi')
        {
            $this->setStatusTransaksiChart();

            $this->dispatch('update-status-transaksi-chart', [
                'labels' => $this->labels,
                'datasets' => $this->datasets,
                'selectedDate' => $this->selectedDate,
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

            $this->setTransaksiHarianChart();

            session()->flash('success', 'Berhasil mengimpor data transaksi!');

            $this->dispatch('hide-import-transaction-modal');

            $this->dispatch('update-transaksi-harian-chart', [
                'labels' => $this->labels,
                'datasets' => $this->datasets,
                'selectedDate' => $this->selectedDate,
                'selectedChart' => $this->filterList[0],
            ]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data transaksi. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert');

            throw $th;
        }
    }

    private function setTransaksiHarianChart()
    {
        $thisMonthDailyTransactions = DailyTransaction::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->sortBy('created_at')
            ->groupBy(function ($transaction)
            {
                return $transaction->created_at->format('d/m/Y');
            });

        $totalDailyTransactions = [];
        $totalDailyNominals = [];

        foreach ($thisMonthDailyTransactions as $transaction)
        {
            $totalDailyTransactions[] = $transaction->count();
            $totalDailyNominals[] = $transaction->sum('total');
        }

        $this->labels = $thisMonthDailyTransactions->keys()->toArray();
        $this->datasets = [
            [
                'type' => 'line',
                'label' => 'Transaksi',
                'backgroundColor' => '#00a25099',
                'borderColor' => '#00a250',
                'pointStyle' => 'rect',
                'pointRadius' => 25,
                'pointHoverRadius' => 20,
                'borderWidth' => 2,
                'yAxisID' => 'y1',
                'data' => $totalDailyTransactions,
            ],
            [
                'type' => 'bar',
                'label' => 'Nominal',
                'backgroundColor' => '#fbb031b3',
                'borderColor' => '#fbb031',
                'borderWidth' => 2,
                'yAxisID' => 'y',
                'data' => $totalDailyNominals,
            ],
        ];
    }

    private function setTransaksiProdukTerbesarChart()
    {
        $todayProductTransactions = DailyTransaction::query()
            ->with('product')
            ->whereDay('created_at', $this->selectedDate)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($transaction)
            {
                return $transaction->product->description;
            });

        $this->labels = collect($todayProductTransactions)->sortDesc()->take(10)->sort()->keys()->toArray();
        $this->datasets = [
            [
                'label' => 'Transaksi',
                'backgroundColor' => '#00a250b3',
                'borderColor' => '#00a250',
                'borderWidth' => 2,
                'barThickness' => 30,
                'yAxisID' => 'y',
                'data' => collect($todayProductTransactions)->sortDesc()->take(10)->sort()->map(function ($item)
                {
                    return $item->count();
                })->values()->all(),
            ],
        ];
    }

    private function setTransaksiAgenTerbesarChart()
    {
        $todayAgentTransactions = DailyTransaction::query()
            ->with(['agent'])
            ->whereDay('created_at', $this->selectedDate)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($transaction)
            {
                return $transaction->agent->name;
            });

        foreach (collect($todayAgentTransactions)->sortDesc()->take(10) as $transaction)
        {
            $this->mostFrequentAgentTransactions[] = [
                'branch' => $transaction->first()->agent->branch,
                'account' => $transaction->first()->agent->account,
                'name' => $transaction->first()->agent->name,
                'transaction' => $transaction->count(),
                // FORMAT NOMINAL
                'nominal' => number_format($transaction->sum('total'), 0, ',', '.'),
            ];
        }
    }

    private function setStatusTransaksiChart()
    {
        $todayStatusTransactions = DailyTransaction::query()
            ->whereDay('created_at', $this->selectedDate)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($transaction)
            {
                return $transaction->status;
            });

        $this->labels = collect($todayStatusTransactions)->keys()->toArray();
        $this->datasets = [
            [
                'label' => 'Total Transaksi',
                'backgroundColor' => 
                [
                    'rgba(50, 220, 50, 0.8)',
                    'rgba(255, 0, 0, 1)',
                    'rgba(18, 18, 18, 0.5)',
                ],
                'data' => collect($todayStatusTransactions)->map(function ($item)
                {
                    return $item->count();
                })->values()->all(),
            ],
        ];
    }
}
