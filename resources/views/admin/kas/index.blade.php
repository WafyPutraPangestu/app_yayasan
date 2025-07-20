<x-layout>
    {{-- Menggunakan Alpine.js untuk manajemen state frontend --}}
    <div class="container mx-auto px-4 py-8" x-data="kasApp()">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
            <div class="flex-1">
                <h1
                    class="text-3xl font-bold text-gray-900 dark:text-white bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">
                    Manajemen Kas
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Kelola pemasukan dan pengeluaran kas organisasi</p>
            </div>

            <a href="{{ route('kas.create') }}"
                class="btn-primary hover-lift hover:shadow-lg flex items-center gap-2 px-6 py-3 w-full md:w-auto justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Tambah Transaksi
            </a>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Saldo -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Saldo</p>
                <p class="text-2xl font-bold"
                    :class="stats.saldo >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                    x-text="formatRupiah(stats.saldo)">
                </p>
                <div class="h-1 mt-2 rounded-full"
                    :class="stats.saldo >= 0 ? 'bg-gradient-to-r from-green-100 to-green-500' :
                        'bg-gradient-to-r from-red-100 to-red-500'">
                </div>
            </div>

            <!-- Pemasukan -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pemasukan</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400"
                    x-text="formatRupiah(stats.total_pemasukan)">
                </p>
                <div class="h-1 mt-2 bg-gradient-to-r from-green-100 to-green-500 rounded-full"></div>
            </div>

            <!-- Pengeluaran -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pengeluaran</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400"
                    x-text="formatRupiah(stats.total_pengeluaran)">
                </p>
                <div class="h-1 mt-2 bg-gradient-to-r from-red-100 to-red-500 rounded-full"></div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card-modern hover:shadow-lg transition-all duration-300 mb-6 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Filter Tipe --}}
                <select x-model="tipe" @change="loadKas()" class="input-modern">
                    <option value="">Semua Tipe</option>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
                {{-- Filter Bulan --}}
                <select x-model="bulan" @change="loadKas()" class="input-modern">
                    <option value="">Semua Bulan</option>
                    <template x-for="m in 12" :key="m">
                        <option :value="m"
                            x-text="new Date(2000, m-1).toLocaleDateString('id-ID', {month: 'long'})"></option>
                    </template>
                </select>
                {{-- Filter Tahun --}}
                <select x-model="tahun" @change="loadKas()" class="input-modern"
                    :disabled="availableYears.length === 0">
                    <template x-if="availableYears.length === 0">
                        <option>Memuat...</option>
                    </template>
                    <template x-for="year in availableYears" :key="year">
                        <option :value="year" x-text="year"></option>
                    </template>
                </select>
            </div>
        </div>

        <!-- Tabel Transaksi -->
        <div class="card-modern overflow-hidden hover:shadow-lg transition-all duration-300">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Keterangan</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Jumlah</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-if="loading">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center py-12">
                                        <div
                                            class="animate-spin rounded-full h-10 w-10 border-4 border-primary-500 border-t-transparent">
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400">Memuat data...</span>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loading && kas.length === 0">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center py-12 space-y-4">
                                        <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300">Tidak ada data
                                            transaksi</p>
                                        <p class="text-sm text-center">Tambahkan transaksi baru atau coba filter yang
                                            berbeda</p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-for="item in kas" :key="item.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white"
                                        x-text="new Date(item.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-medium">
                                            <span x-text="item.user?.name?.charAt(0) || '-'"></span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"
                                                x-text="item.jenis_kas?.nama_jenis_kas || item.keterangan || 'Tidak ada keterangan'">
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400"
                                                x-text="item.user?.name || 'Sistem'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold"
                                        :class="item.tipe === 'pemasukan' ? 'text-green-600 dark:text-green-400' :
                                            'text-red-600 dark:text-red-400'"
                                        x-text="formatRupiah(item.jumlah, item.tipe)"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-1">
                                        <a :href="`{{ url('kas') }}/${item.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 p-2 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <form :action="`{{ url('kas') }}/${item.id}`" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan <span class="font-medium" x-text="kas.length"></span> dari <span class="font-medium"
                        x-text="totalItems"></span> transaksi
                </div>
                <div class="flex space-x-2">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm font-medium transition-colors"
                        :class="currentPage === 1 ? 'cursor-not-allowed opacity-50 text-gray-400' :
                            'hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'">
                        Sebelumnya
                    </button>
                    <button @click="nextPage" :disabled="currentPage * perPage >= totalItems"
                        class="px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm font-medium transition-colors"
                        :class="currentPage * perPage >= totalItems ? 'cursor-not-allowed opacity-50 text-gray-400' :
                            'hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function kasApp() {
            return {
                tipe: '',
                bulan: '',
                tahun: '',
                availableYears: [],
                kas: [],
                stats: {
                    total_pemasukan: 0,
                    total_pengeluaran: 0,
                    saldo: 0
                },
                loading: true,
                currentPage: 1,
                perPage: 10,
                totalItems: 0,

                init() {
                    this.loadAvailableYears();
                },

                loadAvailableYears() {
                    fetch('{{ route('kas.available-years') }}')
                        .then(res => res.json())
                        .then(years => {
                            this.availableYears = years;
                            if (years.length > 0 && !this.tahun) {
                                this.tahun = years[0];
                            }
                            this.loadKas();
                        })
                        .catch(err => console.error("Gagal memuat tahun:", err));
                },

                loadKas() {
                    this.loading = true;
                    let params = new URLSearchParams({
                        page: this.currentPage
                    });
                    if (this.tipe) params.append('tipe', this.tipe);
                    if (this.bulan) params.append('bulan', this.bulan);
                    if (this.tahun) params.append('tahun', this.tahun);

                    fetch(`{{ route('kas.data') }}?${params.toString()}`)
                        .then(res => res.json())
                        .then(data => {
                            this.kas = data.data || [];
                            this.totalItems = data.totalItems || 0;
                            this.perPage = data.perPage || 10;
                            this.currentPage = data.currentPage || 1;
                            if (data.stats) {
                                this.stats = {
                                    total_pemasukan: parseFloat(data.stats.total_pemasukan) || 0,
                                    total_pengeluaran: parseFloat(data.stats.total_pengeluaran) || 0,
                                    saldo: parseFloat(data.stats.saldo) || 0
                                };
                            }
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error("Gagal memuat data transaksi:", err);
                            this.loading = false;
                        });
                },

                nextPage() {
                    if (this.currentPage * this.perPage < this.totalItems) {
                        this.currentPage++;
                        this.loadKas();
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.loadKas();
                    }
                },

                // FIX: Menghapus sintaks parameter default (type = null)
                formatRupiah(number, type) {
                    let prefix = '';
                    if (type === 'pemasukan') prefix = '+ ';
                    if (type === 'pengeluaran') prefix = '- ';

                    // Menambahkan pengecekan untuk memastikan 'number' tidak null atau undefined
                    const num = number || 0;

                    return prefix + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(num));
                }
            }
        }
    </script>
</x-layout>
