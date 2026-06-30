/**
 * Variant Module — Product Main
 * Dépendances : aucune (chargé en 2ème, après accordion)
 * Exporte : PM.submitAddToCart, PM.updateVariantUI, PM.updateTotalPrice, PM.formatPrice
 * Variables partagées : PM.state.selectedVariantId, PM.state.currentVariantData, PM.state.currentQty
 */

(function () {
  'use strict';

  // ───────── SHARED STATE ─────────
  window.PM = window.PM || {};
  var state = window.PM.state = window.PM.state || {};
  state.selectedVariantId = null;
  state.currentVariantData = null;
  state.currentQty = 1;

  // ───────── DOM REFS ─────────
  var form = document.getElementById('pm-add-to-cart-form');
  if (!form) return;

  state.form = form;
  state.addBtn = document.getElementById('pm-add-btn');
  state.variantInput = form.querySelector('input[name="id"]');
  state.choices = form.querySelectorAll('.pm-choice');
  state.priceDisplays = form.querySelectorAll('.pm-price[data-variant-price]');
  state.totalAmountDisplay = document.getElementById('pm-total-amount');
  state.stickyTotalDisplay = document.getElementById('pm-sticky-total-amount');

  // Init selected variant
  state.selectedVariantId = state.variantInput ? state.variantInput.value : null;

  // ───────── VARIANT DATA ─────────
  var allVariants = {};

  if (window.productJSON && Array.isArray(window.productJSON.variants)) {
    window.productJSON.variants.forEach(function (v) {
      allVariants[v.id] = v;
    });
  } else {
    state.priceDisplays.forEach(function (el) {
      var vid = el.getAttribute('data-variant-price');
      if (vid) {
        allVariants[vid] = { id: vid, price: el.textContent.trim(), available: true };
      }
    });
  }

  // ───────── FORMAT PRICE ─────────
  function formatPrice(cents) {
    var dollars = (cents / 100).toFixed(2);
    if (window.Shopify && window.Shopify.money_format) {
      return window.Shopify.money_format.replace('{{amount}}', dollars);
    }
    return '$' + dollars;
  }
  PM.formatPrice = formatPrice;

  // ───────── UPDATE TOTAL PRICE ─────────
  function animateTotalPrice(el) {
    if (!el) return;
    el.classList.remove('pm-total-animate');
    void el.offsetWidth;
    el.classList.add('pm-total-animate');
  }

  function updateTotalPrice() {
    if (!state.currentVariantData) return;
    var priceCents = state.currentVariantData.price;
    if (typeof priceCents !== 'number') return;
    var totalCents = priceCents * state.currentQty;
    var formatted = formatPrice(totalCents);
    if (state.totalAmountDisplay) state.totalAmountDisplay.textContent = formatted;
    if (state.stickyTotalDisplay) state.stickyTotalDisplay.textContent = formatted;
    animateTotalPrice(document.getElementById('pm-total-price'));
    animateTotalPrice(document.getElementById('pm-sticky-total'));
  }
  PM.updateTotalPrice = updateTotalPrice;

  // ───────── UPDATE UI ─────────
  function updateVariantUI(variantId) {
    state.selectedVariantId = variantId;
    if (state.variantInput) state.variantInput.value = variantId;

    state.choices.forEach(function (c) {
      c.classList.toggle('active', c.getAttribute('data-variant-id') === variantId);
    });

    var variant = allVariants[variantId];
    state.currentVariantData = variant;

    // Update main button
    if (state.addBtn && variant) {
      var available = variant.available !== undefined ? variant.available : true;
      if (available) {
        var priceHtml = typeof variant.price === 'number' ? formatPrice(variant.price) : (variant.price || '');
        state.addBtn.innerHTML = (window.pmAddToCartText || 'Add to Cart') + ' — <span class="pm-btn-price">' + priceHtml + '</span>';
        state.addBtn.disabled = false;
      } else {
        state.addBtn.textContent = window.pmSoldOutText || 'Sold Out';
        state.addBtn.disabled = true;
      }
    }

    // Update sticky displays (delegated via shared state for sticky-cart module)
    if (typeof PM.onVariantChange === 'function') {
      PM.onVariantChange(variant);
    }

    updateTotalPrice();
  }
  PM.updateVariantUI = updateVariantUI;

  // ───────── CHOICE CLICK ─────────
  state.choices.forEach(function (choice) {
    choice.addEventListener('click', function () {
      var vid = choice.getAttribute('data-variant-id');
      if (vid) updateVariantUI(vid);
    });
  });

  // ───────── AJAX ADD TO CART ─────────
  function setLoadingState(loading) {
    var buttons = [];
    if (state.addBtn) buttons.push(state.addBtn);
    if (state.stickyBtn && state.stickyBtn !== state.addBtn) buttons.push(state.stickyBtn);

    buttons.forEach(function (btn) {
      btn.disabled = loading;
      btn.textContent = loading ? 'Adding…' : (window.pmAddToCartText || 'Add to Cart');
    });
  }

  function restoreButtonStates() {
    if (state.currentVariantData) {
      updateVariantUI(state.selectedVariantId);
    } else {
      if (state.addBtn) {
        state.addBtn.innerHTML = window.pmAddToCartText || 'Add to Cart';
        state.addBtn.disabled = false;
      }
      if (state.stickyBtn) {
        state.stickyBtn.innerHTML = window.pmAddToCartText || 'Add to Cart';
        state.stickyBtn.disabled = false;
      }
    }
  }

  function submitAddToCart(variantId) {
    if (!variantId) {
      alert('Please select a variant.');
      return;
    }

    var quantity = state.currentQty;
    setLoadingState(true);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/cart/add.js', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        restoreButtonStates();
        if (typeof window.openCartDrawer === 'function') {
          window.openCartDrawer();
        } else {
          location.reload();
        }
      } else {
        restoreButtonStates();
        var errMsg = 'Error adding to cart.';
        try {
          var errData = JSON.parse(xhr.responseText);
          if (errData && errData.description) errMsg = errData.description;
        } catch (_) { /* ignore */ }
        alert(errMsg);
      }
    };

    xhr.onerror = function () {
      restoreButtonStates();
      alert('Network error. Please try again.');
    };

    xhr.send(JSON.stringify({
      id: variantId,
      quantity: quantity
    }));
  }
  PM.submitAddToCart = submitAddToCart;

  // ───────── OVERRIDE NATIVE FORM SUBMIT ─────────
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!state.selectedVariantId) {
      alert('Please select a variant.');
      return;
    }
    submitAddToCart(state.selectedVariantId);
  });

  // ───────── EXPOSE FOR EXTERNAL USE ─────────
  window.pmSubmitAddToCart = submitAddToCart;
  window.pmUpdateVariantUI = updateVariantUI;

  // ───────── INIT ─────────
  if (state.selectedVariantId && allVariants[state.selectedVariantId]) {
    updateVariantUI(state.selectedVariantId);
  }

})();
