/**
 * Product Panstellar — assets/js/product.js
 *
 * Interactions du template produit (adaptées de Dawn global.js) :
 *   - quantity-input (boutons − / +)
 *   - galerie : clic sur vignette → activation de l'image correspondante
 *   - formulaire variable WooCommerce : mise à jour du variation_id + prix
 * Sans dépendance à jQuery ni à global.js.
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		// NB : quantity-input est géré par le custom element global de
		// cart-drawer.js (chargé sur toutes les pages). Pas de double handler.
		initGallery();
		initVariationForm();
	});

	/* ─── Galerie : vignettes → image active ─────────────────────────── */
	function initGallery() {
		document.querySelectorAll('media-gallery').forEach(function (gallery) {
			var viewer = gallery.querySelector('[id^="GalleryViewer-"]');
			var thumbs = gallery.querySelectorAll('.thumbnail-list__item[data-target]');
			if (!viewer || !thumbs.length) return;

			thumbs.forEach(function (thumb) {
				var button = thumb.querySelector('button');
				if (!button) return;
				button.addEventListener('click', function () {
					var target = thumb.dataset.target;
					var slide = viewer.querySelector('[data-media-id="' + target + '"]');
					if (!slide) return;

					viewer.querySelectorAll('.product__media-item').forEach(function (item) {
						item.classList.remove('is-active');
					});
					slide.classList.add('is-active');

					thumbs.forEach(function (t) {
						t.classList.remove('is-active');
						var b = t.querySelector('button');
						if (b) b.removeAttribute('aria-current');
					});
					thumb.classList.add('is-active');
					button.setAttribute('aria-current', 'true');

					// Mise à jour du compteur.
					var current = gallery.querySelector('.slider-counter--current');
					var all = Array.from(viewer.querySelectorAll('.product__media-item'));
					if (current) current.textContent = all.indexOf(slide) + 1;
				});
			});
		});
	}

	/* ─── Formulaire variable WooCommerce ────────────────────────────── */
	function initVariationForm() {
		// Cible le form.cart qui contient un input variation_id (produit variable).
		var form = Array.prototype.find.call(
			document.querySelectorAll('form.cart'),
			function (f) { return f.querySelector('input[name="variation_id"]'); }
		);
		if (!form) return;

		var variationIdInput = form.querySelector('input[name="variation_id"]');
		var productIdInput = form.querySelector('input[name="product_id"]');
		var productId = productIdInput ? productIdInput.value : '';
		var selects = form.querySelectorAll('select[name^="attribute_"]');
		var priceEl = productId
			? document.querySelector('#price-' + productId + ' .price')
			: null;

		// Données de variations injectées par functions.php (wp_add_inline_script).
		var variationsData = window.panstellarVariations || [];

		function updateVariation() {
			var selected = {};
			selects.forEach(function (select) {
				selected[select.name.replace('attribute_', '')] = select.value;
			});

			var match = variationsData.find(function (variation) {
				return Object.keys(selected).every(function (key) {
					var attr = variation.attributes['attribute_' + key];
					return attr === selected[key] || attr === '';
				});
			});

			if (match) {
				variationIdInput.value = match.variation_id;
				if (priceEl && match.display_price) {
					var currency = document.querySelector('.woocommerce-Price-currencySymbol');
					var symbol = currency ? currency.textContent : '$';
					priceEl.innerHTML = '<span class="woocommerce-Price-amount amount">' + symbol + match.display_price + '</span>';
				}
			}
		}

		selects.forEach(function (select) {
			select.addEventListener('change', updateVariation);
		});
	}
})();
