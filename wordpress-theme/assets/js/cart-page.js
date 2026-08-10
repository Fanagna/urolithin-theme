/**
 * Page panier — assets/js/cart-page.js
 *
 * Conversion de assets/cart.js (Dawn 15) pour la page panier
 * (woocommerce/cart/cart.php) :
 *   - CartItems : changement de quantité (debounce) → ?wc-ajax=update_cart
 *     (format natif cart[KEY][qty] — l'endpoint « update_qty » n'existe pas)
 *   - CartRemoveButton : suppression → ?wc-ajax=remove_cart_item
 *   - CartNote : note de commande (debounce) → admin-ajax panstellar_cart_note
 *   - rafraîchissement des fragments (#main-cart-items, .cart__footer,
 *     #cart-icon-bubble, #CartDrawer-*) après chaque opération
 *
 * Le custom element « cart-remove-button » est déjà défini globalement par
 * cart-drawer.js : ici on l'étend pour la page panier (handler dans le
 * contexte cart-items de la page).
 */
(function () {
	'use strict';

	if (!window.panstellarCartPage) return;

	var wcAjaxUrl = function (action) {
		return panstellarCartPage.ajaxUrl + '?wc-ajax=' + action;
	};

	function fetchConfig() {
		return {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			}
		};
	}

	function debounce(fn, wait) {
		var t;
		return function () {
			var args = arguments;
			var ctx = this;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}

	/* ── Applique les fragments WooCommerce ─────────────────────────── */
	function applyFragments(fragments) {
		if (!fragments) return;
		Object.keys(fragments).forEach(function (selector) {
			var node = document.querySelector(selector);
			if (!node) return;
			var html = fragments[selector];
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

	/* ── État vide / non-vide de la page ────────────────────────────── */
	function refreshEmptyState() {
		// Le fragment #main-cart-items contient la table (ou rien si vide) ;
		// on bascule les classes is-empty sur le conteneur <cart-items>.
		var cartItemsEl = document.querySelector('cart-items');
		if (!cartItemsEl) return;
		var hasItems = !!cartItemsEl.querySelector('.cart-item');
		cartItemsEl.classList.toggle('is-empty', !hasItems);
		var warnings = cartItemsEl.querySelector('.cart__warnings');
		if (warnings) warnings.classList.toggle('hidden', hasItems);
		var form = cartItemsEl.querySelector('#cart');
		if (form) form.classList.toggle('hidden', !hasItems);

		var footer = document.getElementById('main-cart-footer');
		if (footer) {
			footer.classList.toggle('is-empty', !hasItems);
			var blocks = footer.querySelector('.cart__blocks');
			if (blocks) blocks.classList.toggle('hidden', !hasItems);
		}
	}

	// Nonce du formulaire panier (action « woocommerce-cart », champ caché du form).
	function getCartNonce() {
		var el = document.getElementById('woocommerce-cart-nonce');
		return el ? el.value : '';
	}

	// Les spinners du thème (template-parts/loading-spinner.php) portent la
	// classe « hidden » par défaut ; le CSS Dawn masque alors les prix :
	// .cart-item .loading__spinner:not(.hidden)~*{visibility:hidden}
	function enableLoading(input) {
		var item = input ? input.closest('.cart-item') : null;
		if (item) {
			item.querySelectorAll('.loading__spinner').forEach(function (sp) {
				sp.classList.remove('hidden');
			});
		}
	}

	function disableLoading() {
		document.querySelectorAll('.cart-item .loading__spinner').forEach(function (sp) {
			sp.classList.add('hidden');
		});
	}

	/* ── CartItems (quantité + suppression) ─────────────────────────── */
	function initCartItems() {
		var cartItemsEl = document.querySelector('cart-items');
		if (!cartItemsEl) return;

		// Changement de quantité (debounce 300ms, comme Dawn).
		var debouncedOnChange = debounce(function (event) { onChange(event); }, 300);
		cartItemsEl.addEventListener('change', debouncedOnChange);

		function onChange(event) {
			var input = event.target;
			if (!input.dataset || !input.dataset.cartItemKey) return;
			if (input.name && input.name.indexOf('cart[') === 0 && input.name.indexOf('[qty]') > -1) {
				updateQuantity(input.dataset.cartItemKey, input.value, input);
			}
		}

		function updateQuantity(key, quantity, input) {
			var value = parseInt(quantity, 10);
			if (isNaN(value) || value < 0) value = 0;
			if (value === 0) {
				removeItem(key);
				return;
			}
			// Endpoint natif WooCommerce update_cart : cart[KEY][qty]=N + nonce.
			var nonce = getCartNonce();
			var body = 'cart[' + encodeURIComponent(key) + '][qty]=' + encodeURIComponent(value)
				+ '&security=' + encodeURIComponent(nonce)
				+ '&nonce=' + encodeURIComponent(nonce);
			enableLoading(input);
			fetch(wcAjaxUrl('update_cart'), Object.assign(fetchConfig(), { body: body }))
				.then(function (response) { return response.json(); })
				.then(function (data) {
					if (data.error) { alert(data.error); return; }
					if (data.fragments) { applyFragments(data.fragments); refreshEmptyState(); return; }
					return refreshFragments();
				})
				.catch(function () { alert('Could not update cart. Please try again.'); })
				.finally(function () { disableLoading(); });
		}

		function removeItem(key) {
			var nonce = getCartNonce();
			var body = 'cart_item_key=' + encodeURIComponent(key)
				+ '&security=' + encodeURIComponent(nonce)
				+ '&nonce=' + encodeURIComponent(nonce);
			enableLoading();
			fetch(wcAjaxUrl('remove_cart_item'), Object.assign(fetchConfig(), { body: body }))
				.then(function (response) { return response.json(); })
				.then(function (data) {
					if (data.error) { alert(data.error); return; }
					if (data.fragments) { applyFragments(data.fragments); refreshEmptyState(); return; }
					return refreshFragments();
				})
				.catch(function () { alert('Could not remove item. Please try again.'); })
				.finally(function () { disableLoading(); });
		}

		// Suppression : le custom element global cart-remove-button gère les
		// clics ; on intercepte via le bouton de la page (data-cart-item-key).
		cartItemsEl.addEventListener('click', function (e) {
			var removeBtn = e.target.closest('cart-remove-button[data-cart-item-key]');
			if (!removeBtn) return;
			e.preventDefault();
			removeItem(removeBtn.dataset.cartItemKey);
		});
	}

	/* ── CartNote (note de commande) ────────────────────────────────── */
	function initCartNote() {
		// La textarea vit dans .cart__footer, remplacé à chaque refresh des
		// fragments : on utilise la délégation sur document pour conserver
		// le listener après chaque remplacement (sinon il serait perdu).
		var debouncedSave = debounce(function (noteEl) {
			if (!noteEl) return;
			// Endpoint admin-ajax dédié (WooCommerce n'en a pas pour la note seule).
			var body = 'action=panstellar_cart_note'
				+ '&woocommerce-cart-note=' + encodeURIComponent(noteEl.value)
				+ '&security=' + encodeURIComponent(getCartNonce());
			fetch(panstellarCartPage.adminUrl, Object.assign(fetchConfig(), { body: body }))
				.then(function (response) { return response.json(); })
				.then(function (data) {
					if (data.fragments) applyFragments(data.fragments);
				})
				.catch(function () { /* silencieux : la note sera envoyée au checkout */ });
		}, 1000);

		document.addEventListener('input', function (e) {
			if (e.target && e.target.matches('cart-note textarea[name="woocommerce-cart-note"]')) {
				debouncedSave(e.target);
			}
		});
	}

	/* ── Rafraîchit les fragments de la page + du drawer ────────────── */
	function refreshFragments() {
		return fetch(wcAjaxUrl('get_refreshed_fragments'), { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (fragData) {
				if (fragData.fragments) {
					applyFragments(fragData.fragments);
				}
				refreshEmptyState();
			});
	}

	initCartItems();
	initCartNote();
	refreshEmptyState();
})();
