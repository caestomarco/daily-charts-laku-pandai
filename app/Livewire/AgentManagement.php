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

    #[Validate('required', message: 'File tidak boleh kosong!')]
    #[Validate('mimes:xlsx,xls', message: 'Tolong upload file dengan ekstensi .xlsx atau .xls!')]
    public $file;

    #[Validate('required', message: 'ID agen tidak boleh kosong!')]
    #[Validate('numeric', message: 'ID agen harus berupa angka!')]
    #[Validate('unique:agents,id', message: 'ID agen sudah ada!')]
    #[Validate('digits:12', message: 'Kode agen harus 12 digit!')]
    public $agentID;

    #[Validate('required', message: 'Kode agen tidak boleh kosong!')]
    #[Validate('numeric', message: 'Kode agen harus berupa angka!')]
    #[Validate('exists:branches,id', message: 'Kode agen tidak ditemukan!')]
    #[Validate('digits:3', message: 'Kode agen harus 3 digit!')]
    public $branchID;

    #[Validate('required', message: 'Nama agen tidak boleh kosong!')]
    #[Validate('string', message: 'Nama agen harus berupa teks!')]
    #[Validate('max:255', message: 'Nama agen tidak boleh lebih dari 255 karakter!')]
    public $agentName;

    #[Validate('required', message: 'Rekening agen tidak boleh kosong!')]
    #[Validate('numeric', message: 'Rekening agen harus berupa angka!')]
    #[Validate('digits:14', message: 'Rekening agen harus 14 digit!')]
    public $agentAccount;

    #[Validate('required', message: 'Status agen tidak boleh kosong!')]
    #[Validate('boolean', message: 'Status agen harus berupa boolean!')]
    public $agentStatus = true;

    public $fileValidated = false;
    public $agentIDValidated = false;
    public $branchIDValidated = false;
    public $agentNameValidated = false;
    public $agentAccountValidated = false;
    public $agentStatusValidated = true;

    public function render()
    {
        $title = "Manajemen Agen";
        $branchList = Branch::query()->get();

        return view('livewire.agent-management', compact('branchList'))->layout('components.layouts.app', compact('title'));
    }

    public function updatedFile()
    {
        $this->validateOnly('file');
        $this->fileValidated = true;
    }

    public function updatedAgentID()
    {
        $this->validateOnly('agentID');
        $this->agentIDValidated = true;
    }

    public function updatedAgentName()
    {
        $this->validateOnly('agentName');
        $this->agentNameValidated = true;
    }

    public function updatedBranchID()
    {
        $this->validateOnly('branchID');
        $this->branchIDValidated = true;
    }

    public function updatedAgentAccount()
    {
        $this->validateOnly('agentAccount');
        $this->agentAccountValidated = true;
    }

    public function updatedAgentStatus()
    {
        $this->validateOnly('agentStatus');
        $this->agentStatusValidated = true;
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

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data agen. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->agentID]);

            throw $th;
        }
    }

    public function addNewAgent()
    {
        try
        {
            $this->validate(
                [
                    'agentID' => 'required|numeric',
                    'branchID' => 'required|numeric|exists:branches,id|digits:3',
                    'agentName' => 'required|string|max:255',
                    'agentAccount' => 'required|numeric|digits:14',
                    'agentStatus' => 'required|boolean',
                ],
                [
                    'agentID.required' => 'Kode agen tidak boleh kosong!',
                    'agentID.numeric' => 'Kode agen cabang harus berupa angka!',
                    'branchID.required' => 'Kode kantor cabang tidak boleh kosong!',
                    'branchID.numeric' => 'Kode kantor cabang harus berupa angka!',
                    'branchID.exists' => 'Kode kantor cabang tidak ditemukan!',
                    'branchID.digits' => 'Kode kantor cabang harus 3 digit!',
                    'agentName.required' => 'Nama agen tidak boleh kosong!',
                    'agentName.string' => 'Nama agen harus berupa teks!',
                    'agentName.max' => 'Nama agen tidak boleh lebih dari 255 karakter!',
                    'agentAccount.required' => 'Rekening agen tidak boleh kosong!',
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

            $this->dispatch('hide-add-agent-modal', ['id' => $this->branchID]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal menambahkan data agen baru. Pastikan data yang anda masukkan sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->branchID]);

            throw $th;
        }
    }
}
