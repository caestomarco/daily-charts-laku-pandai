<div class="container-fluid d-flex flex-column gap-2 me-0" style="width: 83%">

    {{-- BREADCRUMBS --}}
    <nav aria-label="breadcrumb">
        <ol @class(['breadcrumb ', 'mx-4 mt-4' => true]) class="">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen Produk</li>
        </ol>
    </nav>

    {{-- ALERT --}}
    @if (session()->has('success'))
        <div id="success-alert" class="alert alert-success alert-dismissible fade show position-fixed mx-4 mb-0" role="alert" style="right: .7rem; top: .7rem; z-index: 9999">
            <strong>Sukses!</strong>
            <br>
            {{ session('success') }}
            </br>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (session()->has('error'))
        <div id="error-alert" class="alert alert-danger alert-dismissible fade show position-fixed mx-4 mb-0" role="alert" style="right: .7rem; top: .7rem; z-index: 9999">
            <strong>Error!</strong>
            <br>
            {{ session('error') }}
            </br>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between mx-4 p-3 rounded-3 bg-light-subtle shadow">
        <h3 class="mb-0 text-warning-emphasis fw-bold">Manajemen Produk</h3>
        <div>
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#import-product-modal">
                Import Data Produk
            </button>
            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#add-product-modal">
                Tambah Produk Baru
            </button>
        </div>
    </div>

    {{-- POWERGRID TABLE --}}
    <div class="mx-4 p-3 rounded-3 bg-light-subtle shadow">
        @livewire('product-table')
    </div>

    {{-- IMPORT PRODUCT MODAL --}}
    <div class="modal fade" id="import-product-modal" tabindex="-1" aria-labelledby="import-product-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="import-product-label">Import Produk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="submitFile" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Anda dapat menambahkan data produk secara <span class="fst-italic">otomatis</span> dengan meng-upload file .xlsx seperti contoh <a href="#"
                                class="alert-link">berikut</a>.
                        </div>

                        {{-- FILE --}}
                        <input class="form-control @error('file') is-invalid @elseif($isFileValidated) is-valid @enderror" id="validationCustom01" type="file" wire:model.live="file">
                        <div class="valid-feedback">
                            File valid!
                        </div>
                        @error('file')
                            <div class="invalid-feedback"> {{ $message }} </div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                            <span class="spinner-border spinner-border-sm" aria-hidden="true" wire:loading></span>
                            <span role="status">Submit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ADD PRODUCT MODAL --}}
    <div class="modal fade" id="add-product-modal" tabindex="-1" aria-labelledby="add-product-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="add-product-label">Tambah Produk Baru</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="addNewProduct" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    {{-- FORM CONTENT --}}
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Anda dapat menambahkan data produk secara <span class="fst-italic">manual</span> dengan mengisi form berikut.
                        </div>

                        {{-- ID --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('productID') is-invalid @elseif($productIDValidated) is-valid @enderror" id="product-id" placeholder="" type="text"
                                wire:model.blur="productID">
                            <label for="product-id">Kode Product</label>
                            <div class="valid-feedback">
                                Kode produk valid!
                            </div>
                            @error('productID')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                            <div id="product-id-example" class="form-text">Contoh Kode Produk: BALANCE_INT</div>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('productDescription') is-invalid @elseif($productDescriptionValidated) is-valid @enderror" id="product-description" placeholder=""
                                type="text" wire:model.blur="productDescription">
                            <label for="product-description">Deskripsi Produk</label>
                            <div class="valid-feedback">
                                Deskripsi produk valid!
                            </div>
                            @error('productDescription')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                        </div>
                    </div>

                    {{-- FORM ACTION --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                            <span class="spinner-border spinner-border-sm" aria-hidden="true" wire:loading></span>
                            <span role="status">Submit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('additional-script')
    <script>
        (() => {
            // HIDE ADD PRODUCT MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-add-product-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('add-product-modal')).hide();

                setTimeout(function() {
                    window.bootstrap.Alert.getOrCreateInstance('#success-alert').close();
                }, 5000)
            });

            // HIDE IMPORT PRODUCT MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-import-product-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('import-product-modal')).hide();

                setTimeout(function() {
                    window.bootstrap.Alert.getOrCreateInstance('#success-alert').close();
                }, 5000)
            });

            // AUTO CLOSE ERROR ALERT AFTER 5 SECONDS
            window.addEventListener('auto-close-error-alert', (event) => {
                setTimeout(function() {
                    window.bootstrap.Alert.getOrCreateInstance('#error-alert').close();
                }, 5000)
            });
        })();
    </script>
@endpush
