<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold gradient-primary bg-clip-text text-gray-100 px-4 py-2 mb-4">
                Edit User: {{ $user->name }}
            </h1>
            <p class="text-gray-600 text-lg">
                Ubah detail akun user ini
            </p>
        </div>

        <div class=" mx-auto">
            <div class="card-modern">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.
                    </div>
                @endif

                <form action="{{ route('manajemen-admin.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama
                                Lengkap:</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                required>
                        </div>

                        <div>
                            <label for="bin_binti"
                                class="block text-sm font-semibold text-gray-700 mb-2">Bin/Binti:</label>
                            <input type="text" name="bin_binti" id="bin_binti"
                                value="{{ old('bin_binti', $user->bin_binti) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Contoh: bin/binti [Nama Orang Tua]">
                        </div>

                        <div>
                            <label for="jenis_kelamin" class="block text-sm font-semibold text-gray-700 mb-2">Jenis
                                Kelamin:</label>
                            <select name="jenis_kelamin" id="jenis_kelamin"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="laki-laki"
                                    {{ old('jenis_kelamin', $user->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>
                                    Laki-laki</option>
                                <option value="perempuan"
                                    {{ old('jenis_kelamin', $user->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>
                                    Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email:</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                required>
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Role:</label>
                            <select name="role" id="role"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User
                                </option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="tempat_lahir" class="block text-sm font-semibold text-gray-700 mb-2">Tempat
                                Lahir:</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir"
                                value="{{ old('tempat_lahir', $user->tempat_lahir) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Kota tempat lahir">
                        </div>

                        <div>
                            <label for="tanggal_lahir" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                Lahir:</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                value="{{ old('tanggal_lahir', $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <div class="md:col-span-2">
                            <label for="alamat" class="block text-sm font-semibold text-gray-700 mb-2">Alamat:</label>
                            <textarea name="alamat" id="alamat" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Alamat lengkap saat ini">{{ old('alamat', $user->alamat) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="no_hp" class="block text-sm font-semibold text-gray-700 mb-2">No. HP:</label>
                            <input type="text" name="no_hp" id="no_hp"
                                value="{{ old('no_hp', $user->no_hp) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="08xx-xxxx-xxxx">
                        </div>

                        <div class="md:col-span-2">
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status:</label>
                            <select name="status" id="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="Pending"
                                    {{ old('status', $user->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Aktif" {{ old('status', $user->status) == 'Aktif' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="Nonaktif"
                                    {{ old('status', $user->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif
                                </option>
                                <option value="Wafat" {{ old('status', $user->status) == 'Wafat' ? 'selected' : '' }}>
                                    Wafat</option>
                                <option value="Mengundurkan diri"
                                    {{ old('status', $user->status) == 'Mengundurkan diri' ? 'selected' : '' }}>
                                    Mengundurkan diri</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-8">
                        <a href="{{ route('manajemen-admin.index') }}" class="btn-outline">Batal</a>
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
