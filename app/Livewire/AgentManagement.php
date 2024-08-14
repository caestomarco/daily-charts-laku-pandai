<?php

namespace App\Livewire;

use App\Imports\AgentImport;
use App\Models\Agent;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;

class AgentManagement extends Component
{
    use WithFileUploads;

    public $mode = 'ADD';

    #[Validate('required', message: 'File tidak boleh kosong!')]
    #[Validate('mimes:xlsx,xls', message: 'Tolong upload file dengan ekstensi .xlsx atau .xls!')]
    public $file;

    #[Validate('required', message: 'ID agen tidak boleh kosong!')]
    #[Validate('numeric', message: 'ID agen harus berupa angka!')]
    #[Validate('unique:agents,id', message: 'ID agen sudah ada!')]
    #[Validate('digits:12', message: 'Kode agen harus 12 digit!')]
    public $agentID;

    #[Validate('required', message: 'Kode cabang tidak boleh kosong!')]
    #[Validate('numeric', message: 'Kode cabang harus berupa angka!')]
    #[Validate('exists:branches,id', message: 'Kode cabang tidak ditemukan!')]
    #[Validate('digits:3', message: 'Kode cabang harus 3 digit!')]
    public $branchID;

    #[Validate('required', message: 'Nama agen tidak boleh kosong!')]
    #[Validate('string', message: 'Nama agen harus berupa teks!')]
    #[Validate('max:255', message: 'Nama agen tidak boleh lebih dari 255 karakter!')]
    public $agentName;

    #[Validate('numeric', message: 'Rekening agen harus berupa angka!')]
    #[Validate('digits:14', message: 'Rekening agen harus 14 digit!')]
    public $agentAccount;

    #[Validate('required', message: 'Status agen tidak boleh kosong!')]
    #[Validate('boolean', message: 'Status agen harus berupa boolean!')]
    public $agentStatus = true;

    public $isFileValidated = false;
    public $agentIDValidated = false;
    public $branchIDValidated = false;
    public $agentNameValidated = false;
    public $agentAccountValidated = false;
    public $agentStatusValidated = true;

    public function resetComponent() 
    {
        $this->reset();
    }

    public function render()
    {
        $title = "Manajemen Agen";
        $branchList = Branch::query()->get();

        return view('livewire.agent-management', compact('branchList'))->layout('components.layouts.app', compact('title'));
    }

    public function updated($property)
    {
        $this->validateOnly($property);

        $this->{$property . 'Validated'} = true;
        $this->isFileValidated = true;
    }

    public function submitFile()
    {
        try
        {
            $this->validateOnly('file');

            $this->file->storeAs('files', $this->file->getClientOriginalName());

            Excel::import(new AgentImport, storage_path('app/files/' . $this->file->getClientOriginalName()));

            session()->flash('success', 'Berhasil mengimpor data agen!');

            $this->dispatch('hide-import-agent-modal');

            $this->dispatch('pg:eventRefresh-AgentTable');

            $this->reset('file', 'isFileValidated');
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data agen. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->agentID]);

