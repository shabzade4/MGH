(function(){
  const nav = document.querySelector('.main-nav');
  const toggle = document.querySelector('.mobile-toggle');
  if (toggle && nav) {
    toggle.addEventListener('click', () => nav.classList.toggle('open'));
  }

  // Simple slider
  const slider = document.querySelector('.slider');
  if (slider) {
    const track = slider.querySelector('.slides');
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dots button');
    let index = 0;
    function go(i){ index = (i+slides.length)%slides.length; track.style.transform = `translateX(-${index*100}%)`; dots.forEach((d,k)=>d.classList.toggle('active', k===index)); }
    dots.forEach((btn,i)=> btn.addEventListener('click', ()=> go(i)));
    setInterval(()=> go(index+1), 5000);
  }

  // Cart badge update
  function getCart(){ try { return JSON.parse(localStorage.getItem('pe_cart')||'{}'); } catch(e){ return {}; } }
  function setCart(cart){ localStorage.setItem('pe_cart', JSON.stringify(cart)); document.dispatchEvent(new CustomEvent('pe:cart:update')); }

  function updateServerCart(productId, qty){
    if (typeof pe_ajax === 'undefined') return Promise.resolve();
    const body = new URLSearchParams({ action: 'pe_add_to_cart', product_id: String(productId), qty: String(qty||1) });
    return fetch(pe_ajax.url, { method: 'POST', headers: { 'Content-Type':'application/x-www-form-urlencoded' }, body }).then(r=>r.json()).catch(()=>({}));
  }

  document.querySelectorAll('[data-add-to-cart]')?.forEach(btn => {
    btn.addEventListener('click', () => {
      const id = String(btn.getAttribute('data-product-id'));
      const title = btn.getAttribute('data-product-title') || '';
      const cart = getCart();
      cart[id] = cart[id] ? { ...cart[id], qty: cart[id].qty + 1 } : { id, title, qty: 1 };
      setCart(cart);
      btn.classList.add('added');
      setTimeout(()=> btn.classList.remove('added'), 1200);
      updateServerCart(id, 1).then(()=> refreshBadgeFromServer());
    });
  });

  function refreshBadge(){
    const badge = document.querySelector('[data-cart-badge]');
    if (!badge) return;
    const cart = getCart();
    const count = Object.values(cart).reduce((s, it)=> s + (it.qty||0), 0);
    badge.textContent = String(count || 0);
  }

  function refreshBadgeFromServer(){
    const badge = document.querySelector('[data-cart-badge]');
    if (!badge || typeof pe_ajax === 'undefined') return;
    fetch(pe_ajax.url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({ action:'pe_cart_count' }) })
      .then(r=>r.json()).then(j=>{ if(j && j.success && j.data && typeof j.data.count !== 'undefined'){ badge.textContent = String(j.data.count); } });
  }

  document.addEventListener('pe:cart:update', refreshBadge);
  // Initial
  refreshBadge();
  refreshBadgeFromServer();
})();