import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import './bootstrap';
import './infinite-scroll';
import gsap from 'gsap';

if (document.getElementById('preloader')) {
    document.documentElement.classList.add('preloader-active');

    window.addEventListener('load', () => {
        const tl = gsap.timeline({
            onComplete: () => {
                gsap.to('#preloader', {
                    opacity: 0,
                    duration: 0.4,
                    ease: 'power2.in',
                    onComplete: () => {
                        document.getElementById('preloader').style.display = 'none';
                        document.documentElement.classList.remove('preloader-active');
                    }
                });
            }
        });

        tl.to('#preloader-logo', {
            opacity: 1,
            scale: 1,
            duration: 0.6,
            ease: 'back.out(1.7)'
        }).to('#preloader-bar', {
            opacity: 1,
            duration: 0.3
        }).to('#preloader-fill', {
            width: '100%',
            duration: 0.8,
            ease: 'power2.inOut'
        }).to({}, { duration: 0.3 });
    });
}
