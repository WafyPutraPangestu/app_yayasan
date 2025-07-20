<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Yayasan Management System' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>



    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <!-- Additional Meta Tags -->
    <meta name="description" content="Sistem Manajemen Yayasan">
    <meta name="author" content="Your Company">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- FontAwesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Additional Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }

        /* Page transition */
        .page-transition {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Layout Fixes */
        html {
            overflow-y: scroll;
        }

        body {
            overflow-x: hidden;
        }

        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .mobile-header {
                display: flex !important;
            }

            .desktop-sidebar {
                display: none !important;
            }

            .tablet-sidebar {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                margin-top: 4rem !important;
            }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .mobile-header {
                display: none !important;
            }

            .desktop-sidebar {
                display: none !important;
            }

            .tablet-sidebar {
                display: block !important;
            }

            .main-content {
                margin-left: 4rem !important;
                margin-top: 0 !important;
            }
        }

        @media (min-width: 1024px) {
            .mobile-header {
                display: none !important;
            }

            .desktop-sidebar {
                display: block !important;
            }

            .tablet-sidebar {
                display: none !important;
            }

            .main-content {
                margin-left: 16rem !important;
                margin-top: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Loading Screen -->
    <div id="loading-screen" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Memuat...</p>
        </div>
    </div>

    <!-- Mobile Header -->
    <header
        class="mobile-header fixed top-0 left-0 right-0 bg-white shadow-sm z-20 h-16 flex items-center px-4 lg:hidden">
        <button onclick="toggleMobileSidebar()" class="p-2 rounded-md text-gray-600 hover:bg-gray-100">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <div class="ml-4 flex items-center">
            <div class="w-8 h-8 gradient-primary rounded-lg flex items-center justify-center">
                <i class="fas fa-mosque text-white text-sm"></i>
            </div>
            <span class="ml-2 font-semibold text-gray-800">Yayasan</span>
        </div>
    </header>

    <!-- Sidebar Components -->
    <x-side />

    <!-- Main Content Area -->
    <div class="main-content min-h-screen flex flex-col">
        <!-- Main Content -->
        <main class="flex-1">
            <!-- Breadcrumb -->
            @if (isset($breadcrumbs))
                <nav class="bg-white px-4 py-3 border-b border-gray-200 sm:px-6 lg:px-8">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li>
                            <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>
                        @foreach ($breadcrumbs as $breadcrumb)
                            <li class="flex items-center">
                                <svg class="flex-shrink-0 h-4 w-4 text-gray-400 mx-2" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                @if ($loop->last)
                                    <span class="text-gray-900 font-medium">{{ $breadcrumb['title'] }}</span>
                                @else
                                    <a href="{{ $breadcrumb['url'] }}"
                                        class="text-gray-500 hover:text-gray-700">{{ $breadcrumb['title'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            <!-- Page Content -->
            <div class="page-transition p-4 sm:p-6">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        @unless (Route::currentRouteName() !== 'home')
            <x-footer />
        @endunless
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"
        onclick="toggleMobileSidebar()">
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full"
            id="success-toast">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span>{{ session('success') }}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full"
            id="error-toast">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span>{{ session('error') }}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Initialize -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading screen
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                }, 500);
            }

            // Show toast messages
            const successToast = document.getElementById('success-toast');
            const errorToast = document.getElementById('error-toast');

            if (successToast) {
                setTimeout(() => successToast.classList.remove('translate-x-full'), 100);
                setTimeout(() => successToast.remove(), 5000);
            }

            if (errorToast) {
                setTimeout(() => errorToast.classList.remove('translate-x-full'), 100);
                setTimeout(() => errorToast.remove(), 5000);
            }

            // --- PERBAIKAN DI SINI ---
            // Back to top button functionality
            const backToTopButton = document.getElementById('back-to-top');

            // Cek dulu apakah tombolnya ada di halaman ini
            if (backToTopButton) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopButton.classList.remove('hidden');
                    } else {
                        backToTopButton.classList.add('hidden');
                    }
                });
            }
        });

        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('mobile-sidebar-overlay');

            // Tambahkan pengecekan sebelum memanipulasi elemen
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            }
        }
    </script>
</body>

</html>
