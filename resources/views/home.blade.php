<x-layout>
    <!-- Hero Section with Grid -->
    <section class="gradient-primary rounded-t-lg text-white py-20 relative overflow-hidden">
        <!-- Background Animation -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-overlay animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-accent-300 rounded-full mix-blend-overlay animate-pulse"
                style="animation-delay: 1s"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Chart Area -->
                <div class="order-2 lg:order-1">
                    <div class="card-glass p-8 hover-lift">
                        <h3 class="text-xl font-semibold mb-6 text-center text-white">Data Keanggotaan Yayasan</h3>
                        <!-- Chart Container -->
                        <div
                            class="bg-white/10 backdrop-blur-sm rounded-lg p-6 min-h-[300px] flex items-center justify-center border border-white/20">
                            <canvas id="membershipChart" width="400" height="200"></canvas>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="text-2xl font-bold text-accent-200">{{ $stats['tahun_berdiri'] }}</div>
                                <div class="text-sm text-gray-200">Tahun Berdiri</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="text-2xl font-bold text-success-300">{{ $stats['total_users'] }}+</div>
                                <div class="text-sm text-gray-200">Total Anggota</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="text-2xl font-bold text-warning-300">{{ $stats['active_users'] }}+</div>
                                <div class="text-sm text-gray-200">Anggota Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Hero Content -->
                <div class="order-1 lg:order-2 text-center lg:text-left">
                    <div class="animate-slide-up">
                        <span
                            class="inline-block bg-accent-500/20 text-accent-100 px-4 py-2 rounded-full text-sm font-medium mb-4">
                            🕌 Sejak 1977
                        </span>
                        <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
                            <span class="block">Yayasan</span>
                            <span
                                class="block gradient-text bg-gradient-to-r from-accent-200 to-warning-300 bg-clip-text text-transparent">As-Salam</span>
                            <span class="block text-3xl lg:text-4xl">Joglo</span>
                        </h1>
                        <p class="text-xl lg:text-2xl mb-8 text-gray-100  mx-auto lg:mx-0">
                            Memperkuat pranata sosial-keagamaan dan meningkatkan mutu pendidikan Islam untuk generasi
                            masa depan
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="#sejarah" class="btn-primary hover-lift bg-white text-primary-800 shadow-large">
                                📖 Sejarah Kami
                            </a>
                            <a href="#program"
                                class="btn-outline text-white border-white hover:bg-white hover:text-primary-800">
                                🎯 Program Kami
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 gradient-soft">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 gradient-primary rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">🕌</span>
                    </div>
                    <h3 class="text-3xl font-bold text-primary-800 mb-2">{{ $stats['luas_masjid'] }}</h3>
                    <p class="text-gray-600">Luas Masjid</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 gradient-accent rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👥</span>
                    </div>
                    <h3 class="text-3xl font-bold text-accent-600 mb-2">{{ $stats['jamaah_aktif'] }}</h3>
                    <p class="text-gray-600">Anggota Aktif</p>
                </div>
                {{-- <div class="text-center">
                    {{-- <div
                        class="w-20 h-20 bg-gradient-to-br from-success-400 to-success-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">📚</span>
                    </div> --}}

                {{-- </div>
            <div class="text-center"> --}}
                {{-- <div
                        class="w-20 h-20 bg-gradient-to-br from-warning-400 to-warning-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">🎓</span>
                    </div> --}}

                {{-- </div>  --}}
            </div>
        </div>
    </section>

    <!-- Data Analytics Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-primary-100 text-primary-600 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    📊 Data Keanggotaan
                </span>
                <h2 class="text-4xl font-bold text-secondary-900 mb-4">Statistik Anggota</h2>
                <div class="w-32 h-1 gradient-primary mx-auto mb-6"></div>
                <p class="text-xl text-gray-600  mx-auto">
                    Analisis data keanggotaan berdasarkan usia dan status
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16">
                <!-- Age Distribution Chart -->
                <div class="card-modern hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-semibold text-primary-800">Distribusi Usia Anggota</h3>
                        <span class="text-sm text-gray-500">Total: {{ $ageData['total'] }} anggota</span>
                    </div>

                    <div class="relative h-80 mb-6">
                        <canvas id="ageChart"></canvas>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($ageData['labels'] as $index => $label)
                            <div class="text-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="text-2xl font-bold text-primary-600 mb-1">{{ $ageData['data'][$index] }}
                                </div>
                                <div class="text-sm font-medium text-gray-700">{{ $label }} Tahun</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $ageData['total'] > 0 ? round(($ageData['data'][$index] / $ageData['total']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Status Distribution Chart -->
                <div class="card-modern hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-semibold text-primary-800">Status Keanggotaan</h3>
                        <span class="text-sm text-gray-500">Total: {{ $statusData['total'] }} anggota</span>
                    </div>

                    <div class="relative h-80 mb-6">
                        <canvas id="statusChart"></canvas>
                    </div>

                    <div class="space-y-3">
                        @php
                            $statusColors = ['#8B5CF6', '#10B981', '#F59E0B', '#EF4444', '#6B7280'];
                            $statusIcons = ['⏳', '✅', '❌', '🕊️', '📤'];
                        @endphp
                        @foreach ($statusData['labels'] as $index => $label)
                            <div
                                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3"
                                        style="background-color: {{ $statusColors[$index] ?? '#6B7280' }}"></div>
                                    <span class="font-medium">{{ $statusIcons[$index] ?? '•' }}
                                        {{ $label }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-lg">{{ $statusData['data'][$index] }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $statusData['total'] > 0 ? round(($statusData['data'][$index] / $statusData['total']) * 100, 1) : 0 }}%
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sejarah Section (tetap sama) -->
    <section id="sejarah" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-primary-100 text-primary-600 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    📜 Perjalanan Kami
                </span>
                <h2 class="text-4xl font-bold text-secondary-900 mb-4">Sejarah Singkat Yayasan</h2>
                <div class="w-32 h-1 gradient-primary mx-auto mb-6"></div>
                <p class="text-xl text-gray-600  mx-auto">
                    Perjalanan panjang membangun komunitas yang kuat dan pendidikan yang berkualitas
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Timeline Content -->
                <div class="space-y-8">
                    <div class="card-modern hover-lift border-l-4 border-primary-500">
                        <div class="flex items-start">
                            <div class="bg-primary-100 p-3 rounded-full mr-6">
                                <span class="text-2xl">🕌</span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-semibold text-primary-800 mb-3">1977 - Awal Berdiri</h3>
                                <p class="text-gray-700 mb-4">
                                    Pembangunan Masjid As-Salam Joglo dimulai pada Agustus 1977 di atas lahan seluas ±
                                    320 m²
                                    di Komplek DKI RT 002/RW 004 Joglo, Kembangan, Jakarta Barat.
                                </p>
                                <div class="bg-primary-50 p-4 rounded-lg">
                                    <p class="text-primary-700 font-medium">
                                        🎯 Tujuan: Memperkuat pranata sosial-keagamaan dan mempererat silaturahmi warga
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-modern hover-lift border-l-4 border-accent-500">
                        <div class="flex items-start">
                            <div class="bg-accent-100 p-3 rounded-full mr-6">
                                <span class="text-2xl">📋</span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-semibold text-accent-700 mb-3">1978 - Pendirian Resmi</h3>
                                <p class="text-gray-700 mb-4">
                                    Pada 10 Januari 1978 warga mendirikan Yayasan Masjid dan Perguruan As-Salam,
                                    disahkan melalui Akta Notaris Daeng Lalo SH No. 103 pada 20 Maret 1978.
                                </p>
                                <div class="flex items-center gap-2 text-accent-600">
                                    <span class="text-sm">✅</span>
                                    <span class="font-medium">Status Hukum Resmi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-modern hover-lift border-l-4 border-success-500">
                        <div class="flex items-start">
                            <div class="bg-success-100 p-3 rounded-full mr-6">
                                <span class="text-2xl">🎉</span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-semibold text-success-700 mb-3">2018 - Peresmian Perluasan
                                </h3>
                                <p class="text-gray-700 mb-4">
                                    Masjid diperluas menjadi sekitar 1.400 m² dan diresmikan oleh Gubernur DKI Jakarta
                                    Anies Baswedan pada 25 Mei 2018 sebagai pusat kegiatan sosial, pendidikan, dan
                                    budaya.
                                </p>
                                <div class="bg-success-50 p-4 rounded-lg">
                                    <p class="text-success-700 font-medium">
                                        🏛️ Kapasitas meningkat 4x lipat dari sebelumnya
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Timeline -->
                <div class="relative">
                    <div class="card-glass p-8 text-center">
                        <h3 class="text-2xl font-bold text-white mb-8">Timeline Perkembangan</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between bg-white/10 p-4 rounded-lg">
                                <span class="text-white font-bold">1977</span>
                                <div class="flex-1 mx-4 h-2 gradient-primary rounded-full"></div>
                                <span class="text-white text-sm">Pembangunan Masjid</span>
                            </div>
                            <div class="flex items-center justify-between bg-white/10 p-4 rounded-lg">
                                <span class="text-white font-bold">1978</span>
                                <div class="flex-1 mx-4 h-2 gradient-accent rounded-full"></div>
                                <span class="text-white text-sm">Pendirian Yayasan</span>
                            </div>
                            <div class="flex items-center justify-between bg-white/10 p-4 rounded-lg">
                                <span class="text-white font-bold">2018</span>
                                <div
                                    class="flex-1 mx-4 h-2 bg-gradient-to-r from-success-400 to-success-600 rounded-full">
                                </div>
                                <span class="text-white text-sm">Peresmian Perluasan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Section (tetap sama) -->
    <section id="program" class="py-20 gradient-soft">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-primary-100 text-primary-600 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    🎯 Program Kami
                </span>
                <h2 class="text-4xl font-bold text-secondary-900 mb-4">Program Unggulan</h2>
                <div class="w-32 h-1 gradient-primary mx-auto mb-6"></div>
                <p class="text-xl text-gray-600  mx-auto">
                    Berbagai program yang dirancang untuk mengembangkan potensi dan membangun karakter generasi muda
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Program 1 -->
                <div class="card-modern hover-lift group overflow-hidden">
                    <div
                        class="h-56 gradient-primary rounded-t-lg mb-6 flex items-center justify-center relative overflow-hidden">
                        <span
                            class="text-6xl group-hover:scale-110 transition-transform duration-300">👨‍👩‍👧‍👦</span>
                        <div
                            class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors duration-300">
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-3 h-3 bg-primary-500 rounded-full"></span>
                            <span class="text-sm text-primary-600 font-medium">Program Sosial</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-primary-800">Program Anak Asuh</h3>
                        <p class="text-gray-700 mb-4">
                            Bermula dengan 15 anak yatim/dhuafa dan kini membina {{ $stats['active_users'] }}+ peserta
                            dari tingkat SD hingga
                            perguruan tinggi.
                        </p>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>👥 {{ $stats['active_users'] }}+ Peserta</span>
                            <span>🎓 SD - Perguruan Tinggi</span>
                        </div>
                    </div>
                </div>

                <!-- Program 2 -->
                <div class="card-modern hover-lift group overflow-hidden">
                    <div
                        class="h-56 gradient-accent rounded-t-lg mb-6 flex items-center justify-center relative overflow-hidden">
                        <span class="text-6xl group-hover:scale-110 transition-transform duration-300">📖</span>
                        <div
                            class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors duration-300">
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-3 h-3 bg-accent-500 rounded-full"></span>
                            <span class="text-sm text-accent-600 font-medium">Program Pendidikan</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-accent-700">Pelatihan Pengajar Al-Qur'an</h3>
                        <p class="text-gray-700 mb-4">
                            Program yang berjalan sejak Desember 2014 untuk meningkatkan kualitas pengajaran Al-Qur'an
                            di lingkungan masyarakat.
                        </p>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>📅 Sejak 2014</span>
                            <span>👨‍🏫 50+ Pengajar</span>
                        </div>
                    </div>
                </div>

                <!-- Program 3 -->
                <div class="card-modern hover-lift group overflow-hidden">
                    <div
                        class="h-56 bg-gradient-to-br from-success-400 to-success-600 rounded-t-lg mb-6 flex items-center justify-center relative overflow-hidden">
                        <span class="text-6xl group-hover:scale-110 transition-transform duration-300">🤝</span>
                        <div
                            class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors duration-300">
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-3 h-3 bg-success-500 rounded-full"></span>
                            <span class="text-sm text-success-600 font-medium">Program Komunitas</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-success-700">Kegiatan Sosial</h3>
                        <p class="text-gray-700 mb-4">
                            Berbagai program sosial untuk memberdayakan masyarakat sekitar dan memperkuat silaturahmi
                            antar warga.
                        </p>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>🌍 Komunitas Luas</span>
                            <span>💫 Program Berkelanjutan</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="#" class="btn-primary hover-lift shadow-large">
                    Lihat Semua Program →
                </a>
            </div>
        </div>
    </section>

    <!-- Gallery Section (tetap sama) -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-primary-100 text-primary-600 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    📸 Dokumentasi
                </span>
                <h2 class="text-4xl font-bold text-secondary-900 mb-4">Galeri Kegiatan</h2>
                <div class="w-32 h-1 gradient-primary mx-auto mb-6"></div>
                <p class="text-xl text-gray-600  mx-auto">
                    Dokumentasi berbagai kegiatan dan momen berharga di Yayasan As-Salam Joglo
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div class="aspect-square gradient-primary rounded-xl overflow-hidden hover-lift group cursor-pointer">
                    <div
                        class="w-full h-full flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                        <div class="text-center">
                            <span class="text-4xl mb-2 block">🕌</span>
                            <span class="text-sm">Kegiatan Masjid</span>
                        </div>
                    </div>
                </div>

                <div class="aspect-square gradient-accent rounded-xl overflow-hidden hover-lift group cursor-pointer">
                    <div
                        class="w-full h-full flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                        <div class="text-center">
                            <span class="text-4xl mb-2 block">📚</span>
                            <span class="text-sm">Pembelajaran</span>
                        </div>
                    </div>
                </div>

                <div
                    class="aspect-square bg-gradient-to-br from-success-400 to-success-600 rounded-xl overflow-hidden hover-lift group cursor-pointer">
                    <div
                        class="w-full h-full flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                        <div class="text-center">
                            <span class="text-4xl mb-2 block">🎉</span>
                            <span class="text-sm">Acara Besar</span>
                        </div>
                    </div>
                </div>

                <div
                    class="aspect-square bg-gradient-to-br from-warning-400 to-warning-600 rounded-xl overflow-hidden hover-lift group cursor-pointer">
                    <div
                        class="w-full h-full flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                        <div class="text-center">
                            <span class="text-4xl mb-2 block">🤝</span>
                            <span class="text-sm">Kegiatan Sosial</span>
                        </div>
                    </div>
                </div>

                <div
                    class="aspect-square bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl overflow-hidden hover-lift group cursor-pointer">
                    <div
                        class="w-full h-full flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                    </div>

                </div>

            </div>

            <div class="text-center mt-12">
                <button class="btn-outline hover-glow">Lihat Semua Foto →</button>
            </div>

        </div>

    </section>

    <section class="py-20 gradient-accent text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-overlay"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-white rounded-full mix-blend-overlay"></div>
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-white rounded-full mix-blend-overlay">
            </div>
        </div>

        <div class="container mx-auto px-6 text-center relative z-10">
            <span class="inline-block bg-white/20 text-white px-4 py-2 rounded-full text-sm font-medium mb-6">
                🤝 Bergabung Bersama Kami
            </span>
            <h2 class="text-4xl font-bold mb-6">Mari Berkontribusi untuk Masa Depan yang Lebih Baik</h2>
            <p class="text-xl  mx-auto mb-12 text-gray-100">
                Dukung program-program Yayasan As-Salam Joglo untuk kemajuan pendidikan dan sosial
                masyarakat.
                Bersama kita wujudkan generasi yang berakhlak mulia dan berprestasi.
            </p>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <div class="card-glass p-6 hover-lift">
                    <span class="text-4xl mb-4 block">💰</span>
                    <h3 class="text-xl font-bold mb-2">Donasi</h3>
                    <p class="text-gray-200 text-sm">Bantu program pendidikan dan sosial</p>
                </div>
                <div class="card-glass p-6 hover-lift">
                    <span class="text-4xl mb-4 block">🙋‍♂️</span>
                    <h3 class="text-xl font-bold mb-2">Volunteer</h3>
                    <p class="text-gray-200 text-sm">Bergabung sebagai relawan aktif</p>
                </div>
                <div class="card-glass p-6 hover-lift">
                    <span class="text-4xl mb-4 block">🤝</span>
                    <h3 class="text-xl font-bold mb-2">Partnership</h3>
                    <p class="text-gray-200 text-sm">Kemitraan strategis jangka panjang</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-6">
                <button class="btn-primary hover-lift bg-white text-primary-800 shadow-large px-8 py-4 text-lg">
                    💝 Donasi Sekarang
                </button>
                <button
                    class="btn-outline text-white border-white hover:bg-white hover:text-primary-800 px-8 py-4 text-lg">
                    🙋‍♂️ Menjadi Relawan
                </button>
            </div>
        </div>
    </section>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Membership Chart
            // Membership Doughnut Chart
            const memberCtx = document.getElementById('membershipChart');
            if (memberCtx) {
                new Chart(memberCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Total Anggota', 'Anggota Aktif', 'Anggota Pending', 'Anggota Nonaktif',
                            'Anggota Wafat', 'Mengundurkan Diri'
                        ],
                        datasets: [{
                            data: [
                                {{ $stats['total_users'] }},
                                {{ $stats['active_users'] }},
                                {{ $stats['pending_users'] }},
                                {{ $stats['nonaktif_users'] }},
                                {{ $stats['wafat_users'] }},
                                {{ $stats['mengundurkan_diri_users'] }}
                            ],
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(107, 114, 128, 0.8)',
                                'rgba(139, 92, 246, 0.8)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '65%',
                        radius: '90%'
                    }
                });
            }

            // Age Distribution Chart
            const ageCtx = document.getElementById('ageChart');
            if (ageCtx) {
                new Chart(ageCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($ageData['labels']),
                        datasets: [{
                            label: 'Distribusi Usia',
                            data: @json($ageData['data']),
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($statusData['labels']),
                        datasets: [{
                            data: @json($statusData['data']),
                            backgroundColor: [
                                '#8B5CF6', // Pending
                                '#10B981', // Aktif
                                '#F59E0B', // Nonaktif
                                '#EF4444', // Wafat
                                '#6B7280' // Mengundurkan diri
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    </script>
</x-layout>
