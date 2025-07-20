<x-layout>
    {{-- Alpine.js component untuk mengelola semua interaktivitas dashboard --}}
    <div class="dashboard-admin" x-data="dashboard()">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Keuangan</h1>
            <p class="text-gray-600">Ringkasan, analisis, dan performa keuangan yayasan.</p>
        </div>

        {{-- 1. Kartu Statistik Utama --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card-modern">
                <p class="text-sm font-medium text-gray-600">Total Pemasukan</p>
                <p class="text-2xl font-bold text-success-600">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="card-modern">
                <p class="text-sm font-medium text-gray-600">Total Pengeluaran</p>
                <p class="text-2xl font-bold text-danger-600">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="card-modern">
                <p class="text-sm font-medium text-gray-600">Saldo Akhir</p>
                <p class="text-2xl font-bold {{ $saldo >= 0 ? 'text-success-600' : 'text-danger-600' }}">Rp
                    {{ number_format($saldo, 0, ',', '.') }}</p>
            </div>
            <div class="card-modern">
                <p class="text-sm font-medium text-gray-600">Total Anggota</p>
                <p class="text-2xl font-bold text-primary-600">{{ number_format($totalAnggota) }}</p>
            </div>
        </div>

        {{-- 2. Grafik Keuangan --}}
        <div class="mb-8 card-modern">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Grafik Keuangan Tahunan</h2>
                <select x-model="tahunDipilih" @change="fetchChartData()"
                    class="form-select text-sm rounded-md border-gray-300">
                    <template x-for="tahun in tahunTersedia" :key="tahun">
                        <option :value="tahun" x-text="tahun"></option>
                    </template>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-1">
                    <h3 class="text-md font-semibold mb-2">Pemasukan & Cashflow</h3>
                    <canvas id="financialChart" style="height: 350px;"></canvas>
                </div>
                <div class="md:col-span-1">
                    <h3 class="text-md font-semibold mb-2">Pengeluaran</h3>
                    <canvas id="expenseChart" style="height: 350px;"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-md font-semibold mb-2">Pemasukan per Jenis Kas</h3>
                    <canvas id="doughnutChart" style="height: 350px;"></canvas>
                </div>
                <div class="md:col-span-1">
                    <h3 class="text-md font-semibold mb-2">Pengeluaran per Keterangan</h3>
                    <canvas id="pieChart" style="height: 350px;"></canvas>
                </div>
            </div>
        </div>

        {{-- 3. Iuran Bulan Ini --}}
        <div class="mb-8 card-modern">
            <h2 class="text-lg font-semibold mb-4">Iuran Bulan Ini</h2>
            <p class="text-sm text-gray-500 mb-2">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm">
                        <span>Sudah Bayar</span>
                        <span class="font-bold text-success-600">{{ $iuranBulanIni['total_sudah'] }} Anggota</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                        <div class="bg-success-600 h-2.5 rounded-full"
                            :style="{ width: ({{ $iuranBulanIni['total_sudah'] }} / {{ $totalAnggota ?: 1 }}) * 100 + '%' }">
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm">
                        <span>Belum Bayar</span>
                        <span class="font-bold text-danger-600">{{ $iuranBulanIni['total_belum'] }} Anggota</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                        <div class="bg-danger-600 h-2.5 rounded-full"
                            :style="{
                                width: ({{ $iuranBulanIni['total_belum'] }} / {{ $totalAnggota ?: 1 }}) * 100 +
                                    '%'
                            }">
                        </div>
                    </div>
                </div>
                <button @click="showIuranDetail({{ date('Y') }}, {{ date('n') }})"
                    class="btn-primary w-full mt-4">Lihat Detail</button>
            </div>
        </div>

        {{-- 4. Tracking Iuran Tahunan --}}
        <div class="card-modern mb-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Tracking Iuran 4 Tahun Terakhir</h3>
                <p class="text-gray-600 text-sm">Riwayat pembayaran iuran per bulan</p>
            </div>
            @if (!empty($dataIuran))
                <div class="space-y-6">
                    @foreach ($dataIuran as $tahun => $dataTahun)
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Tahun {{ $tahun }}</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach ($dataTahun as $bulan => $dataBulan)
                                    <div class="bg-gray-50 rounded-lg p-3 hover:shadow-md transition-shadow cursor-pointer"
                                        @click="showIuranDetail({{ $tahun }}, {{ $bulan }})">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="font-medium text-gray-800">
                                                {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
                                            </h5>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full {{ $dataBulan['total_sudah'] == $totalAnggota ? 'bg-success-100 text-success-800' : 'bg-warning-100 text-warning-800' }}">{{ $dataBulan['total_sudah'] }}/{{ $totalAnggota }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-primary-600 h-2 rounded-full"
                                                style="width: {{ $totalAnggota > 0 ? ($dataBulan['total_sudah'] / $totalAnggota) * 100 : 0 }}%">
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">{{ $dataBulan['total_belum'] }} belum
                                            bayar</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 5. Performa Anggota & Rincian Jenis Kas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Kolom Performa Anggota --}}
            <div class="card-modern">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Performa Anggota (12 Bulan Terakhir)</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach ($performaUser as $performa)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <p class="font-medium text-sm">{{ $performa['user']->name }}</p>
                                <p class="text-sm font-bold">{{ round($performa['persentase_tepat_waktu']) }}%</p>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-primary-500 to-accent-500 h-2 rounded-full"
                                    style="width: {{ $performa['persentase_tepat_waktu'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Tepat Waktu: {{ $performa['tepat_waktu'] }}x</span>
                                <span>Terlambat: {{ $performa['terlambat'] }}x</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Kolom Rincian Jenis Kas --}}
            <div class="card-modern">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Rincian per Jenis Kas</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-success-600 mb-2">Pemasukan</h4>
                        <ul class="space-y-2 text-sm">
                            @foreach ($pemasukankuanganPerJenis as $jenis)
                                @if ($jenis['total'] > 0)
                                    <li class="flex justify-between">
                                        <span>{{ $jenis['nama'] }}</span>
                                        <span class="font-medium">Rp
                                            {{ number_format($jenis['total'], 0, ',', '.') }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-danger-600 mb-2">Pengeluaran</h4>
                        <ul class="space-y-2 text-sm">
                            @foreach ($pengeluarankuanganPerJenis as $jenis)
                                @if ($jenis['total'] > 0)
                                    <li class="flex justify-between">
                                        <span>{{ $jenis['keterangan'] }}</span>
                                        <span class="font-medium">Rp
                                            {{ number_format($jenis['total'], 0, ',', '.') }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Detail Iuran --}}
        <template x-if="modal.open">
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click="modal.open = false">
                <div class="bg-white rounded-lg p-6 w-full  shadow-xl" @click.stop>
                    <h3 class="text-xl font-semibold mb-4" x-text="`Detail Iuran ${modal.data.nama_bulan}`"></h3>
                    <div x-show="modal.loading" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-primary-500"></i>
                    </div>
                    <div x-show="!modal.loading"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[60vh] overflow-y-auto">
                        {{-- Kolom Sudah Bayar --}}
                        <div>
                            <h4 class="font-bold text-success-600 mb-2">Sudah Bayar (<span
                                    x-text="modal.data.total_sudah"></span>)</h4>
                            <ul class="space-y-2">
                                <template x-for="item in modal.data.sudah_bayar" :key="item.id">
                                    <li class="text-sm p-2 rounded-md"
                                        :class="item.tepat_waktu ? 'bg-success-50' : 'bg-yellow-50'">
                                        <p class="font-medium" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500" x-text="`Bayar: ${item.tanggal_bayar}`"></p>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        {{-- Kolom Belum Bayar --}}
                        <div>
                            <h4 class="font-bold text-danger-600 mb-2">Belum Bayar (<span
                                    x-text="modal.data.total_belum"></span>)</h4>
                            <ul class="space-y-2">
                                <template x-for="item in modal.data.belum_bayar" :key="item.id">
                                    <li class="text-sm p-2 bg-gray-50 rounded-md flex justify-between items-center">
                                        <div>
                                            <p class="font-medium" x-text="item.name"></p>
                                            <p class="text-xs text-gray-500" x-text="item.email"></p>
                                        </div>
                                        <button type="button" class="btn-primary btn-xs"
                                            @click="sendReminderEmail(item.id)" :disabled="sendingEmail == item.id"
                                            x-text="sendingEmail == item.id ? 'Mengirim...' : 'Kirim Email'"></button>
                                    </li>
                                </template>
                            </ul>
                            <div x-show="emailMessage" class="mt-4"
                                :class="{ 'text-success-500': emailSuccess, 'text-danger-500': !emailSuccess }"
                                x-text="emailMessage"></div>
                        </div>
                    </div>
                    <button @click="modal.open = false" class="mt-6 w-full btn-secondary">Tutup</button>
                </div>
            </div>
        </template>

    </div>

    {{-- Script Alpine.js & Chart.js --}}
    <script>
        function dashboard() {
            return {
                tahunDipilih: {{ $tahunDipilih }},
                tahunTersedia: [],
                chart: null,
                expenseChart: null,
                doughnutChart: null,
                pieChart: null,
                modal: {
                    open: false,
                    loading: false,
                    data: {}
                },
                sendingEmail: null,
                emailMessage: '',
                emailSuccess: false,

                init() {
                    this.fetchTahunTersedia();
                    this.renderFinancialChart(
                        @json($bulanLabels),
                        @json($chartDataPemasukan),
                        @json($chartCashflow)
                    );
                    this.renderExpenseChart(
                        @json($bulanLabels),
                        @json($chartDataPengeluaran)
                    );
                    this.renderDoughnutChart(
                        @json($pemasukankuanganPerJenis->pluck('nama')->toArray()),
                        @json($pemasukankuanganPerJenis->pluck('total')->toArray())
                    );
                    this.renderPieChart(
                        @json($pengeluarankuanganPerJenis->pluck('keterangan')->toArray()),
                        @json($pengeluarankuanganPerJenis->pluck('total')->toArray())
                    );
                },

                fetchTahunTersedia() {
                    fetch('{{ route('dashboard.tahun-tersedia') }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) this.tahunTersedia = data.data;
                        });
                },

                fetchChartData() {
                    fetch(`{{ route('dashboard.chart-data') }}?tahun=${this.tahunDipilih}`)
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                this.renderFinancialChart(result.data.bulan_labels, result.data.chart_pemasukan,
                                    result.data.chart_cashflow);
                                this.renderExpenseChart(result.data.bulan_labels, result.data.chart_pengeluaran);
                            }
                        });
                },

                renderFinancialChart(labels, pemasukan, cashflow) {
                    try {
                        const ctx = document.getElementById('financialChart').getContext('2d');
                        if (this.chart) {
                            this.chart.destroy();
                        }
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Pemasukan',
                                    data: pemasukan,
                                    backgroundColor: 'rgba(5, 150, 105, 0.7)', // Hijau
                                    yAxisID: 'y',
                                }, {
                                    label: 'Cashflow',
                                    data: cashflow,
                                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                                    yAxisID: 'y',
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Gagal merender chart pemasukan:', e);
                    }
                },

                renderExpenseChart(labels, pengeluaran) {
                    try {
                        const ctx = document.getElementById('expenseChart').getContext('2d');
                        if (this.expenseChart) {
                            this.expenseChart.destroy();
                        }
                        this.expenseChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Pengeluaran',
                                    data: pengeluaran,
                                    borderColor: 'rgba(220, 38, 38, 1)', // Merah
                                    backgroundColor: 'rgba(220, 38, 38, 0.7)', // Merah
                                    fill: true,
                                    yAxisID: 'y',
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Gagal merender chart pengeluaran:', e);
                    }
                },

                renderDoughnutChart(labels, data) {
                    try {
                        const ctx = document.getElementById('doughnutChart').getContext('2d');
                        if (this.doughnutChart) {
                            this.doughnutChart.destroy();
                        }
                        this.doughnutChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: [
                                        'rgba(5, 150, 105, 0.8)', // Hijau
                                        'rgba(54, 162, 235, 0.8)',
                                        'rgba(255, 206, 86, 0.8)',
                                        'rgba(75, 192, 192, 0.8)',
                                        'rgba(153, 102, 255, 0.8)',
                                        'rgba(255, 159, 64, 0.8)',
                                        // Tambahkan warna lain jika perlu
                                    ],
                                    borderColor: [
                                        'rgba(5, 150, 105, 1)', // Hijau
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(153, 102, 255, 1)',
                                        'rgba(255, 159, 64, 1)',
                                        // Tambahkan warna lain jika perlu
                                    ],
                                    borderWidth: 1,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    },
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Gagal merender chart doughnut:', e);
                    }
                },

                renderPieChart(labels, data) {
                    try {
                        const ctx = document.getElementById('pieChart').getContext('2d');
                        if (this.pieChart) {
                            this.pieChart.destroy();
                        }
                        this.pieChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: [
                                        'rgba(220, 38, 38, 0.8)', // Merah
                                        'rgba(54, 162, 235, 0.8)',
                                        'rgba(255, 206, 86, 0.8)',
                                        'rgba(75, 192, 192, 0.8)',
                                        'rgba(153, 102, 255, 0.8)',
                                        'rgba(255, 159, 64, 0.8)',
                                        // Tambahkan warna lain jika perlu
                                    ],
                                    borderColor: [
                                        'rgba(220, 38, 38, 1)', // Merah
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(153, 102, 255, 1)',
                                        'rgba(255, 159, 64, 1)',
                                        // Tambahkan warna lain jika perlu
                                    ],
                                    borderWidth: 1,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    },
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Gagal merender chart pie:', e);
                    }
                },

                sendReminderEmail(userId) {
                    if (this.sendingEmail === null) {
                        this.sendingEmail = userId;
                        this.emailMessage = '';
                        this.emailSuccess = false;
                        fetch('{{ route('dashboard.send-reminder-email') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    user_id: userId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.emailMessage = data.message;
                                this.emailSuccess = data.success;
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.emailMessage = 'Terjadi kesalahan saat mengirim email.';
                                this.emailSuccess = false;
                            })
                            .finally(() => {
                                this.sendingEmail = null;
                                setTimeout(() => {
                                    this.emailMessage = '';
                                }, 3000);
                            });
                    }
                },
                showIuranDetail(tahun, bulan) {
                    this.modal.open = true;
                    this.modal.loading = true;
                    fetch(`{{ route('dashboard.belum-bayar-detail') }}?tahun=${tahun}&bulan=${bulan}`)
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                this.modal.data = result.data;
                            }
                        })
                        .finally(() => this.modal.loading = false);
                }
            }
        }
    </script>
</x-layout>
