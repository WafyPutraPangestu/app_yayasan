<x-layout>
    {{-- Script Alpine.js untuk mengelola state form --}}
    <script>
        function editKasForm() {
            const allJenisKas = @json($jenisKas);
            const kasData = @json($ka);

            return {
                tipe: kasData.tipe,
                selectedJenisKasId: '{{ old('jenis_kas_id', $ka->jenis_kas_id) }}',
                rawAmount: '{{ old('jumlah', $ka->jumlah) }}',
                formattedAmount: '{{ old('jumlah') ? number_format(old('jumlah'), 0, ',', '.') : number_format($ka->jumlah, 0, ',', '.') }}',

                get filteredJenisKas() {
                    const tipeFilter = this.tipe === 'pemasukan' ? 'pemasukan' : 'pengeluaran';
                    return allJenisKas.filter(jk => jk.default_tipe === tipeFilter);
                },

                get selectedJenisKas() {
                    if (!this.selectedJenisKasId) return null;
                    return allJenisKas.find(jk => jk.id == this.selectedJenisKasId);
                },

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
            };
        }
    </script>

    <div class="container mx-auto px-4 py-8" x-data="editKasForm()">
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
            <h1 class="text-3xl font-bold text-secondary-900">Edit Transaksi Kas</h1>
        </div>

        <div class="mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-8">
                <form action="{{ route('kas.update', $ka->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Menampilkan tipe transaksi sebagai teks, karena tidak bisa diubah --}}
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Tipe Transaksi</label>
                        <div class="w-full py-3 px-4 rounded-lg bg-secondary-100 text-secondary-800 font-medium">
                            {{ ucfirst($ka->tipe) }}
                        </div>
                    </div>

                    {{-- Menampilkan field yang relevan berdasarkan tipe transaksi --}}
                    @if ($ka->tipe == 'pemasukan')
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Anggota</label>
                            <input type="text"
                                value="{{ $ka->user->name ?? 'Tidak diketahui' }} ({{ $ka->user->id_anggota ?? '-' }})"
                                class="block w-full px-4 py-3 border border-secondary-300 rounded-lg bg-secondary-50"
                                readonly>
                            <p class="mt-2 text-xs text-secondary-500">Anggota dan Jenis Kas tidak dapat diubah untuk
                                menjaga integritas data.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Jenis Kas</label>
                            <input type="text" value="{{ $ka->jenisKas->nama_jenis_kas ?? 'Tidak diketahui' }}"
                                class="block w-full px-4 py-3 border border-secondary-300 rounded-lg bg-secondary-50"
                                readonly>
                        </div>
                    @else
                        {{-- Pengeluaran --}}
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Jenis Kas</label>
                            <input type="text" value="{{ $ka->jenisKas->nama_jenis_kas ?? 'Tidak diketahui' }}"
                                class="block w-full px-4 py-3 border border-secondary-300 rounded-lg bg-secondary-50"
                                readonly>
                        </div>
                    @endif

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
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Tanggal Transaksi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg
                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-secondary-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg></div>
                            <input type="date" name="tanggal"
                                value="{{ old('tanggal', $ka->tanggal->format('Y-m-d')) }}"
                                class="block w-full pl-10 pr-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3"
                            class="block w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Contoh: Iuran bulan Juli / Beli ATK">{{ old('keterangan', $ka->keterangan) }}</textarea>
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
                            </svg>Update Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
