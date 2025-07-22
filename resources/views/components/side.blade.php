<!-- Mobile Sidebar -->
<aside id="mobile-sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden">
    <div class="flex flex-col h-full">
        <div class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-green-600 to-green-700 text-white">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                    <img src="{{ asset('images/home/logo_yayasan.png') }}" alt="Logo Yayasan">
                </div>
                <div>
                    <h1 class="text-lg font-bold">Yayasan</h1>
                    <p class="text-xs opacity-90">Management System</p>
                </div>
            </div>
            <button onclick="toggleMobileSidebar()" class="p-1 rounded-md hover:bg-white/20">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 py-6 overflow-y-auto">
            @can('admin')
                <div class="mb-8">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Admin Panel
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard.index') }}"
                                class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('dashboard.index') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                                <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                Dashboard
                            </a>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-users-cog mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span class="flex-1 text-left">Manajemen Anggota</span>
                                <i :class="open ? 'transform rotate-180' : ''"
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <ul x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                                <li>
                                    <a href="{{ route('manajemen-admin.index') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('manajemen-admin.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-list mr-2"></i> Daftar Data Anggota
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('manajemen-admin.create') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('manajemen-admin.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Admin
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-tags mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span class="flex-1 text-left">Jenis Kas</span>
                                <i :class="open ? 'transform rotate-180' : ''"
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <ul x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                                <li>
                                    <a href="{{ route('jenis-kas.index') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('jenis-kas.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-list mr-2"></i> Daftar Jenis Kas
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('jenis-kas.create') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('jenis-kas.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Jenis Kas
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-wallet mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span class="flex-1 text-left">Manajemen Kas</span>
                                <i :class="open ? 'transform rotate-180' : ''"
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <ul x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                                <li>
                                    <a href="{{ route('kas.index') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('kas.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-list mr-2"></i> Daftar Data Kas
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('kas.create') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('kas.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Transaksi Kas
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            @endcan

            @can('user')
                <div class="mb-8">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        User Menu
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('user.dashboard') }}"
                                class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                                <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('user.riwayat') }}"
                                class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('user.riwayat') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                                <i class="fas fa-history mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                Riwayat Pembayaran
                            </a>
                        </li>
                    </ul>
                </div>
            @endcan

            <div class="mb-8">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Menu Umum
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('home') }}"
                            class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                            <i class="fas fa-home mr-3 text-gray-400 group-hover:text-blue-600"></i>
                            Home
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="p-4 border-t border-gray-200">
            @auth
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 gradient-primary rounded-full flex items-center justify-center">
                            <span class="text-white font-medium text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white gradient-primary rounded-lg hover:opacity-90 transition-all duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login
                </a>
            @endauth
        </div>
    </div>
</aside>

<!-- Desktop Sidebar -->
<aside x-data="{ sidebarOpen: true }"
    class="desktop-sidebar fixed inset-y-0 left-0 z-30 bg-white shadow-lg transition-all duration-300 ease-in-out"
    :class="sidebarOpen ? 'w-64' : 'w-20'">
    <div class="flex flex-col h-full">
        <div
            class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-green-600 to-green-700 text-white">
            <div class="flex items-center space-x-3" x-show="sidebarOpen">
                <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                    <img src="{{ asset('images/home/logo_yayasan.png') }}" alt="Logo Yayasan">
                </div>
                <div>
                    <h1 class="text-lg font-bold">Yayasan</h1>
                    <p class="text-xs opacity-90">Management System</p>
                </div>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="p-1 rounded-md hover:bg-blue-500 focus:outline-none">
                <i x-show="sidebarOpen" class="fas fa-chevron-left text-white"></i>
                <i x-show="!sidebarOpen" class="fas fa-chevron-right text-white"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 py-6 overflow-y-auto">
            @can('admin')
                <div class="mb-8">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3"
                        x-show="sidebarOpen">
                        Admin Panel
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard.index') }}"
                                class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('dashboard.index') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                                <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span x-show="sidebarOpen">Dashboard</span>
                            </a>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-users-cog mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span x-show="sidebarOpen" class="flex-1 text-left">Manajemen Anggota </span>
                                <i x-show="sidebarOpen" :class="open ? 'transform rotate-180' : ''"
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <ul x-show="open && sidebarOpen" x-collapse class="ml-8 mt-1 space-y-1">
                                <li>
                                    <a href="{{ route('manajemen-admin.index') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('manajemen-admin.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-list mr-2"></i> Daftar Data Anggota
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('manajemen-admin.create') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('manajemen-admin.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Akun Anggota
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-tags mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span x-show="sidebarOpen" class="flex-1 text-left">Jenis Kas</span>
                                <i x-show="sidebarOpen" :class="open ? 'transform rotate-180' : ''"
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <ul x-show="open && sidebarOpen" x-collapse class="ml-8 mt-1 space-y-1">
                                <li>
                                    <a href="{{ route('jenis-kas.index') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('jenis-kas.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-list mr-2"></i> Daftar Jenis Kas
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('jenis-kas.create') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('jenis-kas.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Jenis Kas
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-wallet mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span x-show="sidebarOpen" class="flex-1 text-left">Manajemen Kas</span>
                                <i x-show="sidebarOpen" :class="open ? 'transform rotate-180' : ''"
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <ul x-show="open && sidebarOpen" x-collapse class="ml-8 mt-1 space-y-1">
                                <li>
                                    <a href="{{ route('kas.index') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('kas.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-list mr-2"></i> Daftar Data Kas
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('kas.create') }}"
                                        class="nav-link-sub flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('kas.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Transaksi Kas
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            @endcan

            @can('user')
                <div class="mb-8">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3"
                        x-show="sidebarOpen">
                        User Menu
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('user.dashboard') }}"
                                class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                                <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span x-show="sidebarOpen">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('user.riwayat') }}"
                                class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('user.riwayat') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                                <i class="fas fa-history mr-3 text-gray-400 group-hover:text-blue-600"></i>
                                <span x-show="sidebarOpen">Riwayat Pembayaran</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endcan

            <div class="mb-8">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3"
                    x-show="sidebarOpen">
                    Menu Umum
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('home') }}"
                            class="nav-link group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : '' }}">
                            <i class="fas fa-home mr-3 text-gray-400 group-hover:text-blue-600"></i>
                            <span x-show="sidebarOpen">Home</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="p-4 border-t border-gray-200">
            @auth
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 mb-3" x-show="sidebarOpen">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 gradient-primary rounded-full flex items-center justify-center">
                            <span class="text-white font-medium text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span x-show="sidebarOpen">Logout</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white gradient-primary rounded-lg hover:opacity-90 transition-all duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    <span x-show="sidebarOpen">Login</span>
                </a>
            @endauth
        </div>
    </div>
