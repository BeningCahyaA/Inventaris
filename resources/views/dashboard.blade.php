<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800">Dashboard</h2>
    </x-slot>

    @php
    $canManageAssets = in_array(auth()->user()->role, ['admin', 'super_admin'], true);
    $isSuperAdmin = auth()->user()->role === 'super_admin';
    $hasAlerts = $overdueCount + $warrantyExpiringCount + $warrantyExpiredCount + $maintenanceAgingCount > 0;
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <section class="dashboard-welcome rounded-2xl p-5 sm:p-7" aria-labelledby="dashboard-intro">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="min-w-0">
                    <p class="text-xs font-semibold tracking-wider text-purple-200 uppercase">Ringkasan inventaris</p>
                    <h1 id="dashboard-intro" class="mt-2 text-2xl sm:text-3xl font-bold break-words">Selamat datang, {{ auth()->user()->name }}</h1>
                    <p class="mt-2 text-sm text-purple-100 leading-relaxed">Pantau ketersediaan aset dan temukan pekerjaan yang perlu ditindaklanjuti.</p>
                </div>
                @if($canManageAssets)
                <a href="{{ route('assets.create') }}" class="dashboard-add-asset inline-flex shrink-0 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"><span aria-hidden="true" class="text-lg">+</span> Tambah aset</a>
                @endif
            </div>
        </section>

        @if($canManageAssets)
        <nav aria-label="Aksi cepat" class="flex flex-wrap gap-2">
            @foreach([
            ['url' => route('assets.index'), 'label' => 'Lihat semua aset →'],
            ['url' => route('scanner.index'), 'label' => 'Scan Barcode aset'],
            ['url' => route('assets.history'), 'label' => 'Riwayat peminjaman'],
            ['url' => $isSuperAdmin ? route('approvals.index') : route('approvals.my-tickets'), 'label' => $isSuperAdmin ? 'Proses persetujuan' : 'Lihat tiket saya'],
            ] as $shortcut)
            <a href="{{ $shortcut['url'] }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:border-purple-400 hover:text-purple-700 focus-visible:outline-2 focus-visible:outline-purple-600">{{ $shortcut['label'] }}</a>
            @endforeach
        </nav>
        @endif

        <section aria-labelledby="overview-title">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h2 id="overview-title" class="text-lg font-bold text-gray-900">Ringkasan aset</h2>
                <p id="dashboard-update-status" class="text-xs text-gray-500" role="status">Memuat pembaruan data…</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <p class="text-sm font-medium text-gray-600">Total aset</p>
                    <p id="stat-total-assets" class="mt-2 text-3xl font-bold text-gray-900 tabular-nums">{{ $totalAssets }}</p>
                    <p class="mt-2 text-xs text-gray-500">Jumlah aset yang tercatat</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 min-w-0">
                    <p class="text-sm font-medium text-gray-600">Total nilai aset</p>
                    <p id="stat-total-value" class="mt-2 text-xl lg:text-2xl font-bold text-gray-900 tabular-nums break-words">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-gray-500">Berdasarkan harga pembelian</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <p class="text-sm font-medium text-gray-600">Menunggu persetujuan</p>
                    <p id="stat-pending-tickets" class="mt-2 text-3xl font-bold {{ $pendingCount > 0 ? 'text-red-600' : 'text-gray-900' }} tabular-nums">{{ $pendingCount }}</p>
                    <p class="mt-2 text-xs text-gray-500">Tiket aset dan kategori dari seluruh admin</p>
                </div>
            </div>
        </section>

        <section id="lifecycle-alerts-section" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5" aria-labelledby="attention-title">
            <h2 id="attention-title" class="text-lg font-bold text-gray-900">Perlu perhatian</h2>
            <p class="mt-1 text-sm text-gray-500">Periksa pinjaman, masa garansi, dan perawatan aset.</p>
            <p id="dashboard-no-alerts" class="{{ $hasAlerts ? 'hidden' : '' }} mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">Tidak ada peringatan pinjaman, garansi, atau perawatan saat ini.</p>
            <div id="dashboard-alert-cards" class="{{ $hasAlerts ? '' : 'hidden' }} grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mt-4">
                @php
                $alertCards = [
                ['id' => 'overdue', 'count' => $overdueCount, 'label' => 'Pinjaman terlambat', 'hint' => 'Sudah melewati jatuh tempo', 'style' => 'border-red-500 text-red-600', 'url' => route('assets.history', ['status' => 'overdue'])],
                ['id' => 'warranty-expiring', 'count' => $warrantyExpiringCount, 'label' => 'Garansi segera habis', 'hint' => 'Berakhir dalam 30 hari', 'style' => 'border-amber-500 text-amber-600', 'url' => route('assets.index', ['warranty_state' => 'expiring'])],
                ['id' => 'warranty-expired', 'count' => $warrantyExpiredCount, 'label' => 'Garansi sudah habis', 'hint' => 'Periksa kebutuhan perlindungan', 'style' => 'border-orange-500 text-orange-600', 'url' => route('assets.index', ['warranty_state' => 'expired'])],
                ['id' => 'maintenance-aging', 'count' => $maintenanceAgingCount, 'label' => 'Perawatan lama', 'hint' => 'Tercatat sejak 7 hari atau lebih', 'style' => 'border-purple-500 text-purple-600', 'url' => route('assets.index', ['status' => 'maintenance'])],
                ];
                @endphp
                @foreach($alertCards as $card)
                <div id="alert-{{ $card['id'] }}-container" class="rounded-lg border-l-4 p-4 bg-gray-50 {{ $card['count'] > 0 ? $card['style'] : 'border-gray-200 text-gray-600' }}">
                    <p class="text-sm font-semibold text-gray-700">{{ $card['label'] }}</p>
                    <p id="stat-{{ $card['id'] }}" class="mt-2 text-2xl font-bold tabular-nums">{{ $card['count'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $card['hint'] }}</p>
                    @if($canManageAssets)
                    <a href="{{ $card['url'] }}" class="inline-block mt-3 py-1 text-xs font-semibold text-purple-700 hover:underline focus-visible:outline-2 focus-visible:outline-purple-600">{{ $card['id'] === 'maintenance-aging' ? 'Lihat aset dalam perawatan' : 'Lihat detail' }} →</a>
                    @endif
                </div>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="asset-status-title">
            <h2 id="asset-status-title" class="text-lg font-bold text-gray-900">Ketersediaan aset</h2>
            <p class="mt-1 mb-3 text-sm text-gray-500">{{ $canManageAssets ? 'Pilih status untuk melihat daftar asetnya.' : 'Jumlah aset berdasarkan status saat ini.' }}</p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                @php
                $statusCards = [
                ['status' => 'available', 'id' => 'available', 'label' => 'Tersedia', 'hint' => 'Siap digunakan', 'value' => $statusAvailable, 'style' => 'border-emerald-200 bg-emerald-50 text-emerald-800'],
                ['status' => 'in_use', 'id' => 'in-use', 'label' => 'Sedang digunakan', 'hint' => 'Dipinjam atau digunakan', 'value' => $statusInUse, 'style' => 'border-blue-200 bg-blue-50 text-blue-800'],
                ['status' => 'maintenance', 'id' => 'maintenance', 'label' => 'Dalam perawatan', 'hint' => 'Sedang diperbaiki', 'value' => $statusMaintenance, 'style' => 'border-amber-200 bg-amber-50 text-amber-800'],
                ['status' => 'broken', 'id' => 'broken', 'label' => 'Rusak', 'hint' => 'Tidak dapat digunakan', 'value' => $statusBroken, 'style' => 'border-red-200 bg-red-50 text-red-800'],
                ];
                @endphp
                @foreach($statusCards as $card)
                <div class="relative rounded-xl border p-4 sm:p-5 {{ $card['style'] }} {{ $canManageAssets ? 'hover:shadow-md transition-shadow' : '' }}">
                    <h3 class="text-sm font-semibold">{{ $card['label'] }}</h3>
                    <p id="stat-status-{{ $card['id'] }}" class="mt-2 text-3xl font-bold tabular-nums">{{ $card['value'] }}</p>
                    <p class="mt-2 text-xs">{{ $card['hint'] }}</p>
                    @if($canManageAssets)
                    <a href="{{ route('assets.index', ['status' => $card['status']]) }}" class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600" aria-label="Lihat aset: {{ $card['label'] }}"></a>
                    @endif
                </div>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="asset-analysis-title">
            <h2 id="asset-analysis-title" class="text-lg font-bold text-gray-900">Komposisi dan perkembangan aset</h2>
            <p class="mt-1 mb-3 text-sm text-gray-500">Bandingkan kategori, status, dan penambahan aset selama 12 bulan terakhir.</p>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
                    <h3 class="text-sm font-semibold text-gray-800">Jumlah aset per kategori</h3>
                    <p class="chart-description">Diurutkan dari jumlah terbanyak. Arahkan ke batang untuk detail.</p>
                    <p id="category-chart-empty" class="{{ $categoryStats->sum('total') > 0 ? 'hidden' : '' }} chart-empty">Belum ada aset dalam kategori yang tercatat.</p>
                    <div class="category-chart-scroll">
                        <div id="category-chart-area" class="category-chart-area">
                            <canvas id="categoryBarChart" aria-label="Grafik jumlah aset per kategori" role="img" data-labels="{{ $categoryStats->pluck('name')->toJson() }}" data-values="{{ $categoryStats->pluck('total')->toJson() }}">Jumlah aset setiap kategori tersedia dalam rincian kategori di bawah.</canvas>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
                    <h3 class="text-sm font-semibold text-gray-800">Perbandingan status aset</h3>
                    <p class="chart-description">Jumlah dan persentase dari seluruh aset.</p>
                    <p id="status-chart-empty" class="{{ $totalAssets > 0 ? 'hidden' : '' }} chart-empty">Belum ada aset. Tambahkan aset untuk melihat perbandingan.</p>
                    <div class="status-chart-layout">
                        <div class="status-chart-area">
                            <canvas id="statusDonutChart" aria-label="Grafik perbandingan status aset" role="img" data-values="{{ json_encode([$statusAvailable, $statusInUse, $statusMaintenance, $statusBroken]) }}">Jumlah tiap status tersedia pada bagian ketersediaan aset.</canvas>
                            <div class="status-chart-center" aria-hidden="true"><strong id="chart-status-total">{{ $totalAssets }}</strong><span>Total aset</span></div>
                        </div>
                        <ul class="status-chart-legend" aria-label="Rincian status aset">
                            @foreach($statusCards as $card)
                            <li><span class="chart-status-label"><span class="chart-status-dot chart-dot-{{ $card['id'] }}" aria-hidden="true"></span>{{ $card['label'] }}</span><span class="chart-status-values"><strong id="chart-count-{{ $card['id'] }}">{{ $card['value'] }}</strong><span id="chart-percent-{{ $card['id'] }}">{{ $totalAssets > 0 ? number_format($card['value'] / $totalAssets * 100, 1, ',', '.') : '0' }}%</span></span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 xl:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-800">Penambahan aset per bulan</h3>
                    <p class="chart-description">12 bulan terakhir · Angka menunjukkan aset yang ditambahkan, bukan total aset.</p>
                    <div class="trend-chart-area">
                        <canvas id="trendLineChart" aria-label="Grafik penambahan aset selama 12 bulan terakhir" role="img" data-labels="{{ json_encode($trendLabels) }}" data-values="{{ json_encode($trendData) }}">Grafik jumlah aset yang ditambahkan per bulan.</canvas>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <section class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5" aria-labelledby="physical-title">
                <h2 id="physical-title" class="text-base font-bold text-gray-900">Kondisi fisik aset</h2>
                <p class="mt-1 mb-4 text-xs text-gray-500">Kondisi barang, terpisah dari status pemakaiannya.</p>
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-emerald-50 p-3 text-emerald-800">
                        <p class="text-xs font-semibold">Baik</p>
                        <p id="stat-cond-baik" class="mt-2 text-2xl font-bold tabular-nums">{{ $condBaik }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-3 text-amber-800">
                        <p class="text-xs font-semibold">Rusak</p>
                        <p id="stat-cond-rusak" class="mt-2 text-2xl font-bold tabular-nums">{{ $condRusak }}</p>
                    </div>
                    <div class="rounded-lg bg-red-50 p-3 text-red-800">
                        <p class="text-xs font-semibold">Rusak total</p>
                        <p id="stat-cond-rusak-total" class="mt-2 text-2xl font-bold tabular-nums">{{ $condRusakTotal }}</p>
                    </div>
                </div>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5" aria-labelledby="category-title">
                <h2 id="category-title" class="text-base font-bold text-gray-900 mb-4">Rincian kategori</h2>
                <p class="text-xs text-gray-500 mb-3">{{ $canManageAssets ? 'Pilih nama kategori untuk melihat daftar asetnya.' : 'Jumlah aset di setiap kategori.' }}</p>
                <div id="category-list-container" class="dashboard-category-list">
                    @forelse($categoryStats as $cat)
                    <div class="dashboard-category-row category-stat-item" data-slug="{{ $cat->slug }}">
                        @if($canManageAssets)
                        <a href="{{ route('assets.index', ['category' => $cat->slug]) }}" class="text-sm text-gray-700 hover:text-purple-700 hover:underline break-words focus-visible:outline-2 focus-visible:outline-purple-600">{{ $cat->name }}</a>
                        @else
                        <span class="text-sm text-gray-700 break-words">{{ $cat->name }}</span>
                        @endif
                        <span class="text-sm font-bold text-purple-700 shrink-0"><span class="category-total">{{ $cat->total }}</span> <span class="text-xs font-normal">aset</span></span>
                    </div>
                    @empty
                    <p class="col-span-full text-sm text-gray-500">Belum ada kategori yang tercatat.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col mt-6">
            <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base md:text-lg font-bold text-gray-800">Riwayat Aktivitas</h3>
            </div>
            <div class="p-4 md:p-6 flex-1">
                @if($logs->count() > 0)
                <div class="space-y-3 md:space-y-4">
                    @foreach($logs as $log)
                    <div class="border border-gray-200 rounded-lg p-3 md:p-4 hover:border-purple-200 hover:shadow-sm transition-all bg-white">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1 sm:gap-2 mb-1.5 sm:mb-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-bold text-gray-900 text-sm">{{ $log->ref_name }}</h4>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $log->log_type === 'category' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' }}">{{ $log->log_type === 'category' ? 'Kategori' : 'Aset' }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 font-mono">{{ $log->created_at->timezone('Asia/Jakarta')->format('d/m/Y, H:i') }}</span>
                        </div>
                        <p class="text-[11px] md:text-xs text-gray-500 leading-relaxed mb-2">
                            Perubahan diajukan oleh <b>{{ $log->user->name ?? 'Sistem' }}</b>. Status:
                            <span class="font-bold {{ $log->status === 'approved' ? 'text-emerald-600' : ($log->status === 'pending' ? 'text-amber-500' : 'text-red-500') }} uppercase">{{ match($log->status) { 'approved' => 'Disetujui', 'pending' => 'Menunggu', 'rejected' => 'Ditolak', default => $log->status } }}</span>
                        </p>
                        <div class="text-[10px] md:text-[11px] text-gray-400 flex flex-wrap items-center gap-1">
                            Aksi: <span class="uppercase font-bold">{{ match($log->action) { 'create' => 'Tambah', 'update' => 'Edit', 'delete' => 'Hapus', 'check_out' => 'Pinjam', 'check_in' => 'Kembali', 'maintenance' => 'Perbaikan', default => $log->action } }}</span> <span class="hidden sm:inline">·</span><span class="sm:hidden w-full"></span> Oleh: <span class="uppercase">{{ str_replace('_', ' ', $log->user->role ?? '-') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
                @else
                <div class="h-full flex flex-col items-center justify-center text-gray-400 py-10">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-sm">Belum ada riwayat aktivitas.</p>
                </div>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fontFamily = "'Poppins', 'Segoe UI', sans-serif";
            const formatNumber = value => new Intl.NumberFormat('id-ID').format(Number(value) || 0);

            Chart.defaults.font.family = fontFamily;
            Chart.defaults.font.size = 12;
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.pointStyleWidth = 10;

            let categoryChart = null;
            let statusChart = null;
            let trendChart = null;

            // Shared styling keeps all three diagrams consistent.
            const tooltipStyle = {
                backgroundColor: '#0f172a',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    weight: '600'
                },
                displayColors: false,
            };
            const integerAxis = {
                beginAtZero: true,
                border: {
                    display: false
                },
                grid: {
                    color: '#f1f5f9'
                },
                ticks: {
                    precision: 0,
                    maxTicksLimit: 6,
                    color: '#64748b',
                    font: {
                        size: 11
                    }
                },
            };
            const catCtx = document.getElementById('categoryBarChart');

            function categorySeries(labels, values) {
                return labels.map((name, i) => ({
                        name,
                        total: Number(values[i]) || 0
                    }))
                    .sort((a, b) => b.total - a.total || a.name.localeCompare(b.name, 'id'));
            }

            function sizeCategoryChart(series) {
                document.getElementById('category-chart-area').style.height = Math.max(260, series.length * 38 + 40) + 'px';
                document.getElementById('category-chart-empty').classList.toggle('hidden', series.some(c => c.total > 0));
            }
            if (catCtx) {
                const series = categorySeries(JSON.parse(catCtx.dataset.labels || '[]'), JSON.parse(catCtx.dataset.values || '[]'));
                sizeCategoryChart(series);
                categoryChart = new Chart(catCtx, {
                    type: 'bar',
                    data: {
                        labels: series.map(c => c.name),
                        datasets: [{
                            label: 'Jumlah aset',
                            data: series.map(c => c.total),
                            backgroundColor: '#6366f1',
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 5,
                            maxBarThickness: 20,
                            borderSkipped: false
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                right: 12
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => `${formatNumber(ctx.parsed.x)} aset`
                                }
                            }
                        },
                        scales: {
                            x: {
                                ...integerAxis,
                                title: {
                                    display: true,
                                    text: 'Jumlah aset',
                                    color: '#64748b',
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            y: {
                                border: {
                                    display: false
                                },
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    autoSkip: false,
                                    color: '#334155',
                                    font: {
                                        size: 11
                                    },
                                    callback: function(value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 22 ? label.slice(0, 21) + '…' : label;
                                    }
                                }
                            },
                        },
                    },
                });
            }

            const statusCtx = document.getElementById('statusDonutChart');

            function updateStatusLegend(values) {
                const total = values.reduce((sum, value) => sum + Number(value), 0);
                document.getElementById('chart-status-total').textContent = formatNumber(total);
                ['available', 'in-use', 'maintenance', 'broken'].forEach((id, i) => {
                    document.getElementById('chart-count-' + id).textContent = formatNumber(values[i]);
                    document.getElementById('chart-percent-' + id).textContent = new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 1
                    }).format(total ? Number(values[i]) / total * 100 : 0) + '%';
                });
            }
            if (statusCtx) {
                const statusData = JSON.parse(statusCtx.dataset.values || '[]').map(Number);
                updateStatusLegend(statusData);
                statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tersedia', 'Sedang digunakan', 'Dalam perawatan', 'Rusak'],
                        datasets: [{
                            data: statusData,
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '74%',
                        layout: {
                            padding: 6
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => `${formatNumber(ctx.parsed)} aset`
                                }
                            }
                        }
                    },
                });
            }

            const trendCtx = document.getElementById('trendLineChart');
            if (trendCtx) {
                trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: JSON.parse(trendCtx.dataset.labels || '[]'),
                        datasets: [{
                            label: 'Aset ditambahkan',
                            data: JSON.parse(trendCtx.dataset.values || '[]').map(Number),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#6366f1',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointHitRadius: 12,
                            fill: true,
                            tension: 0.2
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => `${formatNumber(ctx.parsed.y)} aset ditambahkan`
                                }
                            }
                        },
                        scales: {
                            x: {
                                border: {
                                    display: false
                                },
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: 6,
                                    maxRotation: 0,
                                    minRotation: 0,
                                    color: '#64748b',
                                    font: {
                                        size: 11
                                    },
                                    callback: function(value) {
                                        return this.getLabelForValue(value);
                                    }
                                }
                            },
                            y: {
                                ...integerAxis,
                                title: {
                                    display: true,
                                    text: 'Jumlah aset',
                                    color: '#64748b',
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                        },
                    },
                });
            }

            function refreshDashboardData(data) {
                if (!data) {
                    fetch('{{ route("dashboard.stats") }}', {
                            headers: {
                                Accept: 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Statistik tidak tersedia');
                            return response.json();
                        })
                        .then(d => refreshDashboardData(d))
                        .catch(error => {
                            document.getElementById('dashboard-update-status').textContent = 'Pembaruan tertunda. Data terakhir tetap ditampilkan.';
                            console.error('Error fetching dashboard stats:', error);
                        });
                    return;
                }

                // 1. Update counters
                document.getElementById('stat-total-assets').innerText = data.totalAssets;
                document.getElementById('stat-total-value').innerText = 'Rp ' + formatNumber(data.totalValue);
                document.getElementById('dashboard-update-status').textContent = 'Diperbarui ' + new Date().toLocaleTimeString('en-GB', {
                    timeZone: 'Asia/Jakarta',
                    hour12: false
                }) + ' WIB';
                document.getElementById('status-chart-empty').classList.toggle('hidden', Number(data.totalAssets) > 0);

                const pendingTicketsEl = document.getElementById('stat-pending-tickets');
                pendingTicketsEl.innerText = data.pendingCount;
                if (data.pendingCount > 0) {
                    pendingTicketsEl.classList.add('text-red-600');
                    pendingTicketsEl.classList.remove('text-gray-900');
                } else {
                    pendingTicketsEl.classList.remove('text-red-600');
                    pendingTicketsEl.classList.add('text-gray-900');
                }

                document.getElementById('stat-status-available').innerText = data.statusAvailable;
                document.getElementById('stat-status-in-use').innerText = data.statusInUse;
                document.getElementById('stat-status-maintenance').innerText = data.statusMaintenance;
                document.getElementById('stat-status-broken').innerText = data.statusBroken;

                document.getElementById('stat-cond-baik').innerText = data.condBaik;
                document.getElementById('stat-cond-rusak').innerText = data.condRusak;
                document.getElementById('stat-cond-rusak-total').innerText = data.condRusakTotal;

                // 2. Update Charts
                if (categoryChart) {
                    const series = categorySeries(data.categoryStats.map(c => c.name), data.categoryStats.map(c => c.total));
                    sizeCategoryChart(series);
                    categoryChart.data.labels = series.map(c => c.name);
                    categoryChart.data.datasets[0].data = series.map(c => c.total);
                    categoryChart.resize();
                    categoryChart.update('none'); // Update without animation for smoother feedback
                }

                if (statusChart) {
                    statusChart.data.datasets[0].data = [
                        data.statusAvailable,
                        data.statusInUse,
                        data.statusMaintenance,
                        data.statusBroken
                    ].map(Number);
                    updateStatusLegend(statusChart.data.datasets[0].data);
                    statusChart.update('none');
                }

                if (trendChart) {
                    trendChart.data.labels = data.trendLabels;
                    trendChart.data.datasets[0].data = data.trendData;
                    trendChart.update('none');
                }

                // 3. Update Category List totals
                document.querySelectorAll('.category-stat-item').forEach(item => {
                    const slug = item.dataset.slug;
                    const catData = data.categoryStats.find(c => c.slug === slug);
                    if (catData) {
                        item.querySelector('.category-total').innerText = catData.total;
                    }
                });

                // 4. Update Lifecycle Alerts
                document.getElementById('stat-overdue').innerText = data.overdueCount;
                document.getElementById('stat-warranty-expiring').innerText = data.warrantyExpiringCount;
                document.getElementById('stat-warranty-expired').innerText = data.warrantyExpiredCount;
                document.getElementById('stat-maintenance-aging').innerText = data.maintenanceAgingCount;

                const totalAlerts = data.overdueCount + data.warrantyExpiringCount + data.warrantyExpiredCount + data.maintenanceAgingCount;
                document.getElementById('dashboard-alert-cards').classList.toggle('hidden', totalAlerts === 0);
                document.getElementById('dashboard-no-alerts').classList.toggle('hidden', totalAlerts > 0);

                // Update alert card styles
                const boxes = [{
                        id: 'alert-overdue-container',
                        statId: 'stat-overdue',
                        count: data.overdueCount,
                        color: 'red'
                    },
                    {
                        id: 'alert-warranty-expiring-container',
                        statId: 'stat-warranty-expiring',
                        count: data.warrantyExpiringCount,
                        color: 'amber'
                    },
                    {
                        id: 'alert-warranty-expired-container',
                        statId: 'stat-warranty-expired',
                        count: data.warrantyExpiredCount,
                        color: 'orange'
                    },
                    {
                        id: 'alert-maintenance-aging-container',
                        statId: 'stat-maintenance-aging',
                        count: data.maintenanceAgingCount,
                        color: 'purple'
                    }
                ];

                boxes.forEach(box => {
                    const el = document.getElementById(box.id);
                    const statEl = document.getElementById(box.statId);
                    if (box.count > 0) {
                        el.classList.add(`border-${box.color}-500`);
                        el.classList.remove('border-gray-200');
                        statEl.classList.add(`text-${box.color}-600`);
                    } else {
                        el.classList.remove(`border-${box.color}-500`);
                        el.classList.add('border-gray-200');
                        statEl.classList.remove(`text-${box.color}-600`);
                    }
                });
            }

            // ═══════════════════════════════════════════════
            // WebSocket Real-time via Laravel Echo
            // ═══════════════════════════════════════════════
            if (window.Echo) {
                console.log('Echo initialized, listening on dashboard channel...');
                window.Echo.channel('dashboard')
                    .listen('.stats.updated', (e) => {
                        console.log('Real-time update received:', e.stats);
                        refreshDashboardData(e.stats);
                    });
            } else {
                console.warn('Echo not found, falling back to 30s polling.');
            }

            // Also poll when Echo exists so a disconnected socket does not leave stale data.
            setInterval(() => {
                if (!document.hidden) refreshDashboardData();
            }, 30000);

            // Initial load
            refreshDashboardData();
        });
    </script>
    @endpush
</x-app-layout>