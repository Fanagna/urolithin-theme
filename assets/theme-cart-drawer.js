/**
 * Cart Drawer Module — Urolithin Theme
 * Smooth AJAX cart drawer, no page reloads
 */
(function () {
  'use strict';

  var drawer = document.getElementById('pm-cart-drawer');
  var overlay = document.getElementById('pm-drawer-overlay');
  var closeBtn = document.getElementById('pm-drawer-close');
  var cartBtn = document.getElementById('pm-cart-btn');
  var cartCount = document.getElementById('pm-cart-count');
  var hamburger = document.getElementById('pm-hamburger');
  var mobileMenu = document.getElementById('pm-mobile-menu');

  if (!drawer) return;

  // ─── Money formatting ───
  function formatMoney(cents) {
    if (window.Shopify && window.Shopify.money_format) {
      return window.Shopify.money_format.replace('{{amount}}', (cents / 100).toFixed(2));
    }
    return '$' + (cents / 100).toFixed(2);
  }

  // ─── Show/hide drawer (visual only) ───
  function showDrawer() {
    drawer.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function hideDrawer() {
    drawer.classList.remove('open');
    document.body.style.overflow = '';
  }

  // ─── Open cart drawer with fresh data ───
  // This OVERRIDES window.openCartDrawer so ALL add-to-cart paths
  // (form submit, sticky button, cart icon) get fresh cart data.
  window.openCartDrawer = function () {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/cart.js', true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        var cart = JSON.parse(xhr.responseText);
        rebuildDrawer(cart);
      }
      showDrawer();
    };
    xhr.onerror = function () { showDrawer(); };
    xhr.send();
  };

  window.closeCartDrawer = hideDrawer;

  // ─── Cart icon click ───
  if (cartBtn) {
    cartBtn.addEventListener('click', function (e) {
      e.preventDefault();
      window.openCartDrawer();
    });
  }

  // ─── Overlay & close ───
  if (overlay) overlay.addEventListener('click', hideDrawer);
  if (closeBtn) closeBtn.addEventListener('click', hideDrawer);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer.classList.contains('open')) hideDrawer();
  });

  // ─── Hamburger / Mobile Menu ───
  if (hamburger) {
    hamburger.addEventListener('click', function () {
      hamburger.classList.toggle('active');
      mobileMenu.classList.toggle('open');
      document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
    });
  }

  if (mobileMenu) {
    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        hamburger.classList.remove('active');
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  // ─── Cart count badge ───
  function updateCartCount(count) {
    if (cartCount) cartCount.textContent = count > 0 ? count : '';
  }

  // ─── Escape HTML ───
  function esc(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  }

  // ─── Rebuild drawer from cart JSON ───
  function rebuildDrawer(cart) {
    var body = document.getElementById('pm-drawer-body');
    var footer = document.getElementById('pm-drawer-footer');
    if (!body) return;

    updateCartCount(cart.item_count);

    if (cart.item_count === 0) {
      body.innerHTML =
        '<div class="pm-drawer-empty">' +
          '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5">' +
            '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>' +
            '<line x1="3" y1="6" x2="21" y2="6"/>' +
            '<path d="M16 10a4 4 0 01-8 0"/>' +
          '</svg>' +
          '<p>Your cart is empty</p>' +
          '<a href="/collections/all" class="pm-cta" style="display:inline-flex;width:auto;padding:12px 28px;font-size:14px;text-decoration:none;">Continue Shopping</a>' +
        '</div>';
      if (footer) footer.innerHTML = '';
      return;
    }

    var html = '<div class="pm-drawer-items">';
    cart.items.forEach(function (item) {
      var variant = (item.variant_title && item.variant_title !== 'Default Title')
        ? '<div class="pm-drawer-item-variant">' + esc(item.variant_title) + '</div>'
        : '';
      html +=
        '<div class="pm-drawer-item" data-key="' + item.key + '">' +
          '<img src="' + (item.image || '') + '" alt="' + esc(item.product_title) + '" loading="lazy" class="pm-drawer-item-img">' +
          '<div class="pm-drawer-item-info">' +
            '<a href="' + esc(item.url) + '" class="pm-drawer-item-title">' + esc(item.product_title) + '</a>' +
            variant +
            '<div class="pm-drawer-item-price">' + formatMoney(item.price) + '</div>' +
            '<div class="pm-drawer-qty-wrap">' +
              '<button class="pm-drawer-qty-btn pm-drawer-qty-minus" data-key="' + item.key + '" type="button">−</button>' +
              '<input type="number" value="' + item.quantity + '" class="pm-drawer-qty-input" data-key="' + item.key + '" min="0" readonly>' +
              '<button class="pm-drawer-qty-btn pm-drawer-qty-plus" data-key="' + item.key + '" type="button">+</button>' +
              '<button class="pm-drawer-item-remove" data-key="' + item.key + '" type="button">' +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2">' +
                  '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' +
                '</svg>' +
              '</button>' +
            '</div>' +
          '</div>' +
        '</div>';
    });
    html += '</div>';
    body.innerHTML = html;

    if (footer) {
      footer.innerHTML =
        '<div class="pm-drawer-subtotal">' +
          '<span>Subtotal</span>' +
          '<span class="pm-drawer-total">' + formatMoney(cart.total_price) + '</span>' +
        '</div>' +
        '<form action="/cart" method="post">' +
          '<button type="submit" name="checkout" class="pm-cta" style="margin:0;">Checkout — ' + formatMoney(cart.total_price) + '</button>' +
        '</form>' +
        '<p class="pm-drawer-note">Free U.S. shipping on orders $75+</p>';
    }
  }

  // ─── Quantity +/- in drawer ───
  drawer.addEventListener('click', function (e) {
    var target = e.target.closest('.pm-drawer-qty-btn, .pm-drawer-item-remove');
    if (!target) return;

    var key = target.getAttribute('data-key');
    var wrap = target.closest('.pm-drawer-qty-wrap');
    if (!wrap) return;
    var input = wrap.querySelector('.pm-drawer-qty-input');
    var current = parseInt(input.value, 10) || 1;
    var next;

    if (target.classList.contains('pm-drawer-qty-plus')) {
      next = current + 1;
    } else if (target.classList.contains('pm-drawer-qty-minus')) {
      next = current > 1 ? current - 1 : 1;
    } else {
      next = 0;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/cart/change.js', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        var cart = JSON.parse(xhr.responseText);
        rebuildDrawer(cart);
      }
    };
    xhr.send(JSON.stringify({ id: key, quantity: next }));
  });

  // ─── Input change ───
  drawer.addEventListener('change', function (e) {
    var input = e.target.closest('.pm-drawer-qty-input');
    if (!input) return;
    var key = input.getAttribute('data-key');
    var val = parseInt(input.value, 10);
    if (isNaN(val) || val < 1) val = 1;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/cart/change.js', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        var cart = JSON.parse(xhr.responseText);
        rebuildDrawer(cart);
      }
    };
    xhr.send(JSON.stringify({ id: key, quantity: val }));
  });

})();
