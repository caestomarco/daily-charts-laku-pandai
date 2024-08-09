<div class="container-fluid d-flex flex-column gap-2 me-0" style="width: 83%">

    {{-- BREADCRUMBS --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mx-4 mt-4">
            <li class="breadcrumb-item" aria-current="page"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Riwayat Transaksi</li>
        </ol>
    </nav>

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between mx-4 p-3 rounded-3 bg-light-subtle shadow">
        <h3 class="mb-0 text-warning-emphasis fw-bold">Riwayat Transaksi Laku Pandai</h3>
        <div>
            
        </div>
    </div>

</div>

@push('additional-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
@endpush
