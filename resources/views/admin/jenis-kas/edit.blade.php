<x-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Edit Jenis Kas</h1>

        <div class="bg-white p-6 rounded-lg shadow-md  mx-auto" x-data="jenisKasForm()">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('jenis-kas.update', $jenisKa->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="kode_jenis_kas" class="block text-gray-700 text-sm font-bold mb-2">Kode Jenis Kas:</label>
                    <input type="text" name="kode_jenis_kas" id="kode_jenis_kas"
                        value="{{ old('kode_jenis_kas', $jenisKa->kode_jenis_kas) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        maxlength="20" required>
                </div>

                <div>
                    <label for="nama_jenis_kas" class="block text-gray-700 text-sm font-bold mb-2">Nama Jenis
                        Kas:</label>
                    <input type="text" name="nama_jenis_kas" id="nama_jenis_kas"
                        value="{{ old('nama_jenis_kas', $jenisKa->nama_jenis_kas) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>

                <div>
                    <label for="default_tipe" class="block text-gray-700 text-sm font-bold mb-2">Sifat Dasar
                        Kas:</label>
                    <select name="default_tipe" id="default_tipe"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                        <option value="pemasukan" @if (old('default_tipe', $jenisKa->default_tipe) == 'pemasukan') selected @endif>Pemasukan</option>
                        <option value="pengeluaran" @if (old('default_tipe', $jenisKa->default_tipe) == 'pengeluaran') selected @endif>Pengeluaran
                        </option>
                    </select>
                </div>

                <div>
                    <label for="tipe_iuran" class="block text-gray-700 text-sm font-bold mb-2">Tipe Iuran:</label>
                    <select name="tipe_iuran" id="tipe_iuran" x-model="tipeIuran"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                        <option value="sukarela" @if (old('tipe_iuran', $jenisKa->tipe_iuran) == 'sukarela') selected @endif>Sukarela</option>
                        <option value="wajib" @if (old('tipe_iuran', $jenisKa->tipe_iuran) == 'wajib') selected @endif>Wajib</option>
                    </select>
                </div>

                <div x-show="tipeIuran === 'wajib'" x-transition class="space-y-4">
                    <div>
                        <label for="nominal_wajib" class="block text-gray-700 text-sm font-bold mb-2">Nominal Wajib per
                            Periode (Rp):</label>
                        <input type="number" name="nominal_wajib" id="nominal_wajib"
                            value="{{ old('nominal_wajib', $jenisKa->nominal_wajib) }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            min="0">
                    </div>
                    {{-- Input Target Lunas Ditambahkan --}}
                    <div>
                        <label for="target_lunas" class="block text-gray-700 text-sm font-bold mb-2">Target Lunas Penuh
                            (Rp):</label>
                        <input type="number" name="target_lunas" id="target_lunas"
                            value="{{ old('target_lunas', $jenisKa->target_lunas) }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            min="0">
                        <p class="text-xs text-gray-500 mt-1">Opsional. Isi jika ada target pelunasan total (misal:
                            untuk 1 tahun).</p>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select name="status" id="status"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                        <option value="aktif" @if (old('status', $jenisKa->status) == 'aktif') selected @endif>Aktif</option>
                        <option value="nonaktif" @if (old('status', $jenisKa->status) == 'nonaktif') selected @endif>Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4">
                    <a href="{{ route('jenis-kas.index') }}"
                        class="text-gray-600 hover:text-gray-800 font-bold">Batal</a>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function jenisKasForm() {
            return {
                tipeIuran: '{{ old('tipe_iuran', $jenisKa->tipe_iuran) }}'
            }
        }
    </script>
</x-layout>
