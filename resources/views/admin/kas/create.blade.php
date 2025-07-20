<x-layout>
    {{-- 
        Script ini adalah otak dari form dinamis di bawah.
        - 'allJenisKas' diambil dari controller, berisi semua data jenis kas.
        - 'tipe': Mengontrol apakah form ini untuk 'pemasukan' atau 'pengeluaran'.
        - 'selectedJenisKasId': Menyimpan ID jenis kas yang dipilih.
        - 'filteredJenisKas': Menyaring 'allJenisKas' secara dinamis berdasarkan 'tipe'.
        - 'selectedJenisKas': Objek data lengkap dari jenis kas yang dipilih.
        - 'metodePembayaran': Mengontrol pilihan 'bulanan' atau 'lunas' untuk iuran wajib.
        - 'updateJumlah': SEKARANG MENGISI OTOMATIS JUMLAH, TAPI TIDAK MENGUNCI INPUTNYA.
        - 'rawAmount' & 'formattedAmount': Mengelola format Rupiah untuk input jumlah.
    --}}
    <script>
        function cashForm() {
            // Mengambil data jenis kas yang dikirim dari controller
            const allJenisKas = @json($jenisKas);

            return {
                tipe: '{{ old('tipe', 'pemasukan') }}',
                selectedJenisKasId: '{{ old('jenis_kas_id') }}',
                metodePembayaran: '{{ old('metode_pembayaran', 'bulanan') }}',
                rawAmount: '{{ old('jumlah', 0) }}',
                formattedAmount: '{{ old('jumlah') ? number_format(old('jumlah'), 0, ',', '.') : '' }}',

                // Menyaring jenis kas yang akan ditampilkan di dropdown
                get filteredJenisKas() {
                    const tipeFilter = this.tipe === 'pemasukan' ? 'pemasukan' : 'pengeluaran';
                    return allJenisKas.filter(jk => jk.default_tipe === tipeFilter);
                },

                // Mendapatkan data lengkap dari jenis kas yang dipilih
                get selectedJenisKas() {
                    if (!this.selectedJenisKasId) return null;
                    return allJenisKas.find(jk => jk.id == this.selectedJenisKasId);
                },

                // Mengupdate jumlah saat jenis kas atau metode pembayaran berubah
                updateJumlah() {
                    if (this.tipe === 'pemasukan' && this.selectedJenisKas?.tipe_iuran === 'wajib') {
                        let total = 0;
                        if (this.metodePembayaran === 'lunas') {
                            total = this.selectedJenisKas.target_lunas || (this.selectedJenisKas.nominal_wajib * 12);
                        } else {
                            total = this.selectedJenisKas.nominal_wajib;
                        }
                        this.rawAmount = total;
                        this.formattedAmount = total.toLocaleString('id-ID');
                    }
                },

                // Mengelola format input Rupiah
                formatAmount() {
                    let value = this.formattedAmount.replace(/[^,\d]/g, '').toString();
                    let split = value.split(',');
                    let sisa = split[0].length % 3;
                    let rupiah = split[0].substr(0, sisa);
                    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                    if (ribuan) {
                        let separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                    this.formattedAmount = rupiah;
                    this.rawAmount = parseInt(value.replace(/\./g, ''));
                },

                // Fungsi untuk mereset pilihan saat tipe transaksi (pemasukan/pengeluaran) diubah
                resetOnTipeChange() {
                    this.selectedJenisKasId = '';
                    this.rawAmount = 0;
                    this.formattedAmount = '';
                }
            };
        }

        // Komponen custom select untuk pencarian anggota dari kode asli Anda
        function customSelect(config) {
            return {
                open: false,
                search: '',
                options: config.options || [],
                selectedOption: null,
                isLoading: false,
                placeholder: config.placeholder || 'Select an option',
                init() {
                    /* ... */ },
                filteredOptions() {
                    if (!this.search) return this.options;
                    return this.options.filter(option => option.text.toLowerCase().includes(this.search.toLowerCase()));
                },
                isSelected(option) {
                    return this.selectedOption && this.selectedOption.id === option.id;
                },
                async updateOptions() {
                    if (typeof config.fetchOptions === 'function') {
                        this.isLoading = true;
                        try {
                            this.options = await config.fetchOptions(this.search);
                        } finally {
                            this.isLoading = false;
                        }
                    }
                },
                selectOption(option) {
                    this.selectedOption = option;
                    this.open = false;
                    this.search = '';
                }
            };
        }
    </script>

    <div class="container mx-auto px-4 py-8" x-data="cashForm()">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} kesalahan:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center mb-8">
            <a href="{{ route('kas.index') }}"
                class="text-primary-600 hover:text-primary-800 mr-4 transition-colors"><svg
                    xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg></a>
            <h1 class="text-3xl font-bold text-secondary-900">Input Kas Baru</h1>
        </div>

        <div class="mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-8">
                <form action="{{ route('kas.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Tipe Transaksi</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" @click="tipe = 'pemasukan'; resetOnTipeChange()"
                                :class="{ 'bg-primary-500 text-white': tipe === 'pemasukan', 'bg-secondary-100 text-secondary-700': tipe !== 'pemasukan' }"
                                class="py-3 px-4 rounded-lg font-medium transition-all flex items-center justify-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>Pemasukan</button>
                            <button type="button" @click="tipe = 'pengeluaran'; resetOnTipeChange()"
                                :class="{ 'bg-danger-500 text-white': tipe === 'pengeluaran', 'bg-secondary-100 text-secondary-700': tipe !== 'pengeluaran' }"
                                class="py-3 px-4 rounded-lg font-medium transition-all flex items-center justify-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4" />
                                </svg>Pengeluaran</button>
                        </div>
                        <input type="hidden" name="tipe" x-model="tipe">
                    </div>

                    <div x-show="tipe === 'pemasukan'" x-transition>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Anggota</label>
                        <div x-data="customSelect({ placeholder: 'Cari anggota...', async fetchOptions(search) { if (!search) return []; const response = await fetch('{{ route('users.search') }}?q=' + encodeURIComponent(search)); return await response.json(); } })" class="relative">
                            <div @click="open = !open" class="cursor-pointer">
                                <div
                                    class="flex items-center justify-between bg-white border border-secondary-300 rounded-lg px-4 py-3">
                                    <span x-text="selectedOption ? selectedOption.text : placeholder"
                                        :class="{ 'text-secondary-400': !selectedOption }"></span><svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-secondary-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg></div>
                            </div>
                            <div x-show="open" @click.outside="open = false"
                                class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-lg py-1 border border-secondary-200 max-h-60 overflow-auto">
                                <div class="px-3 py-2 border-b border-secondary-100 sticky top-0 bg-white"><input
                                        x-model="search" @input.debounce="updateOptions()" type="text"
                                        class="w-full px-3 py-2 border border-secondary-200 rounded-md text-sm"
                                        placeholder="Cari..."></div>
                                <template x-for="(option, index) in filteredOptions" :key="index">
                                    <div @click="selectOption(option)"
                                        class="px-4 py-2 hover:bg-primary-50 cursor-pointer flex items-center"
                                        :class="{ 'bg-primary-100': isSelected(option) }"><span x-text="option.text"
                                            class="truncate"></span><svg x-show="isSelected(option)"
                                            xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-auto text-primary-500"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg></div>
                                </template>
                                <div x-show="isLoading" class="px-4 py-2 text-center text-secondary-500 text-sm">
                                    Memuat...</div>
                                <div x-show="!isLoading && filteredOptions.length === 0"
                                    class="px-4 py-2 text-center text-secondary-500 text-sm">Tidak ditemukan</div>
                            </div>
                            {{-- FIX: Mengganti x-model dengan :value --}}
                            <input type="hidden" name="user_id" :value="selectedOption?.id">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Jenis Kas</label>
                        <select name="jenis_kas_id" x-model="selectedJenisKasId" @change="updateJumlah()"
                            class="block w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="" disabled>-- Pilih Jenis Kas --</option>
                            <template x-for="jk in filteredJenisKas" :key="jk.id">
                                <option :value="jk.id" x-text="jk.nama_jenis_kas"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="tipe === 'pemasukan' && selectedJenisKas?.tipe_iuran === 'wajib'" x-transition
                        class="p-4 border rounded-lg bg-secondary-50 space-y-4">
                        <label class="block text-sm font-medium text-secondary-700">Detail Iuran Wajib</label>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Metode Pembayaran</label>
                            <div class="flex gap-4">
                                <label class="flex items-center"><input type="radio" name="metode_pembayaran"
                                        value="bulanan" x-model="metodePembayaran" @change="updateJumlah()"
                                        class="mr-2"> Bulanan</label>
                                <label class="flex items-center"><input type="radio" name="metode_pembayaran"
                                        value="lunas" x-model="metodePembayaran" @change="updateJumlah()"
                                        class="mr-2"> Bayar Lunas</label>
                            </div>
                        </div>
                        <div x-show="metodePembayaran === 'bulanan'" x-transition>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Bulan Iuran</label>
                            <select name="bulan_iuran"
                                class="block w-full px-4 py-3 border border-secondary-300 rounded-lg">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}"
                                        @if (old('bulan_iuran', date('n')) == $m) selected @endif>
                                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Tahun Iuran</label>
                            <input type="number" name="tahun_iuran" value="{{ old('tahun_iuran', date('Y')) }}"
                                class="block w-full px-4 py-3 border border-secondary-300 rounded-lg"
                                placeholder="Contoh: 2025">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Jumlah (Rp)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span
                                    class="text-secondary-500 font-medium">Rp</span></div>
                            <input type="hidden" name="jumlah" :value="rawAmount">
                            <input type="text" x-model="formattedAmount" @input="formatAmount()"
                                class="block w-full pl-12 pr-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="0" required>
                        </div>
                        <p x-show="tipe === 'pemasukan' && selectedJenisKas?.tipe_iuran === 'wajib'"
                            class="mt-2 text-xs text-secondary-500">
                            Jumlah terisi otomatis. Anda bisa mengubahnya jika anggota membayar sebagian (mencicil).
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Tanggal Transaksi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg
                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-secondary-400"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg></div><input type="date" name="tanggal"
                                value="{{ old('tanggal', date('Y-m-d')) }}"
                                class="block w-full pl-10 pr-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3"
                            class="block w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Contoh: Iuran bulan Juli / Beli ATK">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="flex justify-end space-x-4 pt-6">
                        <a href="{{ route('kas.index') }}"
                            class="inline-flex items-center px-6 py-2 border border-secondary-300 rounded-lg text-secondary-700 bg-white hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">Batal</a>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"><svg
                                xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
