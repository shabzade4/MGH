// Bootstrap init for tooltips if any
document.addEventListener('DOMContentLoaded', () => {
  const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

  // Desktop hover for dropdowns (md and up)
  const enableHoverDropdowns = () => {
    const isDesktop = window.matchMedia('(min-width: 768px)').matches;
    document.querySelectorAll('.navbar .dropdown').forEach(drop => {
      const toggle = drop.querySelector('[data-bs-toggle="dropdown"], .dropdown-toggle');
      const menu = drop.querySelector('.dropdown-menu');
      if (!toggle || !menu) return;
      // Clean previous handlers
      drop.onmouseenter = null;
      drop.onmouseleave = null;
      if (isDesktop) {
        drop.onmouseenter = () => {
          const bsDropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);
          bsDropdown.show();
        };
        drop.onmouseleave = () => {
          const bsDropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);
          bsDropdown.hide();
        };
      }
    });
  };
  enableHoverDropdowns();
  window.addEventListener('resize', enableHoverDropdowns);

  // Auto-cycle hero slider to the RIGHT in RTL
  const heroCarouselEl = document.querySelector('#heroCarousel');
  if (heroCarouselEl) {
    const carousel = new bootstrap.Carousel(heroCarouselEl, {
      interval: 0, // we'll control direction manually
      ride: false,
      pause: false,
      touch: true,
      wrap: true
    });
    setInterval(() => {
      // Move to previous to create rightward motion in RTL
      carousel.prev();
    }, 5000);
  }
});