            throw $th;
        }
    }

    public function downloadFile()
    {
        // IF ON PRODUCTION, USE THIS
        // return response()->download(storage_path('app/files/DaftarAgen.xlsx'));

        // IF ON LOCAL, USE THIS
        return response()->download(public_path('data/DaftarAgen.xlsx'));
    }

    public function addNewAgent()
    {
        try
        {
            $this->validate(
                [
                    'agentID' => 'required|numeric|unique:agents,id|digits:12',
                    'branchID' => 'required|numeric|exists:branches,id|digits:3',
                    'agentName' => 'required|string|max:255',
                    'agentAccount' => 'required|numeric|digits:14',
                    'agentStatus' => 'required|boolean',
                ],
                [
                    'agentID.required' => 'Kode agen tidak boleh kosong!',
                    'agentID.numeric' => 'Kode agen cabang harus berupa angka!',
                    'agentID.unique' => 'Kode agen sudah ada!',
                    'agentID.digits' => 'Kode agen harus 12 digit!',
                    'branchID.required' => 'Kode kantor cabang tidak boleh kosong!',
                    'branchID.numeric' => 'Kode kantor cabang harus berupa angka!',
                    'branchID.exists' => 'Kode kantor cabang tidak ditemukan!',
                    'branchID.digits' => 'Kode kantor cabang harus 3 digit!',
                    'agentName.required' => 'Nama agen tidak boleh kosong!',
                    'agentName.string' => 'Nama agen harus berupa teks!',
                    'agentName.max' => 'Nama agen tidak boleh lebih dari 255 karakter!',
                    'agentAccount.numeric' => 'Rekening agen harus berupa angka!',
                    'agentAccount.digits' => 'Rekening agen harus 14 digit!',
                    'agentStatus.required' => 'Status agen tidak boleh kosong!',
                    'agentStatus.boolean' => 'Status agen harus berupa ACTIVE atau CLOSE!',
                ]
            );

            Agent::query()->updateOrCreate(
                [
                    'id' => $this->agentID,
                ],
                [
                    'branch_id' => $this->branchID,
                    'account' => $this->agentAccount,
                    'name' => $this->agentName,
                    'status' => $this->agentStatus ? 'ACTIVE' : 'CLOSE',
                ]
            );

            session()->flash('success', 'Berhasil menambahkan data agen baru!');

            $this->dispatch('hide-add-agent-modal', ['id' => $this->agentID]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal menambahkan data agen baru. Pastikan data yang anda masukkan sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->agentID]);

            throw $th;
        }
    }

    #[\Livewire\Attributes\On('open-edit-agent-modal')]
    public function prepareEditAgent($agentID, $branchID, $agentName, $agentStatus, $agentAccount)
    {
        $this->mode = 'EDIT';

        $this->agentID = $agentID;
        $this->branchID = $branchID;
        $this->agentName = $agentName;
        $this->agentStatus = $agentStatus === 'ACTIVE' ? true : false;
        $this->agentAccount = $agentAccount;
    }

    public function editExistingAgent()
    {
        try
        {
            $this->validate(
                [
                    'branchID' => 'required|numeric|exists:branches,id|digits:3',
                    'agentName' => 'required|string|max:255',
                    'agentAccount' => 'required|numeric|digits:14',
                    'agentStatus' => 'required|boolean',
                ],
                [
                    'branchID.required' => 'Kode kantor cabang tidak boleh kosong!',
                    'branchID.numeric' => 'Kode kantor cabang harus berupa angka!',
                    'branchID.exists' => 'Kode kantor cabang tidak ditemukan!',
                    'branchID.digits' => 'Kode kantor cabang harus 3 digit!',
                    'agentName.required' => 'Nama agen tidak boleh kosong!',
                    'agentName.string' => 'Nama agen harus berupa teks!',
                    'agentName.max' => 'Nama agen tidak boleh lebih dari 255 karakter!',
                    'agentAccount.numeric' => 'Rekening agen harus berupa angka!',
                    'agentAccount.digits' => 'Rekening agen harus 14 digit!',
                    'agentStatus.required' => 'Status agen tidak boleh kosong!',
                    'agentStatus.boolean' => 'Status agen harus berupa ACTIVE atau CLOSE!',
                ]
            );

            Agent::query()->updateOrCreate(
                [
                    'id' => $this->agentID,
                ],
                [
                    'branch_id' => $this->branchID,
                    'account' => $this->agentAccount,
                    'name' => $this->agentName,
                    'status' => $this->agentStatus ? 'ACTIVE' : 'CLOSE',
                ]
            );

            session()->flash('success', 'Berhasil memperbarui data agen!');

            $this->dispatch('hide-add-agent-modal', ['id' => $this->agentID]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal memperbarui data agen. Pastikan data yang anda masukkan sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->agentID]);

            throw $th;
        }
    }
}
