/**
 * Quantity Module — Product Main
 * Dépendances : theme-sticky-variant.js (PM.state, PM.updateTotalPrice)
 * Chargement : 3ème (après variant)
 */

(function () {
  'use strict';

  var state = window.PM && window.PM.state;
  if (!state) return;

  // ───────── DOM REFS ─────────
  var qtyInput = document.getElementById('pm-qty-input');
  var stickyQtyDisplay = document.getElementById('pm-sticky-qty-display');
  var qtyMinusBtns = document.querySelectorAll('.pm-qty-minus');
  var qtyPlusBtns = document.querySelectorAll('.pm-qty-plus');

  // ───────── QUANTITY LOGIC ─────────
  function updateQty(newQty) {
    if (newQty < 1) newQty = 1;
    state.currentQty = newQty;
    if (qtyInput) qtyInput.value = newQty;
    if (stickyQtyDisplay) stickyQtyDisplay.textContent = newQty;
    if (typeof PM.updateTotalPrice === 'function') {
      PM.updateTotalPrice();
    }
  }

  // Quantity buttons
  qtyMinusBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var current = qtyInput ? parseInt(qtyInput.value, 10) || 1 : state.currentQty;
      if (current > 1) updateQty(current - 1);
    });
  });

  qtyPlusBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var current = qtyInput ? parseInt(qtyInput.value, 10) || 1 : state.currentQty;
      updateQty(current + 1);
    });
  });

  // Sync input change → sticky display
  if (qtyInput) {
    qtyInput.addEventListener('input', function () {
      var val = parseInt(qtyInput.value, 10);
      if (isNaN(val) || val < 1) val = 1;
      updateQty(val);
    });
  }

})();
