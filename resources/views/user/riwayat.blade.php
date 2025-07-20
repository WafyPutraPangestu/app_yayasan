<x-layout>
    <div class="space-y-6" x-data="riwayatData()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-primary p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Riwayat Pembayaran</h1>
                    <p class="text-gray-600 mt-1">Riwayat pembayaran iuran rutin selama 4 tahun terakhir</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-12 h-12 gradient-accent rounded-lg flex items-center justify-center">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Stats Cards -->
            <div class="card-modern hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Lunas</p>
                        <p class="text-2xl font-bold text-green-600" x-text="stats.lunas"></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="card-modern hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Belum Bayar</p>
                        <p class="text-2xl font-bold text-red-600" x-text="stats.belumBayar"></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="card-modern hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Persentase</p>
                        <p class="text-2xl font-bold text-blue-600" x-text="stats.percentage + '%'"></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="card-modern hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Bulan</p>
                        <p class="text-2xl font-bold text-purple-600" x-text="stats.totalBulan"></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card-modern">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Filter Tahun:</label>
                        <select x-model="selectedYear" @change="filterByYear()"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Tahun</option>
                            <template x-for="year in availableYears" :key="year">
                                <option :value="year" x-text="year"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="showAll = !showAll" class="btn-outline text-sm px-3 py-1">
                        <i :class="showAll ? 'fas fa-eye-slash' : 'fas fa-eye'" class="mr-1"></i>
                        <span x-text="showAll ? 'Sembunyikan' : 'Tampilkan Semua'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Riwayat per Tahun -->
        <div class="space-y-6">
            @foreach ($riwayatPerTahun as $tahun => $bulanData)
                <div class="card-modern" x-show="shouldShowYear('{{ $tahun }}')">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Tahun {{ $tahun }}</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">
                                {{ count(array_filter($bulanData, fn($status) => $status === 'Lunas')) }}/{{ count($bulanData) }}
                                Lunas
                            </span>
                            <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full"
                                    style="width: {{ (count(array_filter($bulanData, fn($status) => $status === 'Lunas')) / count($bulanData)) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach ($bulanData as $bulan => $status)
                            <div
                                class="p-4 rounded-lg border-2 transition-all duration-200 hover:shadow-md {{ $status === 'Lunas' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                <div class="text-center">
                                    <div
                                        class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center {{ $status === 'Lunas' ? 'bg-green-500' : 'bg-red-500' }}">
                                        <i
                                            class="fas {{ $status === 'Lunas' ? 'fa-check' : 'fa-times' }} text-white text-xs"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ $bulan }}</p>
                                    <p
                                        class="text-xs {{ $status === 'Lunas' ? 'text-green-600' : 'text-red-600' }} mt-1">
                                        {{ $status }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Progress Bar untuk tahun ini -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                            <span>Progress Pembayaran</span>
                            <span>{{ count(array_filter($bulanData, fn($status) => $status === 'Lunas')) }}/{{ count($bulanData) }}
                                bulan</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-500"
                                style="width: {{ (count(array_filter($bulanData, fn($status) => $status === 'Lunas')) / count($bulanData)) * 100 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary Chart -->
        <div class="card-modern">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Grafik Pembayaran per Tahun</h2>
                <button @click="refreshChart()" class="btn-outline text-sm px-3 py-1">
                    <i class="fas fa-refresh mr-1"></i>
                    Refresh
                </button>
            </div>
            <div class="relative h-80">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        function riwayatData() {
            return {
                selectedYear: '',
                showAll: true,
                availableYears: [
                    @foreach ($riwayatPerTahun as $tahun => $bulanData)
                        '{{ $tahun }}',
                    @endforeach
                ],
                riwayatData: @json($riwayatPerTahun),
                stats: {
                    lunas: 0,
                    belumBayar: 0,
                    percentage: 0,
                    totalBulan: 0
                },
                chart: null,

                init() {
                    this.calculateStats();
                    this.initChart();
                },

                calculateStats() {
                    let totalLunas = 0;
                    let totalBelumBayar = 0;
                    let totalBulan = 0;

                    Object.values(this.riwayatData).forEach(bulanData => {
                        Object.values(bulanData).forEach(status => {
                            totalBulan++;
                            if (status === 'Lunas') {
                                totalLunas++;
                            } else {
                                totalBelumBayar++;
                            }
                        });
                    });

                    this.stats = {
                        lunas: totalLunas,
                        belumBayar: totalBelumBayar,
                        percentage: totalBulan > 0 ? Math.round((totalLunas / totalBulan) * 100) : 0,
                        totalBulan: totalBulan
                    };
                },

                shouldShowYear(year) {
                    if (!this.showAll && this.selectedYear && this.selectedYear !== year) {
                        return false;
                    }
                    return true;
                },

                filterByYear() {
                    // Filter akan ditangani oleh shouldShowYear
                },

                initChart() {
                    const ctx = document.getElementById('paymentChart').getContext('2d');

                    const years = Object.keys(this.riwayatData);
                    const lunasData = [];
                    const belumBayarData = [];

                    years.forEach(year => {
                        const bulanData = this.riwayatData[year];
                        const lunas = Object.values(bulanData).filter(status => status === 'Lunas').length;
                        const belumBayar = Object.values(bulanData).filter(status => status === 'Belum Bayar')
                            .length;

                        lunasData.push(lunas);
                        belumBayarData.push(belumBayar);
                    });

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: years,
                            datasets: [{
                                    label: 'Lunas',
                                    data: lunasData,
                                    backgroundColor: 'rgba(5, 150, 105, 0.8)',
                                    borderColor: 'rgba(5, 150, 105, 1)',
                                    borderWidth: 2,
                                    borderRadius: 8,
                                    borderSkipped: false,
                                },
                                {
                                    label: 'Belum Bayar',
                                    data: belumBayarData,
                                    backgroundColor: 'rgba(220, 38, 38, 0.8)',
                                    borderColor: 'rgba(220, 38, 38, 1)',
                                    borderWidth: 2,
                                    borderRadius: 8,
                                    borderSkipped: false,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Statistik Pembayaran per Tahun'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 12,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                },

                refreshChart() {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    this.initChart();
                    this.calculateStats();
                }
            }
        }
    </script>
</x-layout>
