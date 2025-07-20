import "./bootstrap";
import Alpine from "alpinejs";
import Chart from "chart.js/auto";
window.Chart = Chart;

window.Alpine = Alpine;

// Theme utilities
window.themeUtils = {
    // Smooth scroll to element
    scrollTo(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        }
    },

    // Add loading state to buttons
    addLoadingState(button, text = "Memproses...") {
        if (button) {
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${text}
            `;
        }
    },

    // Remove loading state from buttons
    removeLoadingState(button, originalText) {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    },

    // Show toast notification
    showToast(message, type = "success") {
        const toast = document.createElement("div");
        const bgColor =
            type === "success"
                ? "bg-green-500"
                : type === "error"
                ? "bg-red-500"
                : type === "warning"
                ? "bg-yellow-500"
                : "bg-blue-500";

        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        // Show toast
        setTimeout(() => {
            toast.classList.remove("translate-x-full");
        }, 100);

        // Auto hide after 5 seconds
        setTimeout(() => {
            toast.classList.add("translate-x-full");
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }, 5000);
    },

    // Format currency (Indonesian Rupiah)
    formatCurrency(amount) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    },

    // Format date
    formatDate(date) {
        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }).format(new Date(date));
    },

    // Copy to clipboard
    copyToClipboard(text) {
        navigator.clipboard
            .writeText(text)
            .then(() => {
                this.showToast("Berhasil disalin ke clipboard!", "success");
            })
            .catch(() => {
                this.showToast("Gagal menyalin ke clipboard", "error");
            });
    },

    // Confirm dialog
    confirm(message, callback) {
        const modal = document.createElement("div");
        modal.className =
            "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md mx-4 transform scale-95 transition-transform duration-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfirmasi</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <div class="flex justify-end space-x-3">
                    <button class="btn-outline" onclick="this.closest('.fixed').remove()">
                        Batal
                    </button>
                    <button class="btn-primary" onclick="this.closest('.fixed').remove(); (${callback})()">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Show modal with animation
        setTimeout(() => {
            modal.querySelector(".bg-white").classList.remove("scale-95");
            modal.querySelector(".bg-white").classList.add("scale-100");
        }, 10);
    },
};

// Alpine.js data untuk komponen umum
Alpine.data("navbar", () => ({
    isOpen: false,
    toggle() {
        this.isOpen = !this.isOpen;
    },
    close() {
        this.isOpen = false;
    },
}));

Alpine.data("modal", () => ({
    isOpen: false,
    open() {
        this.isOpen = true;
        document.body.classList.add("overflow-hidden");
    },
    close() {
        this.isOpen = false;
        document.body.classList.remove("overflow-hidden");
    },
}));

Alpine.data("dropdown", () => ({
    isOpen: false,
    toggle() {
        this.isOpen = !this.isOpen;
    },
    close() {
        this.isOpen = false;
    },
}));

/*
// --- BAGIAN INI TIDAK DIPERLUKAN DAN KITA NONAKTIFKAN ---
// Menghapus fungsi submit via AJAX yang kompleks dan berpotensi error.
Alpine.data("form", () => ({
    isSubmitting: false,
    async submit(formElement, url) {
        // ... Logika submit via AJAX yang kita nonaktifkan
    },
}));
*/

Alpine.data("search", () => ({
    query: "",
    results: [],
    isLoading: false,

    async search(url) {
        if (this.query.length < 2) {
            this.results = [];
            return;
        }

        this.isLoading = true;

        try {
            const response = await fetch(
                `${url}?q=${encodeURIComponent(this.query)}`
            );
            const data = await response.json();
            this.results = data.results || [];
        } catch (error) {
            console.error("Search error:", error);
            this.results = [];
        } finally {
            this.isLoading = false;
        }
    },
}));

// Initialize theme
document.addEventListener("DOMContentLoaded", function () {
    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = "smooth";

    // --- PERBAIKAN: HAPUS BLOK INI ---
    /*
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener("submit", function (e) {
            const button = form.querySelector('button[type="submit"]');
            if (button && !button.disabled && form.checkValidity()) {
                window.themeUtils.addLoadingState(button);
            }
        });
    });
    */

    // Add fade-in animation to cards
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.animation = "fadeIn 0.6s ease-out";
            }
        });
    });

    document.querySelectorAll(".card-modern, .card-glass").forEach((card) => {
        observer.observe(card);
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll(".alert").forEach((alert) => {
        setTimeout(() => {
            alert.style.animation = "fadeOut 0.3s ease-out";
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 300);
        }, 5000);
    });
});

Alpine.start();
