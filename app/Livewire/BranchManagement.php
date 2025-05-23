<?php

namespace App\Livewire;

use Livewire\Component;
use App\Imports\BranchImport;
use App\Models\Branch;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;

class BranchManagement extends Component
{
    use WithFileUploads;

    public $mode = 'ADD';

    #[Validate('required', message: 'Please provide a file')]
    #[Validate('mimes:xlsx,xls', message: 'Please provide a valid .xlsx file')]
    public $file;

    #[Validate('required', message: 'Kode kantor cabang tidak boleh kosong!')]
    #[Validate('numeric', message: 'Kode kantor cabang harus berupa angka!')]
    #[Validate('unique:branches,id', message: 'Kode kantor cabang sudah ada!')]
    #[Validate('digits:3', message: 'Kode kantor cabang harus 3 digit!')]
    public $branchID;

    #[Validate('required', message: 'Nama kantor cabang tidak boleh kosong!')]
    #[Validate('string', message: 'Nama kantor cabang harus berupa teks!')]
    #[Validate('max:255', message: 'Nama kantor cabang tidak boleh lebih dari 255 karakter!')]
    public $branchName;

    #[Validate('required', message: 'Status kantor cabang tidak boleh kosong!')]
    #[Validate('boolean', message: 'Status kantor cabang harus berupa OPEN atau CLOSE!')]
    public $branchStatus = true;

    public $isFileValidated = false;
    public $branchIDValidated = false;
    public $branchNameValidated = false;
    public $branchStatusValidated = true;

    public function resetComponent() 
    {
        $this->reset();
    }

    public function render()
    {
        $title = "Manajemen Kantor Cabang";

        return view('livewire.branch-management')->layout('components.layouts.app', compact('title'));
    }

    public function updated($property)
    {
        $this->validateOnly($property);

        $this->{$property . 'Validated'} = true;
        $this->isFileValidated = true;
    }

    public function updatedBranchStatus()
    {
        $this->validateOnly('branchStatus');
        $this->branchStatusValidated = true;
    }

    public function submitFile()
    {
        try
        {
            $this->validateOnly('file');

            $this->file->storeAs('files', $this->file->getClientOriginalName());

            Excel::import(new BranchImport, storage_path('app/files/' . $this->file->getClientOriginalName()));

            session()->flash('success', 'Berhasil mengimpor data kantor cabang!');

            $this->dispatch('hide-import-branch-modal');

            $this->dispatch('pg:eventRefresh-BranchTable');

            $this->reset('file', 'isFileValidated');
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data kantor cabang. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->branchID]);

            throw $th;
        }
    }

    public function downloadFile()
    {
        // IF ON PRODUCTION, USE THIS
        // return response()->download(storage_path('app/files/DaftarKantorCabang.xlsx'));

        // IF ON LOCAL, USE THIS
        return response()->download(public_path('data/DaftarKantorCabang.xlsx'));
    }

    public function addNewBranch()
    {
        try
        {
            $this->validate(
                [
                    'branchID' => 'required|numeric|unique:branches,id|digits:3',
                    'branchName' => 'required|string|max:255',
                    'branchStatus' => 'required|boolean',
                ],
                [
                    'branchID.required' => 'Kode kantor cabang tidak boleh kosong!',
                    'branchID.numeric' => 'Kode kantor cabang harus berupa angka!',
                    'branchID.unique' => 'Kode kantor cabang sudah ada!',
                    'branchID.digits' => 'Kode kantor cabang harus 3 digit!',
                    'branchName.required' => 'Nama kantor cabang tidak boleh kosong!',
                    'branchName.string' => 'Nama kantor cabang harus berupa teks!',
                    'branchName.max' => 'Nama kantor cabang tidak boleh lebih dari 255 karakter!',
                    'branchStatus.required' => 'Status kantor cabang tidak boleh kosong!',
                    'branchStatus.boolean' => 'Status kantor cabang harus berupa OPEN atau CLOSE!',
                ]
            );

            Branch::query()->updateOrCreate(
                [
                    'id' => $this->branchID,
                ],
                [
                    'name' => $this->branchName,
                    'status' => $this->branchStatus ? 'OPEN' : 'CLOSE',
                ]
            );

            session()->flash('success', 'Berhasil menambahkan data kantor cabang baru!');

            $this->dispatch('hide-add-branch-modal', ['id' => $this->branchID]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal menambahkan data kantor cabang baru. Pastikan data yang anda masukkan sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->branchID]);

            throw $th;
        }
    }

    #[\Livewire\Attributes\On('open-edit-branch-modal')]
    public function prepareEditBranch($branchID, $branchName, $branchStatus)
    {
        $this->mode = 'EDIT';

        $this->branchID = $branchID;
        $this->branchName = $branchName;
        $this->branchStatus = $branchStatus === 'OPEN' ? true : false;
    }

    public function editExistingBranch() 
    {
        try
        {
            $this->validate(
                [
                    'branchName' => 'required|string|max:255',
                    'branchStatus' => 'required|boolean',
                ],
                [
                    'branchName.required' => 'Nama kantor cabang tidak boleh kosong!',
                    'branchName.string' => 'Nama kantor cabang harus berupa teks!',
                    'branchName.max' => 'Nama kantor cabang tidak boleh lebih dari 255 karakter!',
                    'branchStatus.required' => 'Status kantor cabang tidak boleh kosong!',
                    'branchStatus.boolean' => 'Status kantor cabang harus berupa OPEN atau CLOSE!',
                ]
            );

            Branch::query()->updateOrCreate(
                [
                    'id' => $this->branchID,
                ],
                [
                    'name' => $this->branchName,
                    'status' => $this->branchStatus ? 'OPEN' : 'CLOSE',
                ]
            );

            session()->flash('success', 'Berhasil memperbarui data kantor cabang!');

            $this->dispatch('hide-add-branch-modal', ['id' => $this->branchID]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal memperbarui data cabang. Pastikan data yang anda masukkan sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->branchID]);

            throw $th;
        }
    }
}
