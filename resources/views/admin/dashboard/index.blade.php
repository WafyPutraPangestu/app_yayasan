<x-layout>
    <div class="dashboard-admin" x-data="dashboard()">
        <div class="mb-8 flex justify-between">
            {{-- Header --}}
            <div class="">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Keuangan</h1>
                <p class="text-gray-600">Ringkasan, analisis, dan performa keuangan yayasan.</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('dashboard.export.all') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Export Semua Data Kas
                </a>
            </div>
        </div>
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

        {{-- 3. Iuran Sukarela Bulan Ini --}}
        <div class="mb-8 card-modern">
            <h2 class="text-lg font-semibold mb-4">Iuran Sukarela Bulan Ini</h2>
            <p class="text-sm text-gray-500 mb-2">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm">
                        <span>Total Pemasukan</span>
                        <span class="font-bold text-success-600">Rp
                            {{ number_format($iuranSukarelaBulanIni['total_pemasukan'], 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                        <div class="bg-success-600 h-2.5 rounded-full"
                            style="width: {{ $iuranSukarelaBulanIni['total_pemasukan'] > 0 ? 100 : 0 }}%">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm">
                        <span>Jumlah Transaksi</span>
                        <span class="font-bold text-primary-600">{{ $iuranSukarelaBulanIni['jumlah_transaksi'] }}
                            Transaksi</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                        <div class="bg-primary-600 h-2.5 rounded-full"
                            style="width: {{ $iuranSukarelaBulanIni['jumlah_transaksi'] > 0 ? 100 : 0 }}%">
                        </div>
                    </div>
                </div>

                <button @click="showIuranSukarelaModal()" class="btn-primary w-full mt-4">Lihat Detail</button>
            </div>
        </div>

        {{-- Modal Khusus Iuran Sukarela --}}
        <div x-show="sukarelaModal.open"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="sukarelaModal.open = false">
            <div class="bg-white rounded-lg p-6 w-full max-w-4xl shadow-xl" @click.stop>
                <h3 class="text-xl font-semibold mb-4" x-text="`Detail Iuran Sukarela ${sukarelaModal.data.bulan}`">
                </h3>

                <div x-show="sukarelaModal.loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-primary-500"></i>
                </div>

                <div x-show="!sukarelaModal.loading">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="card-modern">
                            <h4 class="font-bold text-success-600 mb-2">Total Pemasukan</h4>
                            <p class="text-2xl font-bold"
                                x-text="'Rp ' + sukarelaModal.data.total_pemasukan.toLocaleString('id-ID')"></p>
                        </div>

                        <div class="card-modern">
                            <h4 class="font-bold text-primary-600 mb-2">Jumlah Transaksi</h4>
                            <p class="text-2xl font-bold" x-text="sukarelaModal.data.jumlah_transaksi"></p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-semibold mb-3">Per Jenis Kas</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <template x-for="jenis in sukarelaModal.data.per_jenis_kas" :key="jenis.nama_jenis_kas">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium" x-text="jenis.nama_jenis_kas"></h5>
                                    <p class="text-success-600 font-bold"
                                        x-text="'Rp ' + jenis.total.toLocaleString('id-ID')"></p>
                                    <p class="text-sm text-gray-500" x-text="jenis.jumlah_transaksi + ' transaksi'"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis
                                        Kas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in sukarelaModal.data.transaksi" :key="item.id">
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm" x-text="index + 1"></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium" x-text="item.user_name"></div>
                                            <div class="text-xs text-gray-500" x-text="item.user_email"></div>
                                        </td>
                                        <td class="px-4 py-3 text-sm" x-text="item.jenis_kas"></td>
                                        <td class="px-4 py-3 text-sm font-bold text-success-600"
                                            x-text="'Rp ' + item.jumlah.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                        </td>
                                        <td class="px-4 py-3 text-sm" x-text="item.tanggal"></td>
                                        <td class="px-4 py-3 text-sm" x-text="item.keterangan || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button @click="sukarelaModal.open = false" class="mt-6 w-full btn-secondary">Tutup</button>
            </div>
        </div>

        <div class="card-modern mb-8">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Progress Iuran Wajib</h3>
                <select x-model="selectedJenisKasWajib" class="form-select text-sm rounded-md border-gray-300">
                    <option value="">Semua Jenis Kas</option>
                    @foreach ($trackingWajib as $namaKas => $data)
                        <option value="{{ $namaKas }}">{{ $namaKas }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-gray-600 text-sm mb-4">Pelunasan iuran wajib per jenis kas (target per anggota)</p>

            @foreach ($trackingWajib as $namaKas => $data)
                <div class="mb-8 border-b pb-6 last:border-b-0" x-data="{ open: false }"
                    x-show="selectedJenisKasWajib === '' || selectedJenisKasWajib === '{{ $namaKas }}'">
                    {{-- Header Info --}}
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-medium text-lg text-gray-800">{{ $namaKas }}</h4>
                            <p class="text-sm text-gray-600">Target: Rp
                                {{ number_format($data['target_per_user'], 0, ',', '.') }} per anggota</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-primary-600">
                                {{ $data['total_user_lunas'] }} dari {{ $data['total_anggota'] }} anggota lunas
                            </div>
                            <div class="text-xs text-gray-500">
                                ({{ $data['persentase_user_lunas'] }}% anggota sudah lunas)
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar Anggota Lunas --}}
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">Progress Anggota Lunas</span>
                            <span class="text-sm text-gray-600">{{ $data['persentase_user_lunas'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-500 h-3 rounded-full transition-all duration-300"
                                style="width: {{ $data['persentase_user_lunas'] }}%">
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar Total Terkumpul --}}
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">Total Terkumpul Semua Anggota</span>
                            <span class="text-sm text-gray-600">
                                Rp {{ number_format($data['total_terkumpul_semua_user'], 0, ',', '.') }} /
                                Rp {{ number_format($data['target_total_semua_user'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                                style="width: {{ $data['progress_keseluruhan'] }}%">
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ $data['progress_keseluruhan'] }}% dari target
                            keseluruhan</div>
                    </div>

                    {{-- Summary Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="bg-green-50 p-3 rounded-lg text-center">
                            <div class="text-lg font-bold text-green-600">{{ $data['total_user_lunas'] }}</div>
                            <div class="text-xs text-green-700">Anggota Lunas</div>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-lg text-center">
                            <div class="text-lg font-bold text-orange-600">{{ $data['total_user_belum_lunas'] }}</div>
                            <div class="text-xs text-orange-700">Belum Lunas</div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <div class="text-lg font-bold text-blue-600">
                                Rp {{ number_format($data['target_per_user'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-blue-700">Target/Anggota</div>
                        </div>
                        <div class="bg-purple-50 p-3 rounded-lg text-center">
                            <div class="text-lg font-bold text-purple-600">
                                {{ $data['progress_keseluruhan'] }}%
                            </div>
                            <div class="text-xs text-purple-700">Progress Total</div>
                        </div>
                    </div>

                    {{-- Button untuk lihat detail --}}
                    <div class="text-center">
                        <button @click="open = ! open"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            Lihat Progress Detail Anggota
                        </button>
                    </div>

                    {{-- Detail Progress (Dropdown) --}}
                    <div x-show="open" x-transition>
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center mb-4">
                                <div>
                                    <div class="text-lg font-bold text-primary-600">Rp
                                        {{ number_format($data['target_per_user'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-600">Target per Anggota</div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-green-600">{{ $data['total_user_lunas'] }}
                                    </div>
                                    <div class="text-xs text-gray-600">Anggota Lunas</div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-orange-600">
                                        {{ $data['total_user_belum_lunas'] }}</div>
                                    <div class="text-xs text-gray-600">Belum Lunas</div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-purple-600">
                                        {{ $data['persentase_user_lunas'] }}%</div>
                                    <div class="text-xs text-gray-600">% Anggota Lunas</div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                No</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Anggota</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Sudah Bayar</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Sisa</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Progress</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template
                                            x-for="(progress, index) in trackingWajibData['{{ $namaKas }}'].progress_detail"
                                            :key="index">
                                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm" x-text="index + 1"></td>
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-medium text-gray-900"
                                                        x-text="progress.user.name"></div>
                                                    <div class="text-xs text-gray-500"
                                                        x-text="progress.user.id_anggota || '-'"></div>
                                                </td>
                                                <td class="px-4 py-3 text-sm"
                                                    x-text="'Rp ' + progress.total_terbayar.toLocaleString('id-ID')">
                                                </td>
                                                <td class="px-4 py-3 text-sm"
                                                    x-text="'Rp ' + progress.sisa_bayar.toLocaleString('id-ID')"></td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center">
                                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                            <div class="bg-primary-600 h-2 rounded-full"
                                                                :style="'width: ' + progress.persentase + '%'"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-600"
                                                            x-text="progress.persentase + '%'"></span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3"
                                                    x-html="progress.status === 'lunas' ? '<span class=&quot;px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full&quot;>Lunas</span>' : '<span class=&quot;px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full&quot;>Belum Lunas</span>'">
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Modal untuk detail progress (optional) --}}
            <div id="progressDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0  bg-opacity-75 transition-opacity" aria-hidden="true"
                        @click="closeProgressDetail()"></div>
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Detail Progress Anggota
                                </h3>
                                <button @click="closeProgressDetail()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div id="progressDetailContent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Tracking Iuran Bulanan --}}
        <div x-data="{ open: false }">
            <div class="card-modern mb-8">
                <div class="mb-6 flex justify-between items-center cursor-pointer" @click="open = !open">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Tracking Iuran Bulanan</h3>
                    <svg x-bind:class="{ 'rotate-180': open }" class="w-6 h-6 transition-transform duration-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <select x-model="selectedJenisKasBulanan" class="form-select text-sm rounded-md border-gray-300">
                        <option value="">Semua Jenis Kas</option>
                        @foreach ($trackingBulanan as $jenisKasId => $dataPerJenis)
                            @if (!empty($dataPerJenis))
                                <option
                                    value="{{ $dataPerJenis[array_key_first($dataPerJenis)][array_key_first($dataPerJenis[array_key_first($dataPerJenis)])]['nama_kas'] }}">
                                    {{ $dataPerJenis[array_key_first($dataPerJenis)][array_key_first($dataPerJenis[array_key_first($dataPerJenis)])]['nama_kas'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <p class="text-gray-600 text-sm mb-4">Riwayat pembayaran iuran per bulan</p>

                <div class="space-y-6" x-show="open" x-transition>
                    @foreach ($trackingBulanan as $jenisKasId => $dataPerJenis)
                        @if (!empty($dataPerJenis))
                            <div class="card-modern mb-8"
                                x-show="selectedJenisKasBulanan === '' || selectedJenisKasBulanan === '{{ $dataPerJenis[array_key_first($dataPerJenis)][array_key_first($dataPerJenis[array_key_first($dataPerJenis)])]['nama_kas'] ?? '' }}'">
                                <div class="mb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                        Tracking
                                        {{ $dataPerJenis[array_key_first($dataPerJenis)][array_key_first($dataPerJenis[array_key_first($dataPerJenis)])]['nama_kas'] ?? 'Nama Kas Tidak Ditemukan' }}
                                    </h3>
                                    <p class="text-gray-600 text-sm">
                                        Target: Rp
                                        {{ number_format($dataPerJenis[array_key_first($dataPerJenis)][array_key_first($dataPerJenis[array_key_first($dataPerJenis)])]['target'] ?? 0, 0, ',', '.') }}
                                        ({{ ceil(($dataPerJenis[array_key_first($dataPerJenis)][array_key_first($dataPerJenis[array_key_first($dataPerJenis)])]['target'] ?? 0) / 10000) }}
                                        bulan)
                                    </p>
                                </div>

                                <div class="space-y-6">
                                    @foreach ($dataPerJenis as $tahun => $dataTahun)
                                        <div class="border rounded-lg p-4">
                                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Tahun
                                                {{ $tahun }}
                                            </h4>
                                            <div
                                                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                                @foreach ($dataTahun as $bulan => $dataBulan)
                                                    @php
                                                        $bulanFormatted = \Carbon\Carbon::create(
                                                            $tahun,
                                                            $bulan,
                                                            1,
                                                        )->format('F');
                                                    @endphp
                                                    <div class="bg-gray-50 rounded-lg p-3 hover:shadow-md transition-shadow cursor-pointer"
                                                        @click="showIuranDetail({{ $tahun }}, {{ $bulan }}, {{ $jenisKasId }})">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <h5 class="font-medium text-gray-800">
                                                                {{ $bulanFormatted }}
                                                            </h5>
                                                            <span
                                                                class="text-xs px-2 py-1 rounded-full {{ $dataBulan['total_sudah'] == $totalAnggota ? 'bg-success-100 text-success-800' : 'bg-warning-100 text-warning-800' }}">
                                                                {{ $dataBulan['total_sudah'] }}/{{ $totalAnggota }}
                                                            </span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                                            <div class="bg-primary-600 h-2 rounded-full"
                                                                style="width: {{ $totalAnggota > 0 ? ($dataBulan['total_sudah'] / $totalAnggota) * 100 : 0 }}%">
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-gray-600 mt-1">
                                                            Terkumpul: Rp
                                                            {{ number_format($dataBulan['total_terkumpul'], 0, ',', '.') }}
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        {{-- 6. Performa Anggota & Rincian Jenis Kas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Kolom Performa Anggota --}}
            <div class="card-modern">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Performa Anggota (Bulan Ini)</h3>
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
                <div class="bg-white rounded-lg p-6 w-full shadow-xl" @click.stop>
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
                        <div class="mt-4">
                            <button @click="sendBulkReminders()" :disabled="bulkSending" class="btn-primary w-full">
                                <span
                                    x-text="bulkSending ? 'Mengirim ke semua...' : 'Kirim ke Semua yang Belum Bayar'"></span>
                            </button>
                            <div x-show="bulkEmailMessage" class="mt-2 text-sm"
                                :class="bulkEmailMessage.includes('Berhasil') ? 'text-green-500' : 'text-red-500'"
                                x-text="bulkEmailMessage"></div>
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
                trackingWajibData: @json($trackingWajib),
                selectedJenisKasWajib: '', // State untuk filter Progress Iuran Wajib
                selectedJenisKasBulanan: '', // State untuk filter Tracking Iuran Bulanan
                sukarelaModal: {
                    open: false,
                    loading: false,
                    data: {
                        bulan: '',
                        total_pemasukan: 0,
                        jumlah_transaksi: 0,
                        transaksi: [],
                        per_jenis_kas: []
                    }
                },
                openTrackingBulanan: false,

                // Variabel baru yang ditambahkan
                activeTab: 'unpaid',
                bulkSending: false,
                bulkEmailMessage: '',


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
                showIuranSukarelaModal() {
                    this.sukarelaModal.open = true;
                    this.sukarelaModal.loading = true;

                    fetch('{{ route('dashboard.iuran-sukarela-detail') }}')
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                this.sukarelaModal.data = result.data;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.sukarelaModal.data = {
                                bulan: 'Error',
                                total_pemasukan: 0,
                                jumlah_transaksi: 0,
                                transaksi: [],
                                per_jenis_kas: []
                            };
                        })
                        .finally(() => this.sukarelaModal.loading = false);
                },
                fetchTahunTersedia() {
                    fetch('{{ route('dashboard.tahun-tersedia') }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) this.tahunTersedia = data.data;
                        });
                },
                sendBulkReminders() {
                    if (confirm('Apakah Anda yakin ingin mengirim email pengingat ke semua anggota yang belum membayar?')) {
                        this.bulkSending = true;
                        this.bulkEmailMessage = '';

                        fetch('{{ route('dashboard.send-bulk-reminders') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    tahun: this.modal.data.tahun,
                                    bulan: this.modal.data.bulan,
                                    jenis_kas_id: this.modal.data.jenis_kas_id
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.bulkEmailMessage = data.message;
                                    if (data.failed_emails && data.failed_emails.length > 0) {
                                        this.bulkEmailMessage += '\nEmail yang gagal dikirim: ' + data.failed_emails
                                            .join(', ');
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.bulkEmailMessage = 'Terjadi kesalahan saat mengirim email massal';
                            })
                            .finally(() => {
                                this.bulkSending = false;
                                setTimeout(() => {
                                    this.bulkEmailMessage = '';
                                }, 5000);
                            });
                    }
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
                showIuranDetail(tahun, bulan, jenisKasId = null) {
                    this.modal.open = true;
                    this.modal.loading = true;
                    let url = `{{ route('dashboard.belum-bayar-detail') }}?tahun=${tahun}&bulan=${bulan}`;
                    if (jenisKasId) {
                        url += `&jenis_kas_id=${jenisKasId}`;
                    }
                    fetch(url)
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                this.modal.data = result.data;
                            }
                        })
                        .finally(() => this.modal.loading = false);
                },
                showProgressDetail(namaKas) {
                    const data = this.trackingWajibData[namaKas];
                    if (!data) return;

                    document.getElementById('modalTitle').textContent = `Detail Progress - ${namaKas}`;

                    let progressRows = '';
                    data.progress_detail.forEach((progress, index) => {
                        const statusBadge = progress.status === 'lunas' ?
                            '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Lunas</span>' :
                            '<span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">Belum Lunas</span>';

                        progressRows += `
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">${index + 1}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">${progress.user.name}</div>
                            <div class="text-xs text-gray-500">${progress.user.id_anggota || '-'}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">Rp ${progress.total_terbayar.toLocaleString('id-ID')}</td>
                        <td class="px-4 py-3 text-sm">Rp ${progress.sisa_bayar.toLocaleString('id-ID')}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: ${progress.persentase}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">${progress.persentase}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">${statusBadge}</td>
                    </tr>
                `;
                    });

                    const content = `
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-lg font-bold text-primary-600">Rp ${data.target_per_user.toLocaleString('id-ID')}</div>
                            <div class="text-xs text-gray-600">Target per Anggota</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-green-600">${data.total_user_lunas}</div>
                            <div class="text-xs text-gray-600">Anggota Lunas</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-orange-600">${data.total_user_belum_lunas}</div>
                            <div class="text-xs text-gray-600">Belum Lunas</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-purple-600">${data.persentase_user_lunas}%</div>
                            <div class="text-xs text-gray-600">% Anggota Lunas</div>
                        </div>
                    </div>
                </div>
    
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sudah Bayar</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sisa</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${progressRows}
                        </tbody>
                    </table>
                </div>
            `;

                    document.getElementById('progressDetailContent').innerHTML = content;
                    document.getElementById('progressDetailModal').classList.remove('hidden');
                },
                closeProgressDetail() {
                    document.getElementById('progressDetailModal').classList.add('hidden');
                }
            }
        }
    </script>
</x-layout>
