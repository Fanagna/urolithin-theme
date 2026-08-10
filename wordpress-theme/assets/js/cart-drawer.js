/**
 * Cart drawer Panstellar — assets/js/cart-drawer.js
 *
 * Conversion de assets/cart-drawer.js + assets/cart.js (Dawn 15.1.0) :
 *   - CartDrawer : ouverture/fermeture, Escape, overlay, icône panier
 *   - CartDrawerItems extends CartItems : quantité + suppression
 *   - CartRemoveButton : retrait d'un article
 *   - QuantityInput : boutons − / +
 * Sans dépendance à jQuery ni à global.js.
 *
 * Endpoints WooCommerce utilisés (équivalents de /cart/change.js) :
 *   - ?wc-ajax=update_qty        (cart_item_key + qty)
 *   - ?wc-ajax=remove_cart_item  (cart_item_key)
 *   - ?wc-ajax=get_refreshed_fragments (fragments #CartDrawer-* + #cart-icon-bubble)
 */
(function () {
	'use strict';

	/* ── Utilitaires (équivalents de global.js) ─────────────────────── */
	function debounce(fn, wait) {
		var t;
		return function () {
			var args = arguments;
			var ctx = this;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}

	function getFocusableElements(container) {
		return Array.from(
			container.querySelectorAll(
				'summary, a[href], button:enabled, [tabindex]:not([tabindex^="-"]), input:not([type=hidden]):enabled, select:enabled, textarea:enabled'
			)
		);
	}

	var trapFocusHandlers = {};
	function trapFocus(container, elementToFocus) {
		var elements = getFocusableElements(container);
		var first = elements[0];
		var last = elements[elements.length - 1];
		removeTrapFocus();
		trapFocusHandlers.focusin = function (event) {
			if (event.target !== container && event.target !== last && event.target !== first) return;
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
		elementToFocus.focus();
	}
	function removeTrapFocus(elementToFocus) {
		document.removeEventListener('focusin', trapFocusHandlers.focusin);
		document.removeEventListener('focusout', trapFocusHandlers.focusout);
		document.removeEventListener('keydown', trapFocusHandlers.keydown);
		if (elementToFocus) elementToFocus.focus();
	}

	/* ── Endpoint AJAX WooCommerce ──────────────────────────────────── */
	function wcAjaxUrl(action) {
		var url = '/';
		return url + '?wc-ajax=' + action;
	}

	function fetchConfig() {
		return {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		};
	}

	/**
	 * Applique les fragments WooCommerce ({selector: html}) au DOM.
	 * Équivalent du rendu des sections de Dawn.
	 */
	function applyFragments(fragments) {
		if (!fragments) return;
		Object.keys(fragments).forEach(function (selector) {
			var node = document.querySelector(selector);
			if (!node) return;
			var html = fragments[selector];
			// Fragment enveloppé (ex: #cart-icon-bubble cible .shopify-section) : on extrait.
			var tmp = document.createElement('div');
			tmp.innerHTML = html;
			var inner = tmp.querySelector(selector) || tmp;
			if (inner) {
				node.outerHTML = inner.outerHTML;
			} else {
				node.outerHTML = html;
			}
		});
	}

	/* ── QuantityInput (équivalent Dawn) ─────────────────────────────── */
	if (!customElements.get('quantity-input')) {
		customElements.define(
			'quantity-input',
			class extends HTMLElement {
				constructor() {
					super();
					this.input = this.querySelector('input');
					this.changeEvent = new Event('change', { bubbles: true });
					if (!this.input) return;
					this.input.addEventListener('change', this.onInputChange.bind(this));
					this.querySelectorAll('button').forEach(function (button) {
						button.addEventListener('click', this.onButtonClick.bind(this));
					}, this);
				}

				onInputChange() {
					this.validateQtyRules();
				}

				onButtonClick(event) {
					event.preventDefault();
					var previousValue = this.input.value;
					if (event.target.name === 'plus') {
						if (parseInt(this.input.dataset.min) > parseInt(this.input.step) && this.input.value === '0') {
							this.input.value = this.input.dataset.min;
						} else {
							this.input.stepUp();
						}
					} else {
						this.input.stepDown();
					}
					if (previousValue !== this.input.value) {
						this.input.dispatchEvent(this.changeEvent);
					}
					if (this.input.dataset.min === previousValue && event.target.name === 'minus') {
						this.input.value = parseInt(this.input.min);
					}
				}

				validateQtyRules() {
					var value = parseInt(this.input.value);
					if (this.input.min) {
						this.querySelector("[name='minus']").classList.toggle('disabled', parseInt(value) <= parseInt(this.input.min));
					}
					if (this.input.max) {
						var max = parseInt(this.input.max);
						this.querySelector("[name='plus']").classList.toggle('disabled', value >= max);
					}
				}
			}
		);
	}

	/* ── CartRemoveButton (équivalent Dawn) ──────────────────────────── */
	if (!customElements.get('cart-remove-button')) {
		customElements.define(
			'cart-remove-button',
			class extends HTMLElement {
				constructor() {
					super();
					this.addEventListener('click', function (event) {
						event.preventDefault();
						var key = this.dataset.cartItemKey;
						var drawerItems = this.closest('cart-drawer-items');
						if (drawerItems) drawerItems.removeItem(key);
					});
				}
			}
		);
	}

	/* ── CartDrawerItems (quantité + suppression via ?wc-ajax=) ─────── */
	if (!customElements.get('cart-drawer-items')) {
		customElements.define(
			'cart-drawer-items',
			class extends HTMLElement {
				constructor() {
					super();
					this.lineItemStatusElement = document.getElementById('CartDrawer-LineItemStatus');
					var debouncedOnChange = debounce(function (event) { this.onChange(event); }, 300).bind(this);
					this.addEventListener('change', debouncedOnChange);
				}

				onChange(event) {
					this.validateQuantity(event);
				}

				validateQuantity(event) {
					var input = event.target;
					if (!input.dataset.cartItemKey) return;
					var value = parseInt(input.value, 10);
					if (isNaN(value) || value < 0) value = 0;
					this.updateQuantity(input.dataset.cartItemKey, value, input);
				}

				/**
				 * Met à jour la quantité via ?wc-ajax=update_qty puis rafraîchit
				 * le drawer + la bulle panier via get_refreshed_fragments.
				 */
				updateQuantity(key, quantity, input) {
					var body = 'cart_item_key=' + encodeURIComponent(key) + '&quantity=' + encodeURIComponent(quantity);
					var self = this;
					self.enableLoading(input);

					fetch(wcAjaxUrl('update_qty'), Object.assign(fetchConfig(), { body: body }))
						.then(function (response) { return response.json(); })
						.then(function (data) {
							if (data.error) {
								alert(data.error);
								return;
							}
							// Rafraîchit les fragments (items + footer + bulle).
							return fetch(wcAjaxUrl('get_refreshed_fragments'), { credentials: 'same-origin' })
								.then(function (r) { return r.json(); })
								.then(function (fragData) {
									if (fragData.fragments) {
										applyFragments(fragData.fragments);
									}
									self.refreshEmptyState();
								});
						})
						.catch(function () {
							alert('Could not update cart. Please try again.');
						})
						.finally(function () {
							self.disableLoading();
						});
				}

				/**
				 * Supprime un article via ?wc-ajax=remove_cart_item.
				 */
				removeItem(key) {
					var body = 'cart_item_key=' + encodeURIComponent(key);
					var self = this;
					self.enableLoading();

					fetch(wcAjaxUrl('remove_cart_item'), Object.assign(fetchConfig(), { body: body }))
						.then(function (response) { return response.json(); })
						.then(function (data) {
							if (data.error) {
								alert(data.error);
								return;
							}
							return fetch(wcAjaxUrl('get_refreshed_fragments'), { credentials: 'same-origin' })
								.then(function (r) { return r.json(); })
								.then(function (fragData) {
									if (fragData.fragments) {
										applyFragments(fragData.fragments);
									}
									self.refreshEmptyState();
								});
						})
						.catch(function () {
							alert('Could not remove item. Please try again.');
						})
						.finally(function () {
							self.disableLoading();
						});
				}

				/**
				 * Recalcule l'état vide du drawer (is-empty) + footer après AJAX.
				 */
				refreshEmptyState() {
					var drawer = document.querySelector('cart-drawer');
					if (!drawer) return;
					var isEmpty = drawer.querySelectorAll('.cart-item').length === 0;
					drawer.classList.toggle('is-empty', isEmpty);
					this.classList.toggle('is-empty', isEmpty);

					var emptyContent = drawer.querySelector('.drawer__inner-empty');
					var footer = drawer.querySelector('.drawer__footer');
					if (isEmpty) {
						if (!emptyContent) {
							// Reconstruit le contenu vide minimal.
							var inner = drawer.querySelector('.drawer__inner');
							if (inner) {
								var div = document.createElement('div');
								div.className = 'drawer__inner-empty';
								div.innerHTML =
									'<div class="cart-drawer__warnings center"><div class="cart-drawer__empty-content">' +
									'<h2 class="cart__empty-text">' + (window.panstellarCartStrings ? window.panstellarCartStrings.empty : 'Your cart is empty') + '</h2>' +
									'</div></div>';
								inner.insertBefore(div, inner.firstChild);
							}
						}
						if (footer) footer.remove();
					} else {
						if (emptyContent) emptyContent.remove();
					}
				}

				enableLoading() {
					var items = document.getElementById('CartDrawer-CartItems');
					if (items) items.classList.add('cart__items--disabled');
					if (this.lineItemStatusElement) this.lineItemStatusElement.setAttribute('aria-hidden', 'false');
				}

				disableLoading() {
					var items = document.getElementById('CartDrawer-CartItems');
					if (items) items.classList.remove('cart__items--disabled');
				}
			}
		);
	}

	/* ── CartDrawer (ouverture / fermeture, équivalent Dawn) ─────────── */
	if (!customElements.get('cart-drawer')) {
		customElements.define(
			'cart-drawer',
			class extends HTMLElement {
				constructor() {
					super();
					this.addEventListener('keyup', function (evt) {
						if (evt.code === 'Escape') this.close();
					});
					var overlay = this.querySelector('#CartDrawer-Overlay');
					if (overlay) overlay.addEventListener('click', this.close.bind(this));
					this.setHeaderCartIconAccessibility();
				}

				setHeaderCartIconAccessibility() {
					var cartLink = document.querySelector('#cart-icon-bubble');
					if (!cartLink) return;
					cartLink.setAttribute('role', 'button');
					cartLink.setAttribute('aria-haspopup', 'dialog');
					cartLink.addEventListener('click', function (event) {
						event.preventDefault();
						this.open(cartLink);
					}.bind(this));
					cartLink.addEventListener('keydown', function (event) {
						if (event.code.toUpperCase() === 'SPACE') {
							event.preventDefault();
							this.open(cartLink);
						}
					}.bind(this));
				}

				open(triggeredBy) {
					if (triggeredBy) this.setActiveElement(triggeredBy);
					setTimeout(function () {
						this.classList.add('animate', 'active');
					}.bind(this));
					this.addEventListener(
						'transitionend',
						function () {
							var containerToTrapFocusOn = this.classList.contains('is-empty')
								? this.querySelector('.drawer__inner-empty')
								: document.getElementById('CartDrawer');
							var focusElement = this.querySelector('.drawer__inner') || this.querySelector('.drawer__close');
							if (containerToTrapFocusOn && focusElement) {
								trapFocus(containerToTrapFocusOn, focusElement);
							}
						}.bind(this),
						{ once: true }
					);
					document.body.classList.add('overflow-hidden');
				}

				close() {
					this.classList.remove('active');
					removeTrapFocus(this.activeElement);
					document.body.classList.remove('overflow-hidden');
				}

				setActiveElement(element) {
					this.activeElement = element;
				}
			}
		);
	}

	/* ── Ouverture programmatique (utilisée par les forms AJAX PDP) ─── */
	window.openCartDrawer = function () {
		var drawer = document.querySelector('cart-drawer');
		if (drawer) drawer.open();
	};

	document.addEventListener('DOMContentLoaded', function () {
		// Ouverture automatique du drawer après un add-to-cart natif WooCommerce.
		// L'événement jQuery « added_to_cart » est déclenché par wc-add-to-cart.js
		// (jQuery est toujours chargé quand les fragments WooCommerce sont actifs).
		if (window.jQuery && window.jQuery.fn) {
			window.jQuery(document.body).on('added_to_cart', function () {
				var drawer = document.querySelector('cart-drawer');
				if (drawer) drawer.open();
			});
		}
	});
})();
