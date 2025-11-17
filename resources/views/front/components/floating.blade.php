<!-- Tombol Floating -->
<div x-data="{
        show: false,
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }" x-init="window.addEventListener('scroll', () => show = window.scrollY > 300)">
    <button x-show="show" @click="scrollToTop" x-transition.opacity.duration.300ms class="fixed bottom-6 right-6 bg-red-700 text-white p-5 rounded-full shadow-lg 
               hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
        aria-label="Back to top">
        <!-- Icon panah ke atas -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>
</div>