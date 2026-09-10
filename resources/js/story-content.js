/**
 * Penyaji blok story di halaman publik.
 *
 * Blok yang disisipkan editor (galeri geser, GLightbox, dan pembanding
 * Before/After) sudah membawa gaya inline-nya sendiri, tapi tiga hal butuh
 * JavaScript: tombol geser galeri, pembuka lightbox, dan penggeser pemisah
 * Before/After. Semuanya hanya berjalan di dalam .public-story-content.
 */

import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';

const initializePublicStoryGalleries = () => {
    document.querySelectorAll('.public-story-content .story-content-gallery, .public-story-content .story-lightbox-gallery').forEach((gallery) => {
        if (gallery.hasAttribute('data-story-lightbox-gallery')) {
            gallery.classList.add('story-content-gallery');
            gallery.style.removeProperty('display');
            gallery.style.removeProperty('grid-template-columns');
            gallery.style.removeProperty('gap');
            gallery.querySelectorAll(':scope > figure').forEach((figure) => figure.classList.add('story-content-figure'));
        }

        if (gallery.dataset.storyGalleryInitialized === 'true') return;

        const slides = Array.from(gallery.querySelectorAll(':scope > .story-content-figure'));
        if (slides.length < 2) return;

        const shell = document.createElement('div');
        const previous = document.createElement('button');
        const next = document.createElement('button');
        const counter = document.createElement('span');
        let activeIndex = 0;
        let scrollFrame = null;
        let dragging = false;
        let dragStartX = 0;
        let dragStartScrollLeft = 0;

        const isLightboxGallery = gallery.hasAttribute('data-story-lightbox-gallery');

        shell.className = isLightboxGallery
            ? 'story-gallery-shell story-gallery-shell--lightbox'
            : 'story-gallery-shell';
        gallery.before(shell);
        shell.append(gallery);

        previous.type = 'button';
        previous.className = 'story-gallery-button story-gallery-button--previous';
        previous.innerHTML = isLightboxGallery ? '&laquo;' : '&#8249;';
        previous.setAttribute('aria-label', 'Gambar sebelumnya');

        next.type = 'button';
        next.className = 'story-gallery-button story-gallery-button--next';
        next.innerHTML = isLightboxGallery ? '&raquo;' : '&#8250;';
        next.setAttribute('aria-label', 'Gambar berikutnya');

        counter.className = 'story-gallery-counter';
        counter.setAttribute('aria-live', 'polite');

        shell.append(previous, next, counter);
        gallery.dataset.storyGalleryInitialized = 'true';
        gallery.setAttribute('role', 'region');
        gallery.setAttribute('aria-label', 'Galeri gambar story');
        gallery.setAttribute('tabindex', '0');
        gallery.querySelectorAll('img').forEach((image) => image.setAttribute('draggable', 'false'));

        // Dipisah dari updateControls: menekan tombol sudah menentukan slide
        // tujuannya, jadi labelnya tidak perlu menunggu event scroll. Menunggu
        // event itu membuat tombolnya tampak mati di peramban yang tidak
        // mengirimkan scroll untuk perpindahan tanpa animasi.
        const terapkanKontrol = (index) => {
            activeIndex = Math.max(0, Math.min(slides.length - 1, index));
            previous.disabled = activeIndex === 0;
            next.disabled = activeIndex === slides.length - 1;
            counter.textContent = `${activeIndex + 1} / ${slides.length}`;
        };

        // Dipakai saat galeri digeser sendiri oleh pembaca (seret atau
        // trackpad): posisinya yang menentukan slide mana yang aktif.
        const updateControls = () => {
            const slideWidth = gallery.clientWidth || 1;
            terapkanKontrol(Math.round(gallery.scrollLeft / slideWidth));
        };

        const goTo = (index) => {
            const targetIndex = Math.max(0, Math.min(slides.length - 1, index));
            const tujuan = slides[targetIndex].offsetLeft - gallery.offsetLeft;
            const asal = gallery.scrollLeft;

            gallery.scrollTo({ left: tujuan, behavior: 'smooth' });
            terapkanKontrol(targetIndex);

            // Pada kontainer scroll-snap, Chrome bisa mengabaikan animasi
            // smooth sepenuhnya: scrollTo dipanggil, posisinya tidak bergeser
            // sedikit pun, dan tombolnya jadi diam-diam tak berfungsi. Kalau
            // setelah dua frame masih di tempat, pindahkan tanpa animasi.
            requestAnimationFrame(() => requestAnimationFrame(() => {
                if (asal !== tujuan && gallery.scrollLeft === asal) {
                    gallery.scrollTo({ left: tujuan, behavior: 'instant' });
                }
            }));
        };

        previous.addEventListener('click', () => goTo(activeIndex - 1));
        next.addEventListener('click', () => goTo(activeIndex + 1));
        gallery.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goTo(activeIndex - 1);
            }
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                goTo(activeIndex + 1);
            }
        });
        gallery.addEventListener('scroll', () => {
            if (scrollFrame !== null) window.cancelAnimationFrame(scrollFrame);
            scrollFrame = window.requestAnimationFrame(updateControls);
        }, { passive: true });
        gallery.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) return;
            if (event.target.closest('a.glightbox2')) return;
            dragging = true;
            dragStartX = event.clientX;
            dragStartScrollLeft = gallery.scrollLeft;
            gallery.classList.add('is-dragging');
            gallery.setPointerCapture(event.pointerId);
        });
        gallery.addEventListener('pointermove', (event) => {
            if (!dragging) return;
            gallery.scrollLeft = dragStartScrollLeft - (event.clientX - dragStartX);
        });
        const stopDragging = (event) => {
            if (!dragging) return;
            dragging = false;
            gallery.classList.remove('is-dragging');
            if (gallery.hasPointerCapture(event.pointerId)) gallery.releasePointerCapture(event.pointerId);
            goTo(Math.round(gallery.scrollLeft / (gallery.clientWidth || 1)));
        };
        gallery.addEventListener('pointerup', stopDragging);
        gallery.addEventListener('pointercancel', stopDragging);

        updateControls();
    });
};

