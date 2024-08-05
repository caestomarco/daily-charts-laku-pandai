<div @class(['container-fluid d-flex flex-column', 'gap-2 me-0' => true]) class=" " style="width: 83%">
    {{-- BREADCRUMBS --}}
    <nav aria-label="breadcrumb">
        <ol @class(['breadcrumb ', 'mx-4 mt-4' => true]) class="">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen Kantor Cabang</li>
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
    <div @class([
        'd-flex justify-content-between ',
        'mx-4 p-3 rounded-3',
        'bg-light-subtle shadow-sm' => true,
    ])>
        <h3 @class(['mb-0', 'text-warning-emphasis fw-bold' => true])>Manajemen Kantor Cabang</h3>
        <div>
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#import-branch-modal">
                Import Data Cabang
            </button>
            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#add-branch-modal">
                Tambah Cabang Baru
            </button>
        </div>
    </div>

    {{-- POWERGRID TABLE --}}
    <div @class([
        'mx-4 p-3 rounded-3',
        'bg-light-subtle shadow-lg' => true,
    ])>
        @livewire('branch-table')
    </div>

    {{-- IMPORT BRANCH MODAL --}}
    <div class="modal fade" id="import-branch-modal" tabindex="-1" aria-labelledby="import-branch-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="import-branch-label">Import Kantor Cabang</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="submitFile" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Anda dapat menambahkan data kantor cabang secara <span class="fst-italic">otomatis</span> dengan meng-upload file .xlsx seperti contoh <a href="#"
                                class="alert-link">berikut</a>.
                        </div>

                        {{-- FILE --}}
                        <input class="form-control @error('file') is-invalid @elseif($fileValidated) is-valid @enderror" id="branch-file" type="file" wire:model.live="file">
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

    {{-- ADD BRANCH MODAL --}}
    <div class="modal fade" id="add-branch-modal" tabindex="-1" aria-labelledby="add-branch-label" aria-hidden="true" data-bs-backdrop="static" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="add-branch-label">Tambah Kantor Cabang Baru</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="addNewBranch" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Anda dapat menambahkan data kantor cabang secara <span class="fst-italic">manual</span> dengan mengisi form berikut.
                        </div>

                        {{-- ID --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('branchID') is-invalid @elseif($branchIDValidated) is-valid @enderror" id="branch-id" placeholder="" type="text"
                                wire:model.blur="branchID">
                            <label for="branch-id">Kode Kantor Cabang</label>
                            <div class="valid-feedback">
                                Kode kantor cabang valid!
                            </div>
                            @error('branchID')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                            <div id="emailHelp" class="form-text">Contoh Kode Kantor Cabang: 001</div>
                        </div>

                        {{-- NAME --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('branchName') is-invalid @elseif($branchNameValidated) is-valid @enderror" id="branch-name" placeholder="" type="text"
                                wire:model.blur="branchName">
                            <label for="branch-name">Nama Kantor Cabang</label>
                            <div class="valid-feedback">
                                Nama kantor cabang valid!
                            </div>
                            @error('branchName')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                        </div>

                        {{-- STATUS --}}
                        <div class="form-inline form-check form-switch">
                            <label class="form-check-label" for="branch-status">Status: {{ $branchStatus ? 'OPEN' : 'CLOSE' }}</label>
                            <input class="form-check-input is-valid" type="checkbox" role="switch" id="branch-status" wire:model.live="branchStatus">
                            <div class="valid-feedback">
                                Status kantor cabang {{ $branchStatus ? 'OPEN' : 'CLOSE' }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:loading.attr="disabled">
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
            // HIDE ADD BRANCH MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-add-branch-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('add-branch-modal')).hide();

                setTimeout(function() {
                    window.bootstrap.Alert.getOrCreateInstance('#success-alert').close();
                }, 5000)
            });

            // HIDE IMPORT BRANCH MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-import-branch-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('import-branch-modal')).hide();

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
