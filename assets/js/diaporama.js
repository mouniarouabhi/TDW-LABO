// Diaporama centralisé
let slideIndex = 0;
let slideTimer;

function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    if (slides.length === 0) return;

    slideIndex = (index + slides.length) % slides.length;

    slides.forEach((s, i) => s.classList.toggle('active', i === slideIndex));
    indicators.forEach((d, i) => d.classList.toggle('active', i === slideIndex));
}

function changeSlide(step) {
    showSlide(slideIndex + step);
    restartTimer();
}

function goToSlide(index) {
    showSlide(index);
    restartTimer();
}

function restartTimer() {
    if (slideTimer) clearTimeout(slideTimer);
    slideTimer = setTimeout(() => changeSlide(1), 5000);
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('.slide').length > 1) {
        showSlide(0);
        restartTimer();
        document.querySelectorAll('.indicator').forEach((dot, idx) => {
            dot.addEventListener('click', () => goToSlide(idx));
        });
        document.querySelectorAll('.slide-nav.prev').forEach(btn => {
            btn.addEventListener('click', () => changeSlide(-1));
        });
        document.querySelectorAll('.slide-nav.next').forEach(btn => {
            btn.addEventListener('click', () => changeSlide(1));
        });
    }
});