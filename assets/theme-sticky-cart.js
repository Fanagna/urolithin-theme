/**
 * Sticky Cart Module — Product Main
 * Dépendances : theme-sticky-variant.js (PM.state, PM.submitAddToCart, PM.updateVariantUI)
 * Chargement : 4ème (dernier)
 */

(function () {
  'use strict';

  var state = window.PM && window.PM.state;
  if (!state) return;

  // ───────── DOM REFS ─────────
  var stickyBar = document.getElementById('pm-sticky-cart');
  var stickyBtn = document.getElementById('pm-sticky-btn');
  var stickyPriceDisplay = document.getElementById('pm-sticky-price');

  // Register sticky refs in shared state
  state.stickyBtn = stickyBtn;

  // ───────── STICKY CART VISIBILITY ─────────
  var hero = document.querySelector('.pm-hero');
  var stickyThreshold = 0;

  function getHeroBottom() {
    stickyThreshold = hero ? hero.offsetHeight : window.innerHeight;
  }

  function onScroll() {
    if (!stickyBar) return;
    stickyBar.classList.toggle('pm-visible', window.scrollY > stickyThreshold);
  }

  if (stickyBar) {
    getHeroBottom();
    onScroll();

    window.addEventListener('resize', function () {
      getHeroBottom();
      onScroll();
    });
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // ───────── REGISTER VARIANT CHANGE HANDLER ─────────
  PM.onVariantChange = function (variant) {
    if (!stickyBar) return;

    if (variant) {
      var stickyAvailable = variant.available !== undefined ? variant.available : true;
      if (stickyPriceDisplay) {
        stickyPriceDisplay.textContent = typeof variant.price === 'number'
          ? PM.formatPrice(variant.price)
          : (variant.price || '');
      }
      if (stickyBtn) {
        if (stickyAvailable) {
          var priceVal = typeof variant.price === 'number'
            ? PM.formatPrice(variant.price)
            : (variant.price || '');
          stickyBtn.innerHTML = (window.pmAddToCartText || 'Add to Cart') + ' — <span class="pm-sticky-btn-price">' + priceVal + '</span>';
          stickyBtn.disabled = false;
        } else {
          stickyBtn.textContent = window.pmSoldOutText || 'Sold Out';
          stickyBtn.disabled = true;
        }
      }
    }
  };

  // ───────── STICKY BUTTON CLICK ─────────
  if (stickyBtn) {
    stickyBtn.addEventListener('click', function (e) {
      e.preventDefault();
      if (!state.selectedVariantId) {
        alert('Please select a variant.');
        return;
      }
      PM.submitAddToCart(state.selectedVariantId);
    });
  }

  // Rattrapage : la variante était déjà initialisée avant que ce module ne charge
  if (state.currentVariantData) {
    PM.onVariantChange(state.currentVariantData);
  }

  // ───────── DAWN CART DRAWER INTEGRATION ─────────
  (function findDawnCartDrawer() {
    if (typeof window.openCartDrawer === 'function') return;

    var cartDrawerEl = document.querySelector('cart-drawer');
    if (cartDrawerEl && typeof cartDrawerEl.open === 'function') {
      window.openCartDrawer = function () { cartDrawerEl.open(); };
      return;
    }

    var toggleBtn = document.querySelector('cart-drawer-toggle') || document.querySelector('[data-cart-drawer-toggle]');
    if (toggleBtn && typeof toggleBtn.click === 'function') {
      window.openCartDrawer = function () { toggleBtn.click(); };
      return;
    }

    var cartLink = document.querySelector('a[href*="/cart"]');
    if (cartLink) {
      window.openCartDrawer = function () {
        var drawer = document.querySelector('cart-drawer');
        if (drawer && typeof drawer.open === 'function') {
          drawer.open();
        } else {
          cartLink.click();
        }
      };
      return;
    }

    window.openCartDrawer = function () { location.reload(); };
  })();

})();
