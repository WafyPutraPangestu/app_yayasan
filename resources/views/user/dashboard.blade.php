<x-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-black mb-8">Dashboard Keuangan Masjid</h1>

        <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-primary p-6 mb-8">
            <h2 class="text-xl font-semibold text-secondary-800 dark:text-secondary-100 mb-4">Pilih Tahun</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($years as $year)
                    <a href="?year={{ $year }}"
                        class="px-4 py-2 rounded-full {{ $year == $currentYear ? 'bg-primary-600 text-white' : 'bg-secondary-100 dark:bg-secondary-700 text-secondary-800 dark:text-secondary-200' }} hover:bg-primary-500 hover:text-white transition">
                        {{ $year }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-primary text-black rounded-lg shadow-secondary p-6 hover-lift">
                <h3 class="text-lg font-medium mb-2">Total Pemasukan</h3>
                <p class="text-3xl font-bold">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
                <p class="text-sm opacity-90 mt-1">Tahun {{ $currentYear }}</p>
            </div>

            <div class="bg-gradient-accent text-black rounded-lg shadow-secondary p-6 hover-lift">
                <h3 class="text-lg font-medium mb-2">Jenis Kas</h3>
                <p class="text-3xl font-bold">{{ $jenisKasData->count() }}</p>
                <p class="text-sm opacity-90 mt-1">Kategori Pemasukan</p>
            </div>
            {{-- @dd(number_format(end($monthlyTotals), 0, ',', '.')) --}}
            <div class="bg-gradient-secondary text-black rounded-lg shadow-secondary p-6 hover-lift">
                <h3 class="text-lg font-medium mb-2">Bulan Ini</h3>
                <p class="text-3xl font-bold">Rp {{ number_format($currentMonthTotal, 0, ',', '.') }}</p>
                <p class="text-sm opacity-90 mt-1">{{ date('F Y') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-primary p-6 mb-8">
            <h2 class="text-xl font-semibold text-secondary-800 dark:text-secondary-100 mb-4">Pemasukan Bulanan Tahun
                {{ $currentYear }}</h2>
            <div class="h-80">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-primary p-6">
            <h2 class="text-xl font-semibold text-secondary-800 dark:text-secondary-100 mb-4">Pemasukan per Jenis Kas
                Tahun {{ $currentYear }}</h2>
            <div class="h-80">
                <canvas id="jenisKasChart"></canvas>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug data yang dikirim ke view
            console.log('Monthly Labels:', @json($monthlyLabels));
            console.log('Monthly Totals:', @json($monthlyTotals));
            console.log('Jenis Kas Data:', @json($jenisKasData));

            // Monthly Chart
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthlyLabels),
                    datasets: [{
                        label: 'Pemasukan per Bulan (Rp)',
                        data: @json($monthlyTotals),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Jenis Kas Chart
            const jenisKasCtx = document.getElementById('jenisKasChart').getContext('2d');
            const jenisKasChart = new Chart(jenisKasCtx, {
                type: 'pie',
                data: {
                    labels: @json($jenisKasData->pluck('nama')),
                    datasets: [{
                        data: @json($jenisKasData->pluck('total')),
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(220, 38, 38, 0.7)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Rp ' + context.raw.toLocaleString(
                                        'id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

</x-layout>
