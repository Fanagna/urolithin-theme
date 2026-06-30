/**
 * Accordion Module — Product Main
 * Dépendances : aucune
 * Chargement : theme-sticky-accordion.js (1er)
 */

(function () {
  'use strict';

  var accordion = document.getElementById('pm-accordion');
  if (!accordion) return;

  accordion.querySelectorAll('.pm-acc-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var row = btn.closest('.pm-acc-row');
      var isOpen = row.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });

})();
