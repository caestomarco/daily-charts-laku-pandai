<div class="container-fluid d-flex flex-column gap-2 me-0" style="width: 83%">

    {{-- BREADCRUMBS --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-between mx-4 mt-4">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
            <span class="fw-semibold">{{ now()->translatedFormat('l, d F Y') }}</span>
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
        <h3 class="mb-0 text-warning-emphasis fw-bold">Dashboard LAKU PANDAI</h3>
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
                {{ $selectedChart }}
            </button>
            <ul class="dropdown-menu">
                @foreach ($chartList as $chart)
                    <li wire:click="setChart('{{ $chart }}')"><a class="dropdown-item" href="#">{{ $chart }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- CHART TRANSAKSI HARIAN, TRANSAKSI PRODUK TERBESAR, STATUS TRANSAKSI --}}
    <section @class([
        'mx-4 p-3 rounded-3',
        'justify-content-between bg-light-subtle shadow d-flex align-items-center' => true,
    ]) :class="{ 'd-none': $wire.selectedChart === 'Transaksi Agen Terbesar' }">
        <div @class([
            'col-7' => $selectedChart === 'Status Transaksi',
            'w-100 flex-grow-1 flex-fill' => $selectedChart !== 'Status Transaksi',
        ]) >
            {{-- CHART CANVAS --}}
            <canvas id="chart" class="mb-4 rounded-4" style="min-height: 74vh; max-height: 74vh" wire:ignore.self></canvas>

            {{-- INFORMASI DASHBOARD --}}
            @if ($selectedChart === 'Transaksi Harian')
                <div class="alert alert-primary mb-0">
                    <h3 class="fw-semibold">Informasi Dashboard</h3>
                    <ul class="mb-0">
                        <li>Data yang digunakan adalah data transaksi <strong>LAKU PANDAI</strong> pada tanggal <strong>{{ $currentDate->translatedFormat('l, d F Y') }}</strong> hingga pukul
                            <strong>16.00
                                WIB</strong>.
                        </li>
                        <li>Tercatat <strong>{{ number_format($totalToday['transactions'], '0', '.', ',') }} transaksi</strong> dengan total nominal
                            <strong>{{ number_format($totalToday['nominals'], '0', '.', ',') }}</strong>.
                        </li>
                        <li>Fitur yang paling banyak ditransaksikan adalah <strong>{{ $todayTopTransaction['product_name'] }}</strong> sebanyak <strong>{{ $todayTopTransaction['product_count'] }}
                                transaksi</strong>.</li>
                        <li>Agen yang melakukan transaksi terbanyak adalah agen di bawah <strong>Unit Kantor {{ $todayTopTransaction['agent_branch'] }} </strong> sebanyak
                            <strong>{{ $todayTopTransaction['agent_count'] }} transaksi</strong> dengan total nominal
                            <strong>{{ number_format($todayTopTransaction['agent_nominals'], '0', '.', ',') }}</strong> dari total <strong>{{ $todayTopTransaction['agent_total'] }} agen</strong>
                            yang melakukan transaksi.
                        </li>
                        <li>Tren transaksi <strong>{{ $isChartStonk ? 'NAIK' : 'TURUN' }}</strong> dibanding hari sebelumnya.</li>
                        <li><strong>{{ $isThereNewAgent ? 'ADA' : 'TIDAK ADA' }}</strong> penambahan agen SUMUT LINK.</li>
                    </ul>
                </div>
            @endif

            @if ($selectedChart === 'Transaksi Produk Terbesar' && !$chartDatasets)
                <div class="alert alert-warning mb-0" role="alert">
                    Belum ada data transaksi pada tanggal {{ $currentDate }}.
                </div>
            @endif
        </div>

        {{-- TABEL STATUS TRANSAKSI --}}
        @if ($selectedChart === 'Status Transaksi')
            <div @class([
                'col-4' => true,
            ])>
                <table class="table my-auto">
                    <thead class="table-dark">
                        <tr>
                            <th>Status</th>
                            <th>Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (array_combine($chartLabels, $chartDatasets[0]['data']) as $key => $value)
                            <tr @class([
                                'table-success' => $key === 'SUCCESS',
                                'table-danger' => $key === 'FAILED',
                                'table-secondary' => $key === 'SUSPECT',
                            ])>
                                <td class="w-50">{{ $key }}</td>
                                <td class="w-50">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- TABLE TRANSAKSI AGEN TERBESAR --}}
    @if ($selectedChart === 'Transaksi Agen Terbesar')
        <div @class([
            'mx-4 p-3 rounded-3' => true,
            'bg-light-subtle shadow' => true,
        ]) >
            @if ($topTenAgentTransactions)
                <h2 class="fw-bold text-center">Tabel 10 Transaksi Agen Terbesar - {{ $currentDate->translatedFormat('d/m/Y') }}</h2>
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Kantor Cabang</th>
                            <th>Rekening Agen</th>
                            <th>Nama Agen</th>
                            <th>Total Transaksi</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topTenAgentTransactions as $transaction)
                            <tr class="table-warning">
                                <td>{{ $transaction['branch']->name }}</td>
                                <td>{{ $transaction['account'] }}</td>
                                <td>{{ $transaction['name'] }}</td>
                                <td>{{ $transaction['transaction'] }}</td>
                                <td>{{ $transaction['nominal'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning mb-0" role="alert">
                    Belum ada data transaksi pada tanggal {{ $currentDate }}.
                </div>
            @endif
        </div>
    @endif

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
</div>

@push('additional-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    {{-- MODAL & ALERT LISTENER --}}
    <script>
        (() => {
            // HIDE IMPORT TRANSACTION MODAL & SUCCESS ALERT ON SUCCESSFUL SUBMISSION
            window.addEventListener('hide-import-daily-transaction-modal', (event) => {
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

    {{-- GRAFIK TRANSAKSI HARIAN & TRANSAKSI PRODUK TERBESAR --}}
    <script>
        let chart;

        function initializeCharts(selected_chart, labels, datasets) {
            Chart.defaults.font.size = 14;
            Chart.defaults.font.weight = 'lighter';
            Chart.defaults.color = '#010101';
            ChartDataLabels.defaults.font.size = 20;

            const customBackgroundPlugin = {
                id: 'customCanvasBackgroundColor',
                beforeDraw: (chart) => {
                    const context = chart.canvas.getContext('2d');
                    context.save();
                    context.globalCompositeOperation = 'destination-over';
                    context.fillStyle = '#4C4C4C';
                    context.fillRect(0, 0, chart.width, chart.height);
                    context.restore();
                }
            };

            if (selected_chart === 'Transaksi Harian') {
                Chart.getChart('chart') ? Chart.getChart('chart').destroy() : null;

                chart = new Chart(document.getElementById('chart'), {
                    plugins: [ChartDataLabels, customBackgroundPlugin],
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 20,
                                right: 20,
                                bottom: 20,
                                left: 20
                            }
                        },
                        scales: {
                            x: {
                                border: {
                                    display: false,
                                    width: 23
                                },
                                grid: {
                                    display: false,
                                    drawOnChartArea: false,
                                    drawTicks: false,
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: {
                                    // ONLY SHOW ONE GRID LINE AT ONE TIME
                                    drawOnChartArea: true,
                                    lineWidth: 1,
                                    color: '#FFFFFF4D',
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    // ONLY SHOW ONE GRID LINE AT ONE TIME
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                }
                            },
                        },
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                clamp: true,
                                align: 'top',
                                color: '#FFFFFF',
                                labels: {
                                    title: {
                                        font: {
                                            size: 20,
                                            weight: 'lighter',
                                        }
                                    },
                                    value: {
                                        color: '#FFFFFF'
                                    }
                                },
                                // ONLY SHOW LABELS ON LINE CHART
                                display: function(context) {
                                    return context.dataset.type === 'line';
                                },
                                // CONVERT NOMINAL TO CURRENCY FORMAT
                                formatter: function(value, context) {
                                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                }
                            },
                            legend: {
                                labels: {
                                    color: '#FFFFFF',
                                },
                                position: 'bottom',
                            },
                            title: {
                                color: '#FFFFFF',
                                display: true,
                                text: 'Grafik Transaksi Harian Bulan ' + @json($currentDate->translatedFormat('F Y')),
                                font: {
                                    size: 32,
                                    weight: 'bold',
                                }
                            }
                        },
                    }
                });
            } else if (selected_chart === 'Transaksi Produk Terbesar') {
                Chart.getChart('chart').destroy();

                chart = new Chart(document.getElementById('chart'), {
                    type: 'bar',
                    plugins: [ChartDataLabels],
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                clamp: false,
                                align: 'right',
                                color: '#010101',
                                labels: {
                                    title: {
                                        font: {
                                            size: 20,
                                            weight: 'lighter',
                                        }
                                    },
                                },
                                // CONVERT NOMINAL TO CURRENCY FORMAT
                                formatter: function(value, context) {
                                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            },
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'Grafik 10 Transaksi Produk Terbesar - ' + @json($currentDate->format('d/m/Y')),
                                font: {
                                    size: 32,
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            // x: {
                            //     type: 'linear',
                            //     display: true,
                            //     grid: {
                            //         // ONLY SHOW ONE GRID LINE AT ONE TIME
                            //         drawOnChartArea: false,
                            //         lineWidth: 1,
                            //     },
                            // },
                            // y: {
                            //     type: 'linear',
                            //     display: true,
                            //     grid: {
                            //         // ONLY SHOW ONE GRID LINE AT ONE TIME
                            //         drawOnChartArea: false,
                            //         lineWidth: 1,
                            //     },
                            // },
                        },
                    }
                });
            } else if (selected_chart === 'Status Transaksi') {
                Chart.getChart('chart').destroy();

                chart = new Chart(document.getElementById('chart'), {
                    type: 'pie',
                    plugins: [ChartDataLabels],
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        radius: '90%',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                clamp: true,
                                align: 'top',
                                color: '#010101',
                                formatter: (value, context) => {
                                    const datapoints = context.chart.data.datasets[0].data
                                    const total = datapoints.reduce((total, datapoint) => total + datapoint, 0)
                                    const percentage = value / total * 100
                                    return percentage.toFixed(2) + "%";
                                },
                            },
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Grafik Status Transaksi - ' + @json($currentDate->format('d/m/Y')),
                                font: {
                                    size: 32,
                                    weight: 'bolder'
                                }
                            }
                        },
                    }
                });
            }
        }

        function updateChart(data) {
            data.forEach(element => {
                initializeCharts(element.selectedChart, element.labels, element.datasets);
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            initializeCharts(@json($selectedChart), @json($chartLabels), @json($chartDatasets));

            Livewire.on('update-daily-transactions-chart', data => {
                updateChart(data);
            });

            Livewire.on('update-top-ten-product-transactions-chart', data => {
                updateChart(data);
            });

            Livewire.on('update-today-transactions-status-chart', data => {
                updateChart(data);
            });
        });
    </script>
@endpush
