<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Footer Content -->
        <div class="py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-mosque text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Yayasan</h3>
                            <p class="text-sm text-gray-600">Management System</p>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                        Sistem manajemen yayasan yang membantu dalam pengelolaan administrasi, keuangan, dan operasional
                        yayasan secara efisien dan transparan.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors duration-200">
                            <i class="fab fa-facebook-f text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors duration-200">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors duration-200">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors duration-200">
                            <i class="fab fa-linkedin-in text-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
                        Menu Utama
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="{{ route('home') }}"
                                class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                <i class="fas fa-home mr-2 text-xs"></i>
                                Beranda
                            </a>
                        </li>
                        @auth
                            @can('admin')
                                <li>
                                    <a href="{{ route('manajemen-admin.index') }}"
                                        class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                        <i class="fas fa-users-cog mr-2 text-xs"></i>
                                        Manajemen Admin
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('kas.index') }}"
                                        class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                        <i class="fas fa-wallet mr-2 text-xs"></i>
                                        Manajemen Kas
                                    </a>
                                </li>
                            @endcan
                            @can('user')
                                <li>
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                        <i class="fas fa-tachometer-alt mr-2 text-xs"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.riwayat') }}"
                                        class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                        <i class="fas fa-history mr-2 text-xs"></i>
                                        Riwayat Pembayaran
                                    </a>
                                </li>
                            @endcan
                        @else
                            <li>
                                <a href="{{ route('login') }}"
                                    class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                    <i class="fas fa-sign-in-alt mr-2 text-xs"></i>
                                    Login
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
                        Bantuan
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#"
                                class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                <i class="fas fa-question-circle mr-2 text-xs"></i>
                                FAQ
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                <i class="fas fa-life-ring mr-2 text-xs"></i>
                                Dukungan
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                <i class="fas fa-book mr-2 text-xs"></i>
                                Dokumentasi
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center">
                                <i class="fas fa-envelope mr-2 text-xs"></i>
                                Kontak
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
                        Kontak
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-map-marker-alt text-blue-600 text-sm mt-1"></i>
                            <div>
                                <p class="text-gray-600 text-sm">
                                    Jl. Contoh No. 123<br>
                                    Tangerang, Banten 15117<br>
                                    Indonesia
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-phone text-blue-600 text-sm"></i>
                            <span class="text-gray-600 text-sm">+62 21 1234 5678</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-blue-600 text-sm"></i>
                            <span class="text-gray-600 text-sm">info@yayasan.com</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-clock text-blue-600 text-sm"></i>
                            <span class="text-gray-600 text-sm">
                                Senin - Jumat: 08:00 - 17:00
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Bottom Footer -->
        <div class="border-t border-gray-200 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>&copy; {{ date('Y') }} Yayasan Management System. All rights reserved.</span>
                </div>

                <div class="flex items-center space-x-6">
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm">
                        Kebijakan Privasi
                    </a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm">
                        Syarat & Ketentuan
                    </a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 text-sm">
                        Bantuan
                    </a>
                </div>
            </div>
        </div>

        <!-- Back to Top Button -->
        <div class="fixed bottom-8 right-8 z-30">
            <button id="back-to-top"
                class="hidden bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
                <i class="fas fa-arrow-up text-sm"></i>
            </button>
        </div>
    </div>
</footer>

<!-- Footer Styles -->
<style>
    /* Footer animations */
    footer {
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Hover effects for footer links */
    footer a:hover {
        transform: translateX(2px);
    }

    /* Social media icons hover effects */
    footer .fab:hover {
        transform: scale(1.2);
    }

    /* Newsletter input focus effect */
    footer input:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Back to top button */
    #back-to-top {
        backdrop-filter: blur(10px);
        background: rgba(37, 99, 235, 0.9);
    }

    #back-to-top:hover {
        background: rgba(29, 78, 216, 0.9);
    }
</style>

<!-- Footer JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Back to top button functionality
        const backToTopButton = document.getElementById('back-to-top');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('hidden');
                backToTopButton.classList.add('block');
            } else {
                backToTopButton.classList.add('hidden');
                backToTopButton.classList.remove('block');
            }
        });

        // Smooth scroll for all anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading animation to external links
        document.querySelectorAll('footer a[href^="http"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.target) {
                    e.preventDefault();
                    window.themeUtils.showToast('Membuka halaman...', 'info');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 500);
                }
            });
        });

        // Add intersection observer for footer animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideUp 0.6s ease-out';
                }
            });
        }, observerOptions);

        // Observe footer elements
        document.querySelectorAll('footer > div > div').forEach(el => {
            observer.observe(el);
        });
    });
</script>
