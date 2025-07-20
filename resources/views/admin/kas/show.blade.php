<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-6">
            <a href="{{ route('kas.index') }}" class="text-primary-600 hover:text-primary-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-secondary-900">Detail Transaksi Kas</h1>
        </div>

        <div class="card-modern  mx-auto">
            <div class="space-y-6">
                <div class="flex items-start">
                    <div class="w-1/3 text-sm font-medium text-secondary-500">Tanggal</div>
                    <div class="w-2/3 text-secondary-900">{{ $ka->tanggal->format('d F Y') }}</div>
                </div>

                <div class="flex items-start">
                    <div class="w-1/3 text-sm font-medium text-secondary-500">Anggota</div>
                    <div class="w-2/3 text-secondary-900">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 gradient-primary rounded-full flex items-center justify-center text-white">
                                {{ substr($ka->user->name ?? '?', 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <div class="font-medium">{{ $ka->user->name ?? 'N/A' }}</div>
                                <div class="text-sm text-secondary-500">{{ $ka->user->id_anggota ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-1/3 text-sm font-medium text-secondary-500">Jenis Kas</div>
                    <div class="w-2/3 text-secondary-900">
                        <div class="font-medium">{{ $ka->jenisKas->nama_jenis_kas ?? 'N/A' }}</div>
                        <div class="text-sm text-secondary-500">Kode: {{ $ka->jenisKas->id_jenis_kas ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-1/3 text-sm font-medium text-secondary-500">Jumlah</div>
                    <div class="w-2/3 text-primary-700 font-semibold">
                        Rp {{ number_format($ka->jumlah, 0, ',', '.') }}
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-1/3 text-sm font-medium text-secondary-500">Keterangan</div>
                    <div class="w-2/3 text-secondary-900">
                        {{ $ka->keterangan ?? '-' }}
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <div class="flex space-x-3">
                        <a href="{{ route('kas.edit', $ka->id) }}"
                            class="btn-outline px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>
                        <a href="{{ route('kas.index') }}"
                            class="btn-secondary px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('detailKas', () => ({
                // Jika diperlukan untuk interaksi tambahan
            }));
        });
    </script>
</x-layout>
