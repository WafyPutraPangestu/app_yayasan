<x-layout>
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manajemen Jenis Kas</h1>

            <!-- Grup Tombol -->
            <div class="flex items-center gap-4">
                <a href="{{ route('jenis-kas.create') }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                    Tambah Jenis Kas
                </a>

                <!-- TOMBOL BARU UNTUK EXPORT EXCEL -->
                <a href="{{ route('jenis-kas.export.excel') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export ke Excel
                </a>
            </div>
        </div>

        <!-- Session Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Table Section -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            No
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nama Jenis
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nominal Wajib
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Target Lunas
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jenisKas as $item)
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ $loop->iteration + ($jenisKas->currentPage() - 1) * $jenisKas->perPage() }}
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold">{{ $item->nama_jenis_kas }}
                                </p>
                                <p class="text-gray-600 whitespace-no-wrap text-xs">{{ ucfirst($item->default_tipe) }} -
                                    {{ ucfirst($item->tipe_iuran) }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    @if ($item->tipe_iuran == 'wajib' && $item->nominal_wajib)
                                        Rp {{ number_format($item->nominal_wajib, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    @if ($item->tipe_iuran == 'wajib' && $item->target_lunas)
                                        Rp {{ number_format($item->target_lunas, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span
                                    class="relative inline-block px-3 py-1 font-semibold {{ $item->status == 'aktif' ? 'text-green-900' : 'text-gray-700' }} leading-tight">
                                    <span aria-hidden
                                        class="absolute inset-0 {{ $item->status == 'aktif' ? 'bg-green-200' : 'bg-gray-200' }} opacity-50 rounded-full"></span>
                                    <span class="relative">{{ ucfirst($item->status) }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                                <div class="flex justify-end gap-4">
                                    <a href="{{ route('jenis-kas.show', $item->id) }}"
                                        class="text-gray-600 hover:text-gray-900">Lihat</a>
                                    <a href="{{ route('jenis-kas.edit', $item->id) }}"
                                        class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('jenis-kas.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Anda yakin?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <p class="text-gray-500">Data Jenis Kas belum tersedia.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($jenisKas->hasPages())
                <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
                    {{ $jenisKas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
