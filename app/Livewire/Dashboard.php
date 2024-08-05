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

    #[Validate('required', message: 'Please provide a file')]
    #[Validate('mimes:xlsx,xls', message: 'Please provide a valid .xlsx file')]
    public $file;

    public $filterList = ['Transaksi Harian', 'Transaksi Terbesar', 'Transaksi Agen Terbesar'];
    public $selectedFilter;
    public array $dataset = [];
    public array $labels = [];

    public $fileValidated = false;

    public function mount()
    {
        $this->selectedFilter = $this->filterList[0];

        if ($this->selectedFilter === $this->filterList[0])
        {
            $this->labels[] = $this->getTransaksiHarianLabels();
            $this->dataset = [
                [
                    'type' => 'line',
                    'label' => 'Logged In',
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                    'data' => $this->getRandomData(),
                ],
                [
                    'label' => 'Logged In',
                    'backgroundColor' => 'rgba(255,230,170,0.6)',
                    'borderColor' => 'rgb(255 230 170)',
                    'data' => $this->getRandomData(),
                ],
            ];
        }
    }

    public function render()
    {
        $title = "Dashboard";

        return view('livewire.dashboard')->layout('components.layouts.app', compact('title'));
    }

    public function updatedFile()
    {
        $this->validateOnly('file');
        $this->fileValidated = true;
    }

    public function setFilter($value)
    {
        $this->selectedFilter = $value;
    }

    public function submitFile()
    {
        try
        {
            $this->validateOnly('file');

            $this->file->storeAs('files', $this->file->getClientOriginalName());

            Excel::import(new DailyTransactionsImport, storage_path('app/files/' . $this->file->getClientOriginalName()));

            session()->flash('success', 'Berhasil mengimpor data transaksi!');

            $this->dispatch('hide-import-transaction-modal');

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data transaksi. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert');

            throw $th;
        }
    }

    private function getTransaksiHarianLabels()
    {
        $thisMonthTransactions = DailyTransaction::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->get();

        $groupEachDayTransactions = $thisMonthTransactions->sortBy('created_at')->groupBy(function ($transaction)
        {
            return $transaction->created_at->format('d M');
        })->keys()->toArray();

        return $groupEachDayTransactions;
    }

    private function getRandomData()
    {
        $data = [];
        for ($i = 0; $i < count($this->getTransaksiHarianLabels()); $i++) {
            $data[] = rand(10, 100);
        }
        return $data;
    }
}
