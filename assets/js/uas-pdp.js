/**
 * UA Stack PDP — assets/js/uas-pdp.js
 *
 * Conversion du <script> inline de sections/uas-pdp.liquid (Dawn Shopify) :
 *   - AJAX add to cart (endpoint WooCommerce : ?wc-ajax=add_to_cart)
 *   - Accordéons buy card (Supports / Ingredients / Certificates)
 *   - Toggle One-time vs Subscribe
 *   - Sélecteur de variantes (mise à jour prix + hidden inputs)
 *   - Sélecteur de plans d'abonnement (15 / 23 / 31 %)
 *   - FAQ mini accordéons
 *   - Sticky ATC (IntersectionObserver)
 * Sans dépendance à jQuery.
 */
(function () {
	'use strict';

	/* ── Helper : endpoint AJAX WooCommerce (?wc-ajax=) ──────────────── */
	function wcAjaxUrl(action) {
		var url = (window.panstellarUas && window.panstellarUas.ajaxUrl) || '/';
		var sep = url.indexOf('?') > -1 ? '&' : '?';
		return url + sep + 'wc-ajax=' + action;
	}

	/* ── AJAX Add to Cart (équivalent de /cart/add.js) ───────────────── */
	function uasAddToCart(formEl, btn) {
		if (!btn || btn.dataset.loading) return;
		btn.dataset.loading = '1';
		var origText = btn.textContent;
		btn.textContent = 'Adding…';

		var fd = new FormData(formEl);
		// WooCommerce attend product_id / quantity / variation_id.
		fd.append('product_id', formEl.dataset.productId || '');
		fd.append('quantity', fd.get('quantity') || '1');

		fetch(wcAjaxUrl('add_to_cart'), {
			method: 'POST',
			credentials: 'same-origin',
			body: fd
		})
			.then(function (res) {
				if (!res.ok) throw new Error('HTTP ' + res.status);
				return res.json();
			})
			.then(function (response) {
				if (response && response.error) {
					throw new Error(response.error || 'Could not add to cart.');
				}
				// Fragments WooCommerce : on force le rechargement du fragment panier.
				if (window.wc_add_to_cart_params) {
					var fragmentsUrl = (window.panstellarUas && window.panstellarUas.cartFragmentsUrl) || '/?wc-ajax=get_refreshed_fragments';
					fetch(fragmentsUrl, { credentials: 'same-origin' })
						.then(function (r) { return r.json(); })
						.then(function (data) {
							if (data && data.fragments) {
								Object.keys(data.fragments).forEach(function (selector) {
									var node = document.querySelector(selector);
									if (node) node.outerHTML = data.fragments[selector];
								});
							}
						})
						.catch(function () {});
				}

				if (btn) {
					btn.dataset.added = '1';
					btn.textContent = '\u2713 Added';
				}
				setTimeout(function () {
					if (btn) {
						delete btn.dataset.added;
						btn.textContent = origText;
					}
				}, 1600);

				// Ouverture du drawer panier s'il existe.
				if (window.openCartDrawer) {
					window.openCartDrawer();
				}
			})
			.catch(function (err) {
				var msg = err && err.message ? err.message : 'Could not add to cart. Please try again.';
				alert(msg);
			})
			.finally(function () {
				delete btn.dataset.loading;
				if (!btn.dataset.added) btn.textContent = origText;
			});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var pdp = document.querySelector('.uas-pdp');
		if (!pdp) return;

		/* ── Soumission des formulaires add-to-cart ──────────────────── */
		document.querySelectorAll('.uas-atc-form').forEach(function (f) {
			f.addEventListener('submit', function (e) {
				e.preventDefault();
				uasAddToCart(f, f.querySelector('[name="add"]'));
			});
		});

		/* ── Accordéons buy card ─────────────────────────────────────── */
		pdp.querySelectorAll('.uas-acc-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var row = this.closest('.uas-acc-row');
				var isOpen = row.classList.toggle('open');
				this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		});

		/* ── One-time vs Subscribe toggle ────────────────────────────── */
		var productId = pdp.id.replace('uas-pdp-', '');
		var choiceOnce = document.getElementById('uas-choice-once-' + productId);
		var choiceSub = document.getElementById('uas-choice-sub-' + productId);
		var ctaBtn = document.getElementById('uas-cta-' + productId);
		var ctaText = ctaBtn ? ctaBtn.dataset.onceText || 'ADD TO CART' : 'ADD TO CART';

		function selectChoice(selected, other) {
			selected.classList.add('uas-choice--active');
			other.classList.remove('uas-choice--active');
		}

		if (choiceOnce && choiceSub) {
			choiceOnce.addEventListener('click', function () {
				selectChoice(choiceOnce, choiceSub);
				if (ctaBtn && !ctaBtn.disabled) ctaBtn.textContent = ctaText;
			});
			choiceSub.addEventListener('click', function () {
				selectChoice(choiceSub, choiceOnce);
				if (ctaBtn && !ctaBtn.disabled) ctaBtn.textContent = 'SUBSCRIBE & SAVE';
			});
		}

		/* ── Sélecteur de variantes ──────────────────────────────────── */
		var variantSel = document.getElementById('uas-variant-' + productId);
		if (variantSel) {
			variantSel.addEventListener('change', function () {
				var opt = this.options[this.selectedIndex];
				var vid = this.value;
				var price = opt.dataset.price || '';
				var available = opt.dataset.available === 'true';

				document.querySelectorAll('.uas-atc-form[data-product-id="' + productId + '"]').forEach(function (f) {
					var inp = f.querySelector('[name="variation_id"], [name="add-to-cart"]');
					if (inp) {
						// variation_id pour variable, add-to-cart reste le produit.
						if (inp.name === 'variation_id') inp.value = vid;
					}
				});

				var priceEl = document.getElementById('uas-price-' + productId);
				var stickyPriceEl = document.getElementById('uas-sticky-price-' + productId);
				if (priceEl) priceEl.textContent = price;
				if (stickyPriceEl) stickyPriceEl.textContent = price;

				if (ctaBtn) {
					ctaBtn.disabled = !available;
					if (!available) ctaBtn.textContent = 'Sold Out';
					else ctaBtn.textContent = choiceSub && choiceSub.classList.contains('uas-choice--active') ? 'SUBSCRIBE & SAVE' : ctaText;
				}
				var stickyBtn = document.getElementById('uas-sticky-btn-' + productId);
				if (stickyBtn) {
					stickyBtn.disabled = !available;
					stickyBtn.textContent = available ? ctaText : 'Sold Out';
				}
				var subBtn = document.getElementById('uas-sub-btn-' + productId);
				if (subBtn) subBtn.disabled = !available;
			});
		}

		/* ── Sélecteur de plans d'abonnement ─────────────────────────── */
		var planLabels = {
			'15': '3 MONTHS (SAVE 15%)',
			'23': '6 MONTHS (SAVE 23%)',
			'31': '12 MONTHS (SAVE 31%)'
		};
		var plansWrap = pdp.querySelector('.uas-plans');
		var planBtns = plansWrap ? plansWrap.querySelectorAll('.uas-plan[data-plan-id]') : [];
		var subBtn = document.getElementById('uas-sub-btn-' + productId);

		planBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				planBtns.forEach(function (b) {
					b.classList.remove('uas-plan--selected');
					b.setAttribute('aria-pressed', 'false');
				});
				this.classList.add('uas-plan--selected');
				this.setAttribute('aria-pressed', 'true');
				var pid = this.dataset.planId;
				// Propagation du plan : hidden inputs subscription_plan + dataset.
				document.querySelectorAll('.uas-atc-form[data-product-id="' + productId + '"]').forEach(function (f) {
					f.dataset.plan = pid;
					var planInput = f.querySelector('[name="subscription_plan"]');
					if (planInput) planInput.value = pid;
				});
				var label = planLabels[pid] || '';
				if (subBtn && !subBtn.disabled) subBtn.textContent = 'SUBSCRIBE — ' + label;
			});
		});

		/* ── FAQ mini accordéons ─────────────────────────────────────── */
		document.querySelectorAll('.uas-faq-mini__btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var item = this.closest('.uas-faq-mini__item');
				var isOpen = item.classList.toggle('open');
				this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		});

		/* ── Sticky ATC — apparition quand le hero sort de l'écran ───── */
		var sentinel = document.getElementById('uas-buy-' + productId);
		var sticky = document.getElementById('uas-sticky-' + productId);
		if (sentinel && sticky && 'IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				sticky.classList.toggle('uas-sticky--visible', !entries[0].isIntersecting);
			}, { threshold: 0 }).observe(sentinel);
		}
	});
})();
