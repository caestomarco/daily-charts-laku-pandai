<div @class(['container-fluid d-flex flex-column', 'gap-2 me-0' => true]) style="width: 83%">

    {{-- BREADCRUMBS --}}
    <nav aria-label="breadcrumb">
        <ol @class(['breadcrumb ', 'mx-4 mt-4' => true]) class="">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
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
        <h3 @class(['mb-0', 'text-warning-emphasis fw-bold' => true])>Dashboard</h3>
        <div>
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#import-transaction-modal">
                Import Data Transaksi
            </button>
            <button class="btn btn-outline-primary dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-range mb-1" viewBox="0 0 16 16">
                    <path d="M9 7a1 1 0 0 1 1-1h5v2h-5a1 1 0 0 1-1-1M1 9h4a1 1 0 0 1 0 2H1z"></path>
                    <path
                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z">
                    </path>
                </svg>
                {{ $selectedFilter }}
            </button>
            <ul class="dropdown-menu">
                @foreach ($filterList as $filter)
                    <li wire:click="setFilter('{{ $filter }}')"><a class="dropdown-item" href="#">{{ $filter }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- CHART --}}
    <div @class(['mx-4 p-3 rounded-3 flex-basis-75', 'bg-light-subtle shadow-sm' => true])>
        <canvas id="chart"></canvas>
    </div>

    {{-- IMPORT TRANSACTION MODAL --}}
    <div class="modal fade" id="import-transaction-modal" tabindex="-1" aria-labelledby="import-transaction-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="import-transaction-label">Import Transaksi Hari Ini</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="submitFile" class="needs-validation justify-content-end" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">
                        {{-- GUIDANCE --}}
                        <div class="alert alert-warning" role="alert">
                            Silakan upload data transaksi harian dengan meng-upload file .xlsx seperti contoh <a href="#" class="alert-link">berikut</a>.
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
</div>

@push('additional-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chart = new Chart(
            document.getElementById('chart'), {
                type: 'bar',
                data: {
                    labels: @json($labels[0]),
                    datasets: @json($dataset)
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Grafik Transaksi Harian',
                            font: {
                                size: 32
                            }
                        }
                    },
                    responsive: true,
                }
            }
        );
        Livewire.on('updateChart', data => {
            chart.data = data;
            chart.update();
        });
    </script>
    <script>
        (() => {
            // HIDE IMPORT TRANSACTION MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-import-transaction-modal', (event) => {
                window.bootstrap.Modal.getInstance(document.getElementById('import-transaction-modal')).hide();

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
        })
        ();
    </script>
@endpush
