/**
 * Header Panstellar — assets/js/header.js
 *
 * Conversion autonome des interactions JavaScript du header Dawn :
 *   - StickyHeader                (sections/header.liquid, bloc {% javascript %})
 *   - MenuDrawer / HeaderDrawer   (assets/global.js)
 *   - DetailsModal (recherche)    (assets/global.js — modale de recherche)
 *   - SearchForm                  (assets/search-form.js — bouton reset)
 *   - Préparation des <details>   (assets/global.js — aria-expanded / aria-controls)
 *
 * Aucune dépendance : JS vanilla, sans jQuery. À enqueue avec wp_enqueue_script().
 */
(function () {
  'use strict';

  /* ── Aides (traduites depuis global.js) ─────────────────────────────── */

  function getFocusableElements(container) {
    return Array.from(
      container.querySelectorAll(
        "summary, a[href], button:enabled, [tabindex]:not([tabindex^='-']), [draggable], area, input:not([type=hidden]):enabled, select:enabled, textarea:enabled, object, iframe"
      )
    );
  }

  const trapFocusHandlers = {};

  function trapFocus(container, elementToFocus) {
    const elements = getFocusableElements(container);
    const first = elements[0];
    const last = elements[elements.length - 1];

    removeTrapFocus();

    trapFocusHandlers.focusin = function (event) {
      if (
        event.target !== container &&
        event.target !== last &&
        event.target !== first
      ) {
        return;
      }
      document.addEventListener('keydown', trapFocusHandlers.keydown);
    };

    trapFocusHandlers.focusout = function () {
      document.removeEventListener('keydown', trapFocusHandlers.keydown);
    };

    trapFocusHandlers.keydown = function (event) {
      if (event.code.toUpperCase() !== 'TAB') return;
      if (event.target === last && !event.shiftKey) {
        event.preventDefault();
        first.focus();
      }
      if ((event.target === container || event.target === first) && event.shiftKey) {
        event.preventDefault();
        last.focus();
      }
    };

    document.addEventListener('focusout', trapFocusHandlers.focusout);
    document.addEventListener('focusin', trapFocusHandlers.focusin);

    if (elementToFocus) elementToFocus.focus();
  }

  function removeTrapFocus(elementToFocus) {
    document.removeEventListener('focusin', trapFocusHandlers.focusin);
    document.removeEventListener('focusout', trapFocusHandlers.focusout);
    document.removeEventListener('keydown', trapFocusHandlers.keydown);
    if (elementToFocus) elementToFocus.focus();
  }

  function onKeyUpEscape(event) {
    if (event.code.toUpperCase() !== 'ESCAPE') return;
    const openDetailsElement = event.target.closest('details[open]');
    if (!openDetailsElement) return;
    const summaryElement = openDetailsElement.querySelector('summary');
    openDetailsElement.removeAttribute('open');
    summaryElement.setAttribute('aria-expanded', 'false');
    summaryElement.focus();
  }

  /* ── Préparation des <details> (équivalent global.js) ───────────────── */
  document
    .querySelectorAll('[id^="Details-"] summary')
    .forEach(function (summary) {
      summary.setAttribute('role', 'button');
      summary.setAttribute('aria-expanded', summary.parentNode.hasAttribute('open'));
      if (summary.nextElementSibling.getAttribute('id')) {
        summary.setAttribute('aria-controls', summary.nextElementSibling.id);
      }
      summary.addEventListener('click', function (event) {
        event.currentTarget.setAttribute(
          'aria-expanded',
          !event.currentTarget.closest('details').hasAttribute('open')
        );
      });
      // Escape ne ferme pas le drawer parent (les drawers ont leur propre gestion).
      if (!summary.closest('header-drawer, menu-drawer')) {
        summary.parentElement.addEventListener('keyup', onKeyUpEscape);
      }
    });

  /* ── StickyHeader (équivalent du {% javascript %} de header.liquid) ─── */
  class StickyHeader extends HTMLElement {
    constructor() {
      super();
    }

    connectedCallback() {
      this.header = document.querySelector('.section-header');
      this.headerIsAlwaysSticky =
        this.getAttribute('data-sticky-type') === 'always' ||
        this.getAttribute('data-sticky-type') === 'reduce-logo-size';
      this.headerBounds = {};

      this.setHeaderHeight();

      window
        .matchMedia('(max-width: 990px)')
        .addEventListener('change', this.setHeaderHeight.bind(this));

      if (this.headerIsAlwaysSticky) {
        this.header.classList.add('shopify-section-header-sticky');
      }

      this.currentScrollTop = 0;
      this.preventReveal = false;

      this.onScrollHandler = this.onScroll.bind(this);
      this.hideHeaderOnScrollUp = () => (this.preventReveal = true);

      this.addEventListener('preventHeaderReveal', this.hideHeaderOnScrollUp);
      window.addEventListener('scroll', this.onScrollHandler, false);

      this.createObserver();
    }

    setHeaderHeight() {
      document.documentElement.style.setProperty(
        '--header-height',
        this.header.offsetHeight + 'px'
      );
    }

    disconnectedCallback() {
      this.removeEventListener('preventHeaderReveal', this.hideHeaderOnScrollUp);
      window.removeEventListener('scroll', this.onScrollHandler);
    }

    createObserver() {
      const observer = new IntersectionObserver((entries, obs) => {
        this.headerBounds = entries[0].intersectionRect;
        obs.disconnect();
      });
      observer.observe(this.header);
    }

    onScroll() {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

      if (scrollTop > this.currentScrollTop && scrollTop > this.headerBounds.bottom) {
        this.header.classList.add('scrolled-past-header');
        if (this.preventHide) return;
        requestAnimationFrame(this.hide.bind(this));
      } else if (
        scrollTop < this.currentScrollTop &&
        scrollTop > this.headerBounds.bottom
      ) {
        this.header.classList.add('scrolled-past-header');
        if (!this.preventReveal) {
          requestAnimationFrame(this.reveal.bind(this));
        } else {
          window.clearTimeout(this.isScrolling);
          this.isScrolling = setTimeout(() => {
            this.preventReveal = false;
          }, 66);
          requestAnimationFrame(this.hide.bind(this));
        }
      } else if (scrollTop <= this.headerBounds.top) {
        this.header.classList.remove('scrolled-past-header');
        requestAnimationFrame(this.reset.bind(this));
      }

      this.currentScrollTop = scrollTop;
    }

    hide() {
      if (this.headerIsAlwaysSticky) return;
      this.header.classList.add(
        'shopify-section-header-hidden',
        'shopify-section-header-sticky'
      );
      this.closeMenuDisclosure();
      this.closeSearchModal();
    }

    reveal() {
      if (this.headerIsAlwaysSticky) return;
      this.header.classList.add('shopify-section-header-sticky', 'animate');
      this.header.classList.remove('shopify-section-header-hidden');
    }

    reset() {
      if (this.headerIsAlwaysSticky) return;
      this.header.classList.remove(
        'shopify-section-header-hidden',
        'shopify-section-header-sticky',
        'animate'
      );
    }

    closeMenuDisclosure() {
      this.disclosures =
        this.disclosures || this.header.querySelectorAll('header-menu');
      this.disclosures.forEach((disclosure) => disclosure.close());
    }

    closeSearchModal() {
      this.searchModal =
        this.searchModal || this.header.querySelector('details-modal');
      this.searchModal.close(false);
    }
  }

  customElements.define('sticky-header', StickyHeader);

  /* ── HeaderMenu — fermeture des sous-menus dropdown/mega (global.js) ── */
  class HeaderMenu extends HTMLElement {
    connectedCallback() {
      this.details = this.querySelector('details');
    }

    close() {
      if (this.details && this.details.hasAttribute('open')) {
        this.details.removeAttribute('open');
        const summary = this.details.querySelector('summary');
        if (summary) summary.setAttribute('aria-expanded', 'false');
      }
    }
  }

  customElements.define('header-menu', HeaderMenu);

  /* ── MenuDrawer (équivalent global.js) ──────────────────────────────── */
  class MenuDrawer extends HTMLElement {
    constructor() {
      super();
      this.mainDetailsToggle = this.querySelector('details');
      this.addEventListener('keyup', this.onKeyUp.bind(this));
      this.addEventListener('focusout', this.onFocusOut.bind(this));
      this.bindEvents();
    }

    bindEvents() {
      this.querySelectorAll('summary').forEach((summary) =>
        summary.addEventListener('click', this.onSummaryClick.bind(this))
      );
      this.querySelectorAll(
        'button:not(.localization-selector):not(.country-selector__close-button):not(.country-filter__reset-button)'
      ).forEach((button) =>
        button.addEventListener('click', this.onCloseButtonClick.bind(this))
      );
    }

    onKeyUp(event) {
      if (event.code.toUpperCase() !== 'ESCAPE') return;
      const openDetailsElement = event.target.closest('details[open]');
      if (!openDetailsElement) return;
      if (openDetailsElement === this.mainDetailsToggle) {
        this.closeMenuDrawer(event, this.mainDetailsToggle.querySelector('summary'));
      } else {
        this.closeSubmenu(openDetailsElement);
      }
    }

    onSummaryClick(event) {
      const summaryElement = event.currentTarget;
      const detailsElement = summaryElement.parentNode;
      const parentMenuElement = detailsElement.closest('.has-submenu');
      const isOpen = detailsElement.hasAttribute('open');
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

      function addTrapFocus() {
        trapFocus(summaryElement.nextElementSibling, detailsElement.querySelector('button'));
        summaryElement.nextElementSibling.removeEventListener('transitionend', addTrapFocus);
      }

      if (detailsElement === this.mainDetailsToggle) {
        if (isOpen) event.preventDefault();
        if (isOpen) {
          this.closeMenuDrawer(event, summaryElement);
        } else {
          this.openMenuDrawer(summaryElement);
        }
        document.documentElement.style.setProperty(
          '--viewport-height',
          window.innerHeight + 'px'
        );
      } else {
        setTimeout(() => {
          detailsElement.classList.add('menu-opening');
          summaryElement.setAttribute('aria-expanded', true);
          if (parentMenuElement) parentMenuElement.classList.add('submenu-open');
          if (!reducedMotion || reducedMotion.matches) {
            addTrapFocus();
          } else {
            summaryElement.nextElementSibling.addEventListener(
              'transitionend',
              addTrapFocus
            );
          }
        }, 100);
      }
    }

    openMenuDrawer(summaryElement) {
      setTimeout(() => {
        this.mainDetailsToggle.classList.add('menu-opening');
      });
      summaryElement.setAttribute('aria-expanded', true);
      trapFocus(this.mainDetailsToggle, summaryElement);
      document.body.classList.add('overflow-hidden-' + this.dataset.breakpoint);
    }

    closeMenuDrawer(event, elementToFocus) {
      if (event !== undefined) {
        this.mainDetailsToggle.classList.remove('menu-opening');
        this.mainDetailsToggle
          .querySelectorAll('details')
          .forEach((details) => {
            details.removeAttribute('open');
            details.classList.remove('menu-opening');
          });
        this.mainDetailsToggle
          .querySelectorAll('.submenu-open')
          .forEach((submenu) => submenu.classList.remove('submenu-open'));
        document.body.classList.remove('overflow-hidden-' + this.dataset.breakpoint);
        removeTrapFocus(elementToFocus);
        this.closeAnimation(this.mainDetailsToggle);
        if (event instanceof KeyboardEvent && elementToFocus) {
          elementToFocus.setAttribute('aria-expanded', 'false');
        }
      }
    }

    onFocusOut() {
      setTimeout(() => {
        if (
          this.mainDetailsToggle.hasAttribute('open') &&
          !this.mainDetailsToggle.contains(document.activeElement)
        ) {
          this.closeMenuDrawer();
        }
      });
    }

    onCloseButtonClick(event) {
      const detailsElement = event.currentTarget.closest('details');
      this.closeSubmenu(detailsElement);
    }

    closeSubmenu(detailsElement) {
      const parentMenuElement = detailsElement.closest('.submenu-open');
      if (parentMenuElement) parentMenuElement.classList.remove('submenu-open');
      detailsElement.classList.remove('menu-opening');
      detailsElement.querySelector('summary').setAttribute('aria-expanded', false);
      removeTrapFocus(detailsElement.querySelector('summary'));
      this.closeAnimation(detailsElement);
    }

    closeAnimation(detailsElement) {
      let animationStart;
      const handleAnimation = (time) => {
        if (animationStart === undefined) animationStart = time;
        if (time - animationStart < 400) {
          window.requestAnimationFrame(handleAnimation);
        } else {
          detailsElement.removeAttribute('open');
          const closestOpen = detailsElement.closest('details[open]');
          if (closestOpen) {
            trapFocus(closestOpen, detailsElement.querySelector('summary'));
          }
        }
      };
      window.requestAnimationFrame(handleAnimation);
    }
  }

  customElements.define('menu-drawer', MenuDrawer);

  /* ── HeaderDrawer (équivalent global.js) ────────────────────────────── */
  class HeaderDrawer extends MenuDrawer {
    constructor() {
      super();
    }

    openMenuDrawer(summaryElement) {
      this.header =
        this.header || document.querySelector('.section-header');
      this.borderOffset =
        this.borderOffset ||
        (this.closest('.header-wrapper').classList.contains(
          'header-wrapper--border-bottom'
        )
          ? 1
          : 0);
      document.documentElement.style.setProperty(
        '--header-bottom-position',
        parseInt(this.header.getBoundingClientRect().bottom - this.borderOffset) + 'px'
      );
      this.header.classList.add('menu-open');
      setTimeout(() => {
        this.mainDetailsToggle.classList.add('menu-opening');
      });
      summaryElement.setAttribute('aria-expanded', true);
      window.addEventListener('resize', this.onResize);
      trapFocus(this.mainDetailsToggle, summaryElement);
      document.body.classList.add('overflow-hidden-' + this.dataset.breakpoint);
    }

    closeMenuDrawer(event, elementToFocus) {
      if (elementToFocus) {
        super.closeMenuDrawer(event, elementToFocus);
        this.header.classList.remove('menu-open');
        window.removeEventListener('resize', this.onResize);
      }
    }

    onResize = () => {
      if (this.header) {
        document.documentElement.style.setProperty(
          '--header-bottom-position',
          parseInt(this.header.getBoundingClientRect().bottom - this.borderOffset) + 'px'
        );
      }
      document.documentElement.style.setProperty(
        '--viewport-height',
        window.innerHeight + 'px'
      );
    };
  }

  customElements.define('header-drawer', HeaderDrawer);

  /* ── DetailsModal — modale de recherche (équivalent global.js) ──────── */
  class DetailsModal extends HTMLElement {
    constructor() {
      super();
      this.mainDetailsToggle = this.querySelector('details');
      this.querySelectorAll('summary').forEach((summary) =>
        summary.addEventListener('click', this.onSummaryClick.bind(this))
      );
      this.querySelectorAll('button').forEach((button) =>
        button.addEventListener('click', this.onButtonClick.bind(this))
      );
    }

    onSummaryClick(event) {
      const summaryElement = event.currentTarget;
      const detailsElement = summaryElement.parentNode;
      const isOpen = detailsElement.hasAttribute('open');
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

      function addTrapFocus() {
        trapFocus(summaryElement.nextElementSibling, detailsElement.querySelector('input'));
        summaryElement.nextElementSibling.removeEventListener('transitionend', addTrapFocus);
      }

      if (isOpen) {
        event.preventDefault();
      } else {
        setTimeout(() => {
          if (!reducedMotion || reducedMotion.matches) {
            addTrapFocus();
          } else {
            summaryElement.nextElementSibling.addEventListener(
              'transitionend',
              addTrapFocus
            );
          }
        }, 100);
      }
    }

    onButtonClick(event) {
      const closeButton = event.currentTarget;
      if (closeButton.classList.contains('search-modal__close-button')) {
        this.close();
      }
    }

    close(focus) {
      const detailsElement = this.mainDetailsToggle;
      detailsElement.removeAttribute('open');
      removeTrapFocus(focus ? focus : null);
    }
  }

  customElements.define('details-modal', DetailsModal);

  /* ── SearchForm — formulaire de recherche (équivalent search-form.js) ─ */
  class SearchForm extends HTMLElement {
    constructor() {
      super();
      this.input = this.querySelector('input[type="search"]');
      this.resetButton = this.querySelector('button[type="reset"]');
      if (!this.input || !this.resetButton) return;

      this.input.addEventListener('input', this.onInputChange.bind(this));
      this.input.addEventListener('focus', this.onInputChange.bind(this));
      this.resetButton.addEventListener('click', this.onReset.bind(this));
    }

    onInputChange() {
      const isEmpty = this.input.value.trim() === '';
      this.resetButton.classList.toggle('hidden', isEmpty);
    }

    onReset() {
      this.input.value = '';
      this.resetButton.classList.add('hidden');
      this.input.focus();
    }
  }

  customElements.define('search-form', SearchForm);

  /* ── Compteur panier AJAX (équivalent de la mise à jour cart-icon-bubble) ──
     Le fragment WooCommerce « #cart-icon-bubble » (enregistré dans functions.php
     via woocommerce_add_to_cart_fragments) est re-rendu par WooCommerce lui-même
     après add-to-cart, sans rechargement de page. Aucun code supplémentaire
     nécessaire ici : WooCommerce s'occupe du remplacement du DOM du bubble. */
})();
