(() => {
  if (window.__NDL_LOADED) return;
  window.__NDL_LOADED = true;

  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => [...r.querySelectorAll(s)];
  const once = (el, key) => { if (!el || el.dataset[key]) return false; el.dataset[key] = '1'; return true; };

  const state = { quantity: 1, priceLabel: '', bundleLabel: '' };
  let cartBusy = false; // shared across ALL buy buttons (hero + sticky bar),
                         // not per-button — stops a rapid click on one CTA
                         // then the other from firing two cart/add.js calls
                         // and doubling the quantity added.

  /* ── Bundle selector: syncs CTA price + sticky bar label across the page ── */
  const syncBundles = (chosen) => {
    qsa('.ndl-bundle').forEach(b => {
      const on = b === chosen;
      b.classList.toggle('selected', on);
      b.setAttribute('aria-pressed', String(on));
    });
    state.quantity = Math.max(1, parseInt(chosen.dataset.bottles, 10) || 1);
    state.priceLabel = chosen.dataset.priceLabel || '';
    state.bundleLabel = chosen.dataset.label || '';
    qsa('[data-ndl-sticky-bundle]').forEach(n => n.textContent = state.bundleLabel);
    qsa('[data-ndl-sticky-price]').forEach(n => n.textContent = state.priceLabel);
    // keep the native-form fallback quantity in sync with the chosen bundle
    qsa('[data-ndl-qty-input]').forEach(n => n.value = state.quantity);
  };

  /* ── Inline error toast — shown on any failed add-to-cart instead of
     silently redirecting to /cart with nothing added and no explanation. ── */
  const showCartError = (msg) => {
    let toast = qs('.ndl-cart-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'ndl-cart-toast';
      toast.setAttribute('role', 'alert');
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add('is-visible');
    clearTimeout(toast.__ndlTimer);
    toast.__ndlTimer = setTimeout(() => toast.classList.remove('is-visible'), 4200);
  };

  /* ── Add to cart: native Dawn <cart-drawer>.renderContents() + pubsub, same
     pattern as the other PDP builds in this project (no window.openCartDrawer,
     that global doesn't exist in this theme). ── */
  const addToCart = (button) => {
    if (cartBusy) return;
    const ctx = button.closest('[data-ndl-product]') || qs('[data-ndl-product]');
    if (!ctx) return;
    if (ctx.dataset.available === 'false') { showCartError('This product is currently sold out.'); return; }
    const variantId = parseInt(ctx.dataset.variantId, 10) || 0;
    if (!variantId) { if (ctx.dataset.productUrl) location.href = ctx.dataset.productUrl; return; }
    const label = qs('[data-ndl-cta-label]', button) || button;
    const old = label.textContent;
    cartBusy = true;
    qsa('[data-ndl-buy]').forEach(b => { b.dataset.loading = 'true'; });
    label.textContent = 'Adding…';
    fetch(window.Shopify.routes.root + 'cart/add.js', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({
        id: variantId,
        quantity: state.quantity,
        sections: ['cart-drawer', 'cart-icon-bubble'],
        sections_url: window.location.pathname
      })
    })
      .then(async r => {
        const data = await r.json();
        if (!r.ok) throw new Error(data.description || data.message || 'cart error');
        return data;
      })
      .then(data => {
        if (typeof publish === 'function' && typeof PUB_SUB_EVENTS !== 'undefined') {
          publish(PUB_SUB_EVENTS.cartUpdate, { source: 'ndl-liposomal', productVariantId: variantId, cartData: data });
        }
        document.dispatchEvent(new CustomEvent('cart:refresh', { bubbles: true }));
        label.textContent = 'Added ✓';
        const drawer = document.querySelector('cart-drawer');
        if (drawer && typeof drawer.renderContents === 'function') drawer.renderContents(data);
        else window.location = window.Shopify.routes.root + 'cart';
      })
      .catch(err => {
        label.textContent = old;
        showCartError(typeof err.message === 'string' && err.message !== 'cart error' ? err.message : "Couldn't add this to your cart — please try again.");
      })
      .finally(() => {
        cartBusy = false;
        qsa('[data-ndl-buy]').forEach(b => { b.dataset.loading = 'false'; });
        setTimeout(() => { if (label.textContent === 'Added ✓') label.textContent = old; }, 1200);
      });
  };

  /* ── Persistent add-to-cart bar (all breakpoints — the source mockup only
     revealed this on mobile, leaving desktop shoppers with no way to buy
     once they scrolled past the hero). Reveal once the hero's own CTA
     scrolls out of view (mirrors the psr-sticky-cart / pnf sticky pattern). ── */
  const initSticky = () => {
    const bar = qs('.ndl-sticky-bar');
    if (!bar || !once(bar, 'ndlInit')) return;
    const sentinel = qs('.ndl-hero-cta') || qs('.ndl-hero');
    if (sentinel && 'IntersectionObserver' in window) {
      new IntersectionObserver(es => {
        bar.classList.toggle('is-visible', !es[0].isIntersecting);
      }, { threshold: 0 }).observe(sentinel);
    } else {
      bar.classList.add('is-visible');
    }
  };

  /* ── Card-row sliders (routines/videos/ingredients/survey/testimonials/
     reviews/experts). Mobile-only rows already scroll natively via CSS
     scroll-snap; this just adds prev/next arrows on top and hides them
     when the row is scrolled all the way to that end. ── */
  const initHscroll = (wrap) => {
    if (!once(wrap, 'ndlHscroll')) return;
    const track = wrap.firstElementChild;
    const prev = qs('[data-ndl-hscroll-prev]', wrap);
    const next = qs('[data-ndl-hscroll-next]', wrap);
    if (!track || (!prev && !next)) return;
    const amount = () => {
      const card = track.firstElementChild;
      const gap = parseFloat(getComputedStyle(track).gap) || 0;
      return (card ? card.getBoundingClientRect().width : track.clientWidth) + gap;
    };
    const update = () => {
      const max = track.scrollWidth - track.clientWidth - 2;
      if (prev) prev.classList.toggle('is-hidden', track.scrollLeft <= 2);
      if (next) next.classList.toggle('is-hidden', track.scrollLeft >= max);
    };
    prev?.addEventListener('click', () => track.scrollBy({ left: -amount(), behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left: amount(), behavior: 'smooth' }));
    track.addEventListener('scroll', update, { passive: true });
    addEventListener('resize', update);
    update();
  };

  const init = (root = document) => {
    qsa('.ndl-bundle', root).forEach(b => { if (once(b, 'ndlBundle')) b.addEventListener('click', () => syncBundles(b)); });
    // preventDefault: the CTA is now a submit button inside a native
    // {% form 'product' %} fallback (so it works even before this script
    // runs); once JS is ready the AJAX drawer path takes over instead.
    qsa('[data-ndl-buy]', root).forEach(b => { if (once(b, 'ndlBuy')) b.addEventListener('click', (e) => { e.preventDefault(); addToCart(b); }); });
    qsa('.ndl-hscroll-wrap', root).forEach(initHscroll);
    initSticky();
    const selected = qs('.ndl-bundle.selected');
    if (selected) syncBundles(selected);
  };

  document.addEventListener('DOMContentLoaded', () => init());
  document.addEventListener('shopify:section:load', e => init(e.target));
  if (document.readyState !== 'loading') init();
})();
