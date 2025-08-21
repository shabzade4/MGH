// Bootstrap init for tooltips if any
document.addEventListener('DOMContentLoaded', () => {
  const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

  // Auto-cycle hero slider to the right
  const heroCarouselEl = document.querySelector('#heroCarousel');
  if (heroCarouselEl) {
    const carousel = new bootstrap.Carousel(heroCarouselEl, {
      interval: 5000,
      ride: 'carousel',
      pause: false,
      touch: true,
      wrap: true
    });
  }
});

