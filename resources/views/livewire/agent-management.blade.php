<div class="container-fluid d-flex flex-column gap-2 me-0" style="width: 83%">

    {{-- BREADCRUMBS --}}
    <nav aria-label="breadcrumb">
        <ol @class(['breadcrumb ', 'mx-4 mt-4' => true]) class="">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen Agen</li>
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
        <h3 class="mb-0 text-warning-emphasis fw-bold">Manajemen Agen</h3>
        <div>
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#import-agent-modal">
                Import Data Agen
            </button>
            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#add-agent-modal">
                Tambah Agen Baru
            </button>
        </div>
    </div>

    {{-- POWERGRID TABLE --}}
    <div class="mx-4 p-3 rounded-3 bg-light-subtle shadow">
        @livewire('agent-table')
    </div>

    {{-- IMPORT AGENT MODAL --}}
    <div class="modal fade" id="import-agent-modal" tabindex="-1" aria-labelledby="import-agent-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="import-agent-label">Import Agen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="submitFile" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Anda dapat menambahkan data agen secara <span class="fst-italic">otomatis</span> dengan meng-upload file .xlsx seperti contoh <a href="#"
                                class="alert-link">berikut</a>.
                        </div>

                        {{-- FILE --}}
                        <input class="form-control @error('file') is-invalid @elseif($fileValidated) is-valid @enderror" id="validationCustom01" type="file" wire:model.live="file">
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

    {{-- ADD AGENT MODAL --}}
    <div class="modal fade" id="add-agent-modal" tabindex="-1" aria-labelledby="add-agent-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="add-agent-label">Tambah Agen Baru</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="addNewAgent" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    {{-- FORM CONTENT --}}
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Anda dapat menambahkan data agen secara <span class="fst-italic">manual</span> dengan <br> mengisi form berikut.
                        </div>

                        {{-- ID --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('agentID') is-invalid @elseif($agentIDValidated) is-valid @enderror" id="agent-id" placeholder="" type="text"
                                wire:model.blur="agentID">
                            <label for="agent-id">Kode Agen</label>
                            <div class="valid-feedback">
                                Kode agen valid!
                            </div>
                            @error('agentID')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                            <div id="agent-id-example" class="form-text">Contoh Kode Agen: 200722000001 (12 Digit)</div>
                        </div>

                        {{-- ACCOUNT --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('agentAccount') is-invalid @elseif($agentAccountValidated) is-valid @enderror" id="agent-account" placeholder="" type="text"
                                wire:model.blur="agentAccount">
                            <label for="agent-account">Rekening Agen</label>
                            <div class="valid-feedback">
                                Rekening agen valid!
                            </div>
                            @error('agentAccount')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                            <div id="agent-account-example" class="form-text">Contoh Rekening Agen: 23402030081916 (14 Digit)</div>
                        </div>

                        {{-- NAME --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('agentName') is-invalid @elseif($agentNameValidated) is-valid @enderror" id="agent-name" placeholder="" type="text"
                                wire:model.blur="agentName">
                            <label for="agent-name">Nama Agen</label>
                            <div class="valid-feedback">
                                Nama agen valid!
                            </div>
                            @error('agentName')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                        </div>

                        {{-- BRANCH --}}
                        <div class="form-floating mb-3">
                            <input class="form-control @error('branchID') is-invalid @elseif($branchIDValidated) is-valid @enderror" list="branchList" id="branch-list-search"
                                placeholder="Type to search..." wire:model.blur="branchID">
                            <label for="branch-list-search">Kantor Cabang</label>
                            <datalist id="branchList">
                                @foreach ($branchList as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </datalist>
                            <div class="valid-feedback">
                                Kantor cabang valid!
                            </div>
                            @error('branchID')
                                <div class="invalid-feedback"> {{ $message }} </div>
                            @enderror
                        </div>

                        {{-- STATUS --}}
                        <div class="form-inline form-check form-switch">
                            <label class="form-check-label" for="branch-status">Status: {{ $agentStatus ? 'ACTIVE' : 'CLOSE' }}</label>
                            <input class="form-check-input is-valid" type="checkbox" role="switch" id="agent-status" wire:model.live="agentStatus">
                            <div class="valid-feedback">
                                Status keaktifkan {{ $agentStatus ? 'ACTIVE' : 'CLOSE' }}
                            </div>
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
            // HIDE ADD AGENT MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-add-agent-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('add-agent-modal')).hide();

                setTimeout(function() {
                    window.bootstrap.Alert.getOrCreateInstance('#success-alert').close();
                }, 5000)
            });

            // HIDE IMPORT AGENT MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-import-agent-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('import-agent-modal')).hide();

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