</aside>

<!-- Tablet Sidebar -->
<aside class="tablet-sidebar fixed inset-y-0 left-0 z-30 w-16 bg-white shadow-lg hidden md:block lg:hidden">
    <div class="flex flex-col h-full">
        <div class="flex items-center justify-center h-16 px-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                <img src="{{ asset('images/home/logo_yayasan.png') }}" alt="Logo Yayasan">
            </div>
        </div>

        <nav class="flex-1 px-2 py-4 overflow-y-auto">
            <ul class="space-y-4">
                <li>
                    <a href="{{ route('home') }}" data-tooltip="Home"
                        class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i class="fas fa-home text-lg"></i>
                    </a>
                </li>

                @can('admin')
                    <li>
                        <a href="{{ route('dashboard.index') }}" data-tooltip="Dashboard"
                            class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                    {{ request()->routeIs('dashboard.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <i class="fas fa-tachometer-alt text-lg"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manajemen-admin.index') }}" data-tooltip="Manajemen Aanggota"
                            class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                    {{ request()->routeIs('manajemen-admin.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <i class="fas fa-users-cog text-lg"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('jenis-kas.index') }}" data-tooltip="Jenis Kas"
                            class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                    {{ request()->routeIs('jenis-kas.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <i class="fas fa-tags text-lg"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kas.index') }}" data-tooltip="Manajemen Kas"
                            class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                    {{ request()->routeIs('kas.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <i class="fas fa-wallet text-lg"></i>
                        </a>
                    </li>
                @endcan

                @can('user')
                    <li>
                        <a href="{{ route('user.dashboard') }}" data-tooltip="Dashboard"
                            class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                    {{ request()->routeIs('user.dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <i class="fas fa-tachometer-alt text-lg"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.riwayat') }}" data-tooltip="Riwayat"
                            class="flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 text-gray-700
                                    {{ request()->routeIs('user.riwayat') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <i class="fas fa-history text-lg"></i>
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>

        <div class="p-2 border-t border-gray-200">
            @auth
                <div class="flex justify-center mb-3">
                    <div class="w-8 h-8 gradient-primary rounded-full flex items-center justify-center">
                        <span class="text-white font-medium text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex justify-center p-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex justify-center p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
            @endauth
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"
    onclick="toggleMobileSidebar()">
</div>

<!-- Hamburger Button (Mobile) -->
<button
    class="lg:hidden p-2 ml-2 text-gray-600 rounded-md hover:bg-gray-100 focus:outline-none fixed top-4 left-4 z-20"
    onclick="toggleMobileSidebar()">
    <i class="fas fa-bars"></i>
</button>

<style>
    /* Active state for navigation links */
    .nav-link.active,
    .mobile-nav-link.active {
        @apply bg-blue-50 text-blue-700;
    }

    .nav-link.active i,
    .mobile-nav-link.active i {
        @apply text-blue-600;
    }

    /* Desktop sidebar scroll */
    .desktop-sidebar nav {
        height: calc(100vh - 8rem);
    }

    /* Tablet sidebar tooltips */
    .tablet-sidebar li {
        position: relative;
    }

    .tablet-sidebar li a:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #1e40af;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        white-space: nowrap;
        margin-left: 0.5rem;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .tablet-sidebar li a:hover::after {
        opacity: 1;
    }

    /* Dropdown animation */
    [x-cloak] {
        display: none !important;
    }

    /* Submenu styling */
    .nav-link-sub {
        transition: all 0.2s ease;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
    // Mobile sidebar toggle
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');

        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    // Simpan state sidebar di localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            const sidebar = document.querySelector('.desktop-sidebar');
            sidebar.classList.add('w-20');
            sidebar.classList.remove('w-64');
        }

        // Tutup mobile sidebar saat klik link
        document.querySelectorAll('#mobile-sidebar .nav-link, #mobile-sidebar .nav-link-sub').forEach(link => {
            link.addEventListener('click', toggleMobileSidebar);
        });

        // Tooltip untuk tablet sidebar
        document.querySelectorAll('.tablet-sidebar a').forEach(link => {
            const icon = link.querySelector('i');
            if (icon) {
                const tooltip = icon.className.replace('fa-', '').replace('text-lg', '').trim();
                link.setAttribute('data-tooltip', tooltip.charAt(0).toUpperCase() + tooltip.slice(1));
            }
        });
    });

    // Fungsi untuk toggle sidebar desktop (alternatif jika tidak menggunakan Alpine)
    function toggleDesktopSidebar() {
        const sidebar = document.querySelector('.desktop-sidebar');
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');

        // Simpan state
        const isCollapsed = sidebar.classList.contains('w-20');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
</script>
