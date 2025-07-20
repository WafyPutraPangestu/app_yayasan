<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-6">
            <a href="{{ route('jenis-kas.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold">Detail Jenis Kas</h1>
        </div>


        <div class="bg-white p-6 rounded-lg shadow-md  mx-auto">
            <div class="space-y-4">
                {{-- Nama Jenis Kas --}}
                <div class="flex border-b pb-4">
                    <div class="w-1/3 text-sm font-bold text-gray-600">Nama Jenis</div>
                    <div class="w-2/3 text-gray-800 font-semibold">{{ $jenisKa->nama_jenis_kas }}</div>
                </div>

                {{-- Sifat Dasar --}}
                <div class="flex border-b pb-4">
                    <div class="w-1/3 text-sm font-bold text-gray-600">Sifat Dasar</div>
                    <div class="w-2/3 text-gray-800">{{ ucfirst($jenisKa->default_tipe) }}</div>
                </div>

                {{-- Tipe Iuran --}}
                <div class="flex border-b pb-4">
                    <div class="w-1/3 text-sm font-bold text-gray-600">Tipe Iuran</div>
                    <div class="w-2/3 text-gray-800">{{ ucfirst($jenisKa->tipe_iuran) }}</div>
                </div>

                {{-- Nominal Wajib & Target Lunas (jika ada) --}}
                @if ($jenisKa->tipe_iuran == 'wajib')
                    <div class="flex border-b pb-4">
                        <div class="w-1/3 text-sm font-bold text-gray-600">Nominal per Periode</div>
                        <div class="w-2/3 text-gray-800">
                            {{ $jenisKa->nominal_wajib ? 'Rp ' . number_format($jenisKa->nominal_wajib, 0, ',', '.') : '-' }}
                        </div>
                    </div>
                    <div class="flex border-b pb-4">
                        <div class="w-1/3 text-sm font-bold text-gray-600">Target Lunas Penuh</div>
                        <div class="w-2/3 text-gray-800">
                            {{ $jenisKa->target_lunas ? 'Rp ' . number_format($jenisKa->target_lunas, 0, ',', '.') : '-' }}
                        </div>
                    </div>
                @endif

                {{-- Status --}}
                <div class="flex border-b pb-4">
                    <div class="w-1/3 text-sm font-bold text-gray-600">Status</div>
                    <div class="w-2/3">
                        <span
                            class="relative inline-block px-3 py-1 font-semibold {{ $jenisKa->status == 'aktif' ? 'text-green-900' : 'text-gray-700' }} leading-tight">
                            <span aria-hidden
                                class="absolute inset-0 {{ $jenisKa->status == 'aktif' ? 'bg-green-200' : 'bg-gray-200' }} opacity-50 rounded-full"></span>
                            <span class="relative">{{ ucfirst($jenisKa->status) }}</span>
                        </span>
                    </div>
                </div>

                {{-- Timestamps --}}
                <div class="flex border-b pb-4">
                    <div class="w-1/3 text-sm font-bold text-gray-600">Dibuat Pada</div>
                    <div class="w-2/3 text-gray-600 text-sm">{{ $jenisKa->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="flex">
                    <div class="w-1/3 text-sm font-bold text-gray-600">Diperbarui Pada</div>
                    <div class="w-2/3 text-gray-600 text-sm">{{ $jenisKa->updated_at->format('d M Y, H:i') }}</div>
                </div>

            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-4 pt-8">
                <a href="{{ route('jenis-kas.index') }}" class="text-gray-600 hover:text-gray-800 font-bold">
                    Kembali
                </a>
                <a href="{{ route('jenis-kas.edit', $jenisKa->id) }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Edit
                </a>
            </div>
        </div>
    </div>
</x-layout>