let publicStoryLightbox = null;

const initializePublicBeforeAfter = () => {
    document.querySelectorAll('.public-story-content [data-story-before-after]').forEach((comparison) => {
        if (comparison.dataset.beforeAfterInitialized === 'true') return;

        const range = comparison.querySelector('[data-before-after-range]');
        if (!range) return;

        const updatePosition = () => comparison.style.setProperty('--before-after-position', `${range.value}%`);
        range.addEventListener('input', updatePosition);
        range.addEventListener('change', updatePosition);
        comparison.dataset.beforeAfterInitialized = 'true';
        updatePosition();
    });
};

const initializePublicStoryLightbox = () => {
    publicStoryLightbox?.destroy();
    publicStoryLightbox = null;

    if (!document.querySelector('.public-story-content .glightbox2')) return;

    publicStoryLightbox = GLightbox({
        selector: '.public-story-content .glightbox2',
        loop: false,
        touchNavigation: true,
        keyboardNavigation: true,
        openEffect: 'fade',
        closeEffect: 'fade',
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePublicStoryGalleries);
    document.addEventListener('DOMContentLoaded', initializePublicStoryLightbox);
    document.addEventListener('DOMContentLoaded', initializePublicBeforeAfter);
} else {
    initializePublicStoryGalleries();
    initializePublicStoryLightbox();
    initializePublicBeforeAfter();
}

document.addEventListener('livewire:navigated', initializePublicStoryGalleries);
document.addEventListener('livewire:navigated', initializePublicStoryLightbox);
document.addEventListener('livewire:navigated', initializePublicBeforeAfter);
window.addEventListener('resize', initializePublicStoryGalleries);
