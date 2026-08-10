/* Social-proof blocks — shared carousel/strip scroll controls.
   Loaded once (defer) and works for every spf- section instance on the page.
   Event-delegated so it also covers sections added live in the theme editor. */
(function () {
  function scrollTarget(el, ratio, dir) {
    if (!el) return;
    el.scrollBy({ left: el.clientWidth * ratio * dir, behavior: 'smooth' });
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-spf-scroll]');
    if (!btn) return;
    var wrap = btn.closest('[data-spf-carousel]');
    /* review/video carousels have an inner [data-spf-track]; the logo strip
       is itself the scroll container, so fall back to the wrap element. */
    var track = wrap && (wrap.querySelector('[data-spf-track]') || wrap);
    scrollTarget(track, parseFloat(btn.dataset.spfRatio || '0.78'), Number(btn.dataset.spfScroll || 1));
  });
})();
