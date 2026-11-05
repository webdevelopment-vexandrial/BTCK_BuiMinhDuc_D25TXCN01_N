/* ------------------------------------------------------------------
   gallery.js
   • Masonry grid (CSS columns)
   • Parallax hero (video background already in HTML)
   • Header scroll effect (shadow / background change)
   • Hover overlay with title, price, add‑to‑cart button
   • IntersectionObserver: fade‑in + slide‑up on scroll
   • Lightbox (quick view) – native modal
   • Side‑drawer cart
------------------------------------------------------------------- */

document.addEventListener('DOMContentLoaded', () => {
  /* ---------- 1. Header scroll effect ---------- */
  const header = document.getElementById('site-header');
  const toggleHeader = () => {
    if (window.scrollY > 50) {
      header.classList.add('bg-bgDark/80', 'shadow-lg');
    } else {
      header.classList.remove('bg-bgDark/80', 'shadow-lg');
    }
  };
  window.addEventListener('scroll', toggleHeader);
  toggleHeader(); // init

  /* ---------- 2. Masonry (CSS Columns) already done in HTML ----------
     – just add IntersectionObserver for fade‑in */
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('opacity-100', 'translate-y-0');
          observer.unobserve(e.target);
        }
      });
    },
    { threshold: 0.1 }
  );

  document.querySelectorAll('.product-card').forEach((el) => {
    el.classList.add('opacity-0', 'translate-y-6', 'transition-all', 'duration-500');
    observer.observe(el);
  });

  /* ---------- 3. Lightbox (quick view) ---------- */
  const lightbox = document.createElement('div');
  lightbox.id = 'lightbox';
  lightbox.className =
    'fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm';
  lightbox.innerHTML = `
    <div class="relative max-w-4xl w-full p-4">
      <button id="lb-close" class="absolute top-2 right-2 text-white text-2xl">&times;</button>
      <img id="lb-img" src="" alt="" class="w-full rounded-lg shadow-xl" />
      <div class="mt-4 text-center text-white">
        <h4 id="lb-title" class="text-xl font-medium"></h4>
        <p id="lb-price" class="mt-1 text-primary"></p>
      </div>
    </div>
  `;
  document.body.appendChild(lightbox);

  const openLightbox = (card) => {
    const img = card.querySelector('img');
    const title = card.querySelector('h3')?.textContent || '';
    const price = card.querySelector('.price')?.textContent || '';
    document.getElementById('lb-img').src = img.src;
    document.getElementById('lb-title').textContent = title;
    document.getElementById('lb-price').textContent = price;
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
  };
  const closeLightbox = () => {
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
  };
  document.getElementById('lb-close').addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  document.querySelectorAll('.product-card').forEach((card) => {
    card.addEventListener('click', (e) => {
      // avoid opening when click on “Thêm vào giỏ”
      if (!e.target.closest('button')) openLightbox(card);
    });
  });

  /* ---------- 4. Side‑drawer Cart ---------- */
  const drawer = document.createElement('div');
  drawer.id = 'cart-drawer';
  drawer.className =
    'fixed right-0 top-0 h-full w-80 bg-bgDark shadow-lg transform translate-x-full transition-transform duration-300 z-40';
  drawer.innerHTML = `
    <div class="flex items-center justify-between p-4 border-b border-gray-700">
      <h3 class="text-lg font-medium">Giỏ hàng</h3>
      <button id="drawer-close" class="text-gray-400 hover:text-white">&times;</button>
    </div>
    <div id="drawer-content" class="p-4 overflow-y-auto">
      <p class="text-gray-400">Giỏ hàng trống</p>
    </div>
  `;
  document.body.appendChild(drawer);

  const openDrawer = () => drawer.classList.remove('translate-x-full');
  const closeDrawer = () => drawer.classList.add('translate-x-full');

  document.getElementById('drawer-close').addEventListener('click', closeDrawer);
  const cartButton = document.getElementById('cart-btn');
  if (cartButton) {
    cartButton.addEventListener('click', (e) => {
      e.preventDefault();
      openDrawer();
    });
  }

  // Example: add‑to‑cart (update counter, show placeholder)
  document.querySelectorAll('.product-card button').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // tránh mở lightbox
      const countEl = document.getElementById('cart-count');
      const newCount = parseInt(countEl.textContent) + 1;
      countEl.textContent = newCount;
      // (Ở đây chỉ demo – thực tế sẽ gọi AJAX tới backend)
    });
  });
});