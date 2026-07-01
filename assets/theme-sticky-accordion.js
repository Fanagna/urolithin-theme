/**
 * Accordion Module — Product Main
 * Dépendances : aucune
 * Chargement : theme-sticky-accordion.js (1er)
 */

(function () {
  'use strict';

  // ─── Main accordion (Supports / Ingredients / Certificates) ───
  var accordion = document.getElementById('pm-accordion');
  if (accordion) {
    accordion.querySelectorAll('.pm-acc-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = btn.closest('.pm-acc-row');
        var isOpen = row.classList.toggle('open');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    });
  }

  // ─── FAQ accordion ───
  var faqSection = document.getElementById('pm-faq');
  if (faqSection) {
    faqSection.querySelectorAll('.pm-faq-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = btn.closest('.pm-faq-row');
        var isOpen = row.classList.toggle('open');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    });
  }

})();
