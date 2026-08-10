(function () {
  'use strict';

  if (window.PNFNMNBooted) return;
  window.PNFNMNBooted = true;

  function init(root) {
    if (!root || root.getAttribute('data-pnf-initialized') === 'true') return;
    root.setAttribute('data-pnf-initialized', 'true');

    hideTemplateHeader(root);
    initGallery(root);
    initOffers(root);
    initFormulaCarousel(root);
    initTestimonials(root);
    initSticky(root);
  }

  function hideTemplateHeader(root) {
    if (!root || !root.hasAttribute('data-pnf-nmn')) return;

    [
      '.shopify-section-group-header-group',
      '.section-header',
      '.shopify-section-header-sticky',
      '.header-wrapper'
    ].forEach(function (selector) {
      document.querySelectorAll(selector).forEach(function (element) {
        element.style.display = 'none';
      });
    });
  }

  function initGallery(root) {
    var main = root.querySelector('[data-pnf-main-media]');
    var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-pnf-thumb]'));
    var prev = root.querySelector('[data-pnf-gallery-prev]');
    var next = root.querySelector('[data-pnf-gallery-next]');
    var media = main ? main.closest('.pnf__main-media') : null;
    if (!main || !thumbs.length) return;

    var activeIndex = Math.max(0, thumbs.findIndex(function (thumb) {
      return thumb.classList.contains('is-active');
    }));
    var touchStartX = null;

    function render(index, shouldScrollThumb) {
      activeIndex = (index + thumbs.length) % thumbs.length;

      thumbs.forEach(function (item, itemIndex) {
        var isActive = itemIndex === activeIndex;
        item.classList.toggle('is-active', isActive);
        if (isActive) item.setAttribute('aria-current', 'true');
        else item.removeAttribute('aria-current');
      });

      var thumb = thumbs[activeIndex];
      var imageUrl = thumb.getAttribute('data-image-url');
      var imageAlt = thumb.getAttribute('data-image-alt') || '';
      var slideIndex = thumb.getAttribute('data-index') || String(activeIndex + 1);

      if (imageUrl) {
        main.innerHTML = '<img src="' + escapeAttr(imageUrl) + '" alt="' + escapeAttr(imageAlt) + '" loading="eager" width="1400" height="1400">';
      } else {
        main.innerHTML = '<div class="pnf__placeholder"><span>image</span><strong>' + escapeHtml(slideIndex) + '</strong><span>selected slide</span></div>';
      }

      if (shouldScrollThumb !== false) {
        thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      }
    }

    thumbs.forEach(function (thumb, thumbIndex) {
      thumb.addEventListener('click', function () {
        render(thumbIndex);
      });
    });

    if (prev) prev.addEventListener('click', function () { render(activeIndex - 1); });
    if (next) next.addEventListener('click', function () { render(activeIndex + 1); });

    if (media) {
      media.addEventListener('touchstart', function (event) {
        touchStartX = event.changedTouches[0].clientX;
      }, { passive: true });

      media.addEventListener('touchend', function (event) {
        if (touchStartX === null) return;
        var delta = event.changedTouches[0].clientX - touchStartX;
        touchStartX = null;
        if (Math.abs(delta) < 38) return;
        render(delta < 0 ? activeIndex + 1 : activeIndex - 1);
      }, { passive: true });
    }

    render(activeIndex, false);
  }

  function initOffers(root) {
    var offers = root.querySelectorAll('[data-pnf-offer]');
    var quantity = root.querySelector('[data-pnf-quantity]');
    var ctaPrice = root.querySelector('[data-pnf-cta-price]');
    var stickyPrice = root.querySelector('[data-pnf-sticky-price]');
    var returnTo = root.querySelector('[data-pnf-return-to]');
    var form = root.querySelector('.pnf__product-form');
    if (!offers.length) return;

    function syncDiscount(offer) {
      if (!returnTo || !offer) return;

      var discountCode = offer.getAttribute('data-discount-code') || '';
      if (discountCode) {
        returnTo.disabled = false;
        returnTo.value = '/discount/' + encodeURIComponent(discountCode) + '?redirect=/cart';
      } else {
        returnTo.value = '';
        returnTo.disabled = true;
      }
    }

    offers.forEach(function (offer) {
      offer.addEventListener('click', function () {
        offers.forEach(function (item) { item.classList.remove('is-active'); });
        offer.classList.add('is-active');

        var qty = offer.getAttribute('data-quantity') || '1';
        var price = offer.getAttribute('data-price') || '';

        if (quantity) quantity.value = qty;
        if (ctaPrice && price) ctaPrice.textContent = price;
        if (stickyPrice && price) stickyPrice.textContent = price;
        syncDiscount(offer);
      });
    });

    syncDiscount(root.querySelector('[data-pnf-offer].is-active') || offers[0]);

    if (form) {
      form.addEventListener('submit', function (event) {
        var activeOffer = root.querySelector('[data-pnf-offer].is-active');
        var discountCode = activeOffer ? activeOffer.getAttribute('data-discount-code') || '' : '';
        if (!discountCode || form.getAttribute('data-pnf-discount-ready') === discountCode) return;

        event.preventDefault();
        form.setAttribute('data-pnf-discount-ready', discountCode);

        fetch('/discount/' + encodeURIComponent(discountCode) + '?redirect=/cart', {
          method: 'GET',
          credentials: 'same-origin',
          cache: 'no-store'
        }).finally(function () {
          if (typeof form.requestSubmit === 'function') form.requestSubmit();
          else form.submit();
        });
      });
    }
  }

  function initFormulaCarousel(root) {
    var carousel = root.querySelector('[data-pnf-formula-carousel]');
    if (!carousel) return;

    var track = carousel.querySelector('[data-pnf-formula-track]');
    var prev = carousel.querySelector('[data-pnf-formula-prev]');
    var next = carousel.querySelector('[data-pnf-formula-next]');
    if (!track || !prev || !next) return;

    function gapSize() {
      var styles = window.getComputedStyle(track);
      return parseFloat(styles.columnGap || styles.gap || '0') || 0;
    }

    function scrollStep() {
      var card = track.querySelector('.pnf__formula-card');
      if (!card) return track.clientWidth;
      return (card.getBoundingClientRect().width + gapSize()) * 2;
    }

    function update() {
      var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth - 2);
      var canScroll = maxScroll > 2;
      carousel.classList.toggle('is-scrollable', canScroll);
      prev.disabled = !canScroll || track.scrollLeft <= 2;
      next.disabled = !canScroll || track.scrollLeft >= maxScroll;
    }

    prev.addEventListener('click', function () {
      track.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
    });

    next.addEventListener('click', function () {
      track.scrollBy({ left: scrollStep(), behavior: 'smooth' });
    });

    track.addEventListener('scroll', function () {
      window.requestAnimationFrame(update);
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  function initTestimonials(root) {
    var slider = root.querySelector('[data-pnf-testimonials]');
    if (!slider) return;

    var track = slider.querySelector('[data-pnf-testimonial-track]');
    var slides = slider.querySelectorAll('[data-pnf-testimonial-slide]');
    var prev = slider.querySelector('[data-pnf-testimonial-prev]');
    var next = slider.querySelector('[data-pnf-testimonial-next]');
    var dotsWrap = slider.querySelector('[data-pnf-testimonial-dots]');
    if (!track || slides.length < 2) return;

    var index = 0;
    var delay = parseInt(slider.getAttribute('data-autoplay'), 10) || 4500;
    var timer = null;
    var dots = [];

    function visibleCount() {
      if (window.matchMedia('(max-width: 760px)').matches) return 2;
      return 4;
    }

    function maxIndex() {
      return Math.max(0, slides.length - visibleCount());
    }

    function buildDots() {
      dots = [];
      if (!dotsWrap) return;
      dotsWrap.innerHTML = '';
      for (var dotIndex = 0; dotIndex <= maxIndex(); dotIndex += 1) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'pnf__testimonial-dot';
        dot.setAttribute('aria-label', 'Show testimonial group ' + (dotIndex + 1));
        (function (targetIndex) {
          dot.addEventListener('click', function () { goTo(targetIndex); restart(); });
        })(dotIndex);
        dotsWrap.appendChild(dot);
        dots.push(dot);
      }
    }

    function goTo(nextIndex) {
      var max = maxIndex();
      index = nextIndex > max ? 0 : nextIndex < 0 ? max : nextIndex;
      var step = 100 / visibleCount();
      track.style.transform = 'translateX(' + (-index * step) + '%)';
      dots.forEach(function (dot, dotIndex) {
        dot.classList.toggle('is-active', dotIndex === index);
      });
    }

    function start() {
      stop();
      timer = window.setInterval(function () { goTo(index + 1); }, delay);
    }

    function stop() {
      if (timer) window.clearInterval(timer);
      timer = null;
    }

    function restart() {
      stop();
      start();
    }

    if (prev) prev.addEventListener('click', function () { goTo(index - 1); restart(); });
    if (next) next.addEventListener('click', function () { goTo(index + 1); restart(); });
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
    window.addEventListener('resize', function () {
      buildDots();
      goTo(Math.min(index, maxIndex()));
    });

    buildDots();
    goTo(0);
    start();
  }

  function initSticky(root) {
    var form = root.querySelector('.pnf__product-form');
    var sticky = root.querySelector('[data-pnf-sticky]');
    var stickyButton = root.querySelector('[data-pnf-sticky-button]');
    if (!form || !sticky || !window.IntersectionObserver) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting || entry.boundingClientRect.top > 0) {
          sticky.classList.remove('is-visible');
          sticky.setAttribute('aria-hidden', 'true');
        } else {
          sticky.classList.add('is-visible');
          sticky.setAttribute('aria-hidden', 'false');
        }
      });
    }, { threshold: 0, rootMargin: '0px 0px -20% 0px' });

    observer.observe(form);

    if (stickyButton) {
      stickyButton.addEventListener('click', function () {
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
      });
    }
  }

  function escapeAttr(value) {
    return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
  }

  function escapeHtml(value) {
    return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function boot(scope) {
    (scope || document).querySelectorAll('.pnf').forEach(init);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
  document.addEventListener('shopify:section:load', function (event) { boot(event.target); });
})();
