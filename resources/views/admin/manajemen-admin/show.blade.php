<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold gradient-primary bg-clip-text text-gray-100 px-4 py-2 mb-4">
                Detail User: {{ $user->name }}
            </h1>
            <p class="text-gray-600 text-lg">
                Informasi lengkap mengenai user ini
            </p>
        </div>

        <div class="card-modern  mx-auto">
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">ID Anggota:</strong>
                <p class="text-lg text-gray-900">{{ $user->id_anggota }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Nama Lengkap:</strong>
                <p class="text-lg text-gray-900">{{ $user->name }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Bin/Binti:</strong>
                <p class="text-lg text-gray-900">{{ $user->bin_binti ?? '-' }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Jenis Kelamin:</strong>
                <p class="text-lg text-gray-900">{{ ucfirst($user->jenis_kelamin) ?? '-' }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Email:</strong>
                <p class="text-lg text-gray-900">{{ $user->email }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Role:</strong>
                <p class="text-lg text-gray-900">{{ ucfirst($user->role) }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Tempat, Tanggal Lahir:</strong>
                <p class="text-lg text-gray-900">{{ $user->tempat_lahir ?? '-' }},
                    {{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d F Y') : '-' }}</p>
            </div>
            <div class="mb-4">
                <strong class="text-sm font-semibold text-gray-700">Alamat:</strong>
                <p class="text-lg text-gray-900">{{ $user->alamat ?? '-' }}</p>
            </div>
            <div class="mb-6">
                <strong class="text-sm font-semibold text-gray-700">No. HP:</strong>
                <p class="text-lg text-gray-900">{{ $user->no_hp ?? '-' }}</p>
            </div>
            <div class="flex items-center justify-end">
                <a href="{{ route('manajemen-admin.index') }}" class="btn-secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</x-layout>
