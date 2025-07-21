<x-layout>
    <div class="space-y-6" x-data="riwayatData()">
        <div class="bg-white rounded-lg shadow-primary p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Riwayat Iuran Wajib</h1>
                    <p class="text-gray-600 mt-1">Riwayat pembayaran iuran wajib per bulan</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-12 h-12 gradient-accent rounded-lg flex items-center justify-center">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-primary p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Riwayat</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filter Jenis Kas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kas</label>
                    <select x-model="selectedJenisKas"
                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Jenis Kas</option>
                        @foreach ($riwayatIuranWajibPerBulan as $namaKas => $tracking)
                            <option value="{{ $namaKas }}">{{ $namaKas }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select x-model="selectedTahun"
                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tahun</option>
                        <template x-for="tahun in availableYears" :key="tahun">
                            <option :value="tahun" x-text="tahun"></option>
                        </template>
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="selectedStatus"
                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="Lunas">Lunas</option>
                        <option value="Belum Bayar">Belum Bayar</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @foreach ($riwayatIuranWajibPerBulan as $namaKas => $tracking)
                <div class="card-modern" x-show="shouldShowJenisKas('{{ $namaKas }}')">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">{{ $namaKas }}</h3>
                        <div class="flex items-center space-x-4">
                            <!-- Progress Bar -->
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                        :style="'width: ' + getPercentage('{{ $namaKas }}') + '%'"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-600"
                                    x-text="getPercentage('{{ $namaKas }}') + '%'"></span>
                            </div>
                            <!-- Detail Stats -->
                            <div class="text-right">
                                <div class="text-sm text-gray-600">
                                    <span x-text="getLunasCount('{{ $namaKas }}')"></span> /
                                    <span x-text="getTotalCount('{{ $namaKas }}')"></span> bulan
                                </div>
                                <div class="text-xs text-gray-500">
                                    (<span x-text="getBelumBayarCount('{{ $namaKas }}')"></span> belum bayar)
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!empty($tracking))
                        @foreach ($tracking as $tahun => $bulanData)
                            <div class="mb-4" x-show="shouldShowTahun('{{ $tahun }}')">
                                <h4 class="font-semibold text-gray-700 mb-2">{{ $tahun }}</h4>
                                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 mt-2">
                                    @foreach ($bulanData as $bulan => $status)
                                        <div class="p-3 rounded-md text-center transition-all duration-200 hover:scale-105"
                                            x-show="shouldShowStatus('{{ $status }}')"
                                            style="background-color: {{ $status === 'Lunas' ? '#dcfce7' : '#fef2f2' }};
                                                   border: 1px solid {{ $status === 'Lunas' ? '#86efac' : '#fca5a5' }};">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ \Carbon\Carbon::create(null, $bulan, 1)->format('M') }}
                                            </div>
                                            <div class="text-xs text-{{ $status === 'Lunas' ? 'green' : 'red' }}-600">
                                                {{ $status }}
                                            </div>
                                            @if ($status === 'Lunas')
                                                <i class="fas fa-check-circle text-green-500 text-xs mt-1"></i>
                                            @else
                                                <i class="fas fa-exclamation-circle text-red-500 text-xs mt-1"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-info-circle text-gray-400 text-4xl mb-2"></i>
                            <p class="text-gray-500">Riwayat pembayaran untuk {{ $namaKas }} belum tersedia.</p>
                        </div>
                    @endif
                </div>
            @endforeach

            @if (empty($riwayatIuranWajibPerBulan))
                <div class="card-modern text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500 text-lg">Tidak ada riwayat pembayaran iuran wajib.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function riwayatData() {
            return {
                selectedJenisKas: '',
                selectedTahun: '',
                selectedStatus: '',
                riwayatData: @json($riwayatIuranWajibPerBulan),

                get availableYears() {
                    const years = new Set();
                    Object.values(this.riwayatData).forEach(tracking => {
                        Object.keys(tracking).forEach(year => {
                            years.add(year);
                        });
                    });
                    return Array.from(years).sort((a, b) => b - a);
                },

                shouldShowJenisKas(namaKas) {
                    return this.selectedJenisKas === '' || this.selectedJenisKas === namaKas;
                },

                shouldShowTahun(tahun) {
                    return this.selectedTahun === '' || this.selectedTahun === tahun;
                },

                shouldShowStatus(status) {
                    return this.selectedStatus === '' || this.selectedStatus === status;
                },

                getPercentage(namaKas) {
                    if (!this.riwayatData[namaKas]) return 0;

                    let total = 0;
                    let lunas = 0;

                    Object.values(this.riwayatData[namaKas]).forEach(bulanData => {
                        Object.values(bulanData).forEach(status => {
                            total++;
                            if (status === 'Lunas') lunas++;
                        });
                    });

                    return total > 0 ? Math.round((lunas / total) * 100) : 0;
                },

                getLunasCount(namaKas) {
                    if (!this.riwayatData[namaKas]) return 0;

                    let lunas = 0;
                    Object.values(this.riwayatData[namaKas]).forEach(bulanData => {
                        Object.values(bulanData).forEach(status => {
                            if (status === 'Lunas') lunas++;
                        });
                    });

                    return lunas;
                },

                getTotalCount(namaKas) {
                    if (!this.riwayatData[namaKas]) return 0;

                    let total = 0;
                    Object.values(this.riwayatData[namaKas]).forEach(bulanData => {
                        total += Object.keys(bulanData).length;
                    });

                    return total;
                },

                getBelumBayarCount(namaKas) {
                    return this.getTotalCount(namaKas) - this.getLunasCount(namaKas);
                }
            }
        }
    </script>
</x-layout>
