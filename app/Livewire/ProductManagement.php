<?php

namespace App\Livewire;

use App\Imports\ProductImport;
use App\Models\Product;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;

class ProductManagement extends Component
{
    use WithFileUploads;

    #[Validate('required', message: 'Please provide a file')]
    #[Validate('mimes:xlsx,xls', message: 'Please provide a valid .xlsx file')]
    public $file;

    #[Validate('required', message: 'Kode produk tidak boleh kosong!')]
    #[Validate('unique:products,id', message: 'Kode produk sudah ada!')]
    #[Validate('alpha_dash:ascii', message: 'Kode produk harus berupa angka atau huruf!')]
    #[Validate('uppercase', message: 'Kode produk harus huruf kapital!')]
    #[Validate('min:3', message: 'Kode produk harus minimal 3 karakter!')]
    public $productID;

    #[Validate('required', message: 'Deskripsi produk tidak boleh kosong!')]
    #[Validate('string', message: 'Deskripsi produk harus berupa teks!')]
    public $productDescription;

    public $isFileValidated = false;
    public $productIDValidated = false;
    public $productDescriptionValidated = false;

    public function render()
    {
        $title = "Manajemen Produk";

        return view('livewire.product-management')->layout('components.layouts.app', compact('title'));
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

            Excel::import(new ProductImport, storage_path('app/files/' . $this->file->getClientOriginalName()));

            session()->flash('success', 'Berhasil mengimpor data produk!');

            $this->dispatch('hide-import-product-modal', ['id' => $this->productID]);

            $this->dispatch('pg:eventRefresh-ProductTable');

            $this->reset('file', 'isFileValidated');
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal mengimpor data produk. Pastikan file yang anda pilih sudah benar!');

            $this->dispatch('auto-close-error-alert', ['id' => $this->productID]);

            throw $th;
        }
    }

    public function downloadFile()
    {
        // IF ON PRODUCTION, USE THIS
        // return response()->download(storage_path('app/files/DaftarProduk.xlsx'));

        // IF ON LOCAL, USE THIS
        return response()->download(public_path('data/DaftarProduk.xlsx'));
    }

    public function addNewProduct()
    {
        try
        {
            $this->validate(
                [
                    'productID' => 'required|unique:products,id|alpha_dash:ascii|uppercase|min:3',
                    'productDescription' => 'required|string'
                ],
                [
                    'productID.required' => 'Kode produk tidak boleh kosong!',
                    'productID.unique' => 'Kode produk sudah ada!',
                    'productID.alpha_dash' => 'Kode produk harus berupa angka atau huruf!',
                    'productID.uppercase' => 'Kode produk harus huruf kapital!',
                    'productID.min' => 'Kode produk harus minimal 3 karakter!',
                    'productDescription.required' => 'Deskripsi produk tidak boleh kosong!',
                    'productDescription.string' => 'Deskripsi produk harus berupa teks!'
                ]
            );

            Product::query()->updateOrCreate(
                [
                    'id' => $this->productID,
                ],
                [
                    'description' => $this->productDescription
                ]
            );

            session()->flash('success', 'Berhasil menambahkan produk baru!');

            $this->dispatch('hide-add-product-modal', ['id' => $this->productID]);

            $this->reset();
        }
        catch (\Throwable $th)
        {
            session()->flash('error', 'Gagal menambahkan produk baru. Pastikan data yang anda masukkan sudah benar!');

            $this->dispatch('auto-close-error-alert');

            throw $th;
        }
    }
}
