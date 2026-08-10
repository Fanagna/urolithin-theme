/**
 * Collection / Facets — assets/js/collection.js
 *
 * Conversion des interactions de snippets/facets.liquid + assets/facets.js
 * (Dawn 15) pour WooCommerce :
 *   - soumission des formulaires de filtres (desktop FacetFiltersForm +
 *     FacetSortForm, mobile FacetFiltersFormMobile) en préservant les
 *     paramètres GET (orderby, filter_*, min_price, max_price, paged)
 *   - drawer mobile (menu-drawer : open / close / apply)
 *   - show-more / show-less sur les listes longues
 *   - champ prix : validation min/max avant soumission
 *
 * Fonctionne en vanilla JS (aucune dépendance), chargé sur les pages
 * archive produit (is_shop, is_product_category, is_product_tag).
 */
(function () {
	'use strict';

	if (!window.panstellarCollection || !window.panstellarCollection.ajax) {
		return;
	}

	// ── Helpers ──────────────────────────────────────────────────────
	function serializeForm(form) {
		var data = new URLSearchParams(new FormData(form));
		// Multi-checkbox (filter_*) → valeurs concaténées par virgule.
		var grouped = {};
		data.forEach(function (value, key) {
			if (key.indexOf('filter_') === 0) {
				if (!grouped[key]) grouped[key] = [];
				grouped[key].push(value);
			} else {
				grouped[key] = value;
			}
		});
		return grouped;
	}

	function mergeParams(forms) {
		var merged = {};
		forms.forEach(function (form) {
			var data = serializeForm(form);
			Object.keys(data).forEach(function (key) {
				merged[key] = data[key];
			});
		});
		return merged;
	}

	function buildUrl(params) {
		var url = new URL(window.location.href);
		// On repart d'une URL propre (page courante sans filtres/tri/pagination).
		['orderby', 'min_price', 'max_price', 'paged'].forEach(function (key) {
			url.searchParams.delete(key);
		});
		// Les clés filter_* sont dynamiques : on les retire toutes.
		url.searchParams.forEach(function (value, key) {
			if (key.indexOf('filter_') === 0) {
				url.searchParams.delete(key);
			}
		});
		Object.keys(params).forEach(function (key) {
			var value = params[key];
			if (Array.isArray(value)) {
				if (value.length) url.searchParams.set(key, value.join(','));
			} else if (value !== '' && value !== null && value !== undefined) {
				url.searchParams.set(key, value);
			}
		});
		return url.toString();
	}

	function submitFilters(forms, keepPage) {
		var params = mergeParams(forms);
		if (!keepPage) {
			delete params.paged;
		}
		window.location.href = buildUrl(params);
	}

	// ── Forms desktop : FacetFiltersForm + FacetSortForm ─────────────
	var desktopForm = document.getElementById('FacetFiltersForm');
	var sortForm = document.getElementById('FacetSortForm');

	if (desktopForm) {
		// Checkbox de filtre : soumission au changement (avec délai pour
		// permettre plusieurs sélections simultanées).
		var debounce = null;
		desktopForm.addEventListener('change', function (e) {
			// Tri horizontal : le select orderby vit dans FacetFiltersForm.
			if (e.target.name === 'orderby') {
				submitFilters([desktopForm, sortForm], false);
				return;
			}
			if (e.target.type === 'checkbox') {
				clearTimeout(debounce);
				debounce = setTimeout(function () {
					submitFilters([desktopForm, sortForm], false);
				}, 400);
			}
		});

		// Champs prix : soumission sur « Enter ».
		desktopForm.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && e.target.name && (e.target.name === 'min_price' || e.target.name === 'max_price')) {
				e.preventDefault();
				submitFilters([desktopForm, sortForm], false);
			}
		});

		// Prix : forcer min < max.
		desktopForm.addEventListener('change', function (e) {
			if (!e.target.name || (e.target.name !== 'min_price' && e.target.name !== 'max_price')) {
				return;
			}
			var min = desktopForm.querySelector('input[name="min_price"]');
			var max = desktopForm.querySelector('input[name="max_price"]');
			if (min && max && min.value && max.value && parseFloat(min.value) > parseFloat(max.value)) {
				min.value = max.value;
			}
		});
	}

	if (sortForm) {
		sortForm.addEventListener('change', function (e) {
			if (e.target.name === 'orderby') {
				submitFilters([desktopForm, sortForm], false);
			}
		});
	}

	// ── Drawer mobile (menu-drawer) ──────────────────────────────────
	var mobileDrawer = document.querySelector('menu-drawer.mobile-facets__wrapper');
	var mobileForm = document.getElementById('FacetFiltersFormMobile');

	function closeMobileDrawer() {
		if (mobileDrawer) {
			var details = mobileDrawer.querySelector('details');
			if (details) details.removeAttribute('open');
		}
	}

	if (mobileDrawer && mobileForm) {
		// L'ouverture/fermeture du drawer ET de ses sous-menus est gérée par
		// le custom element « menu-drawer » de header.js (MenuDrawer Dawn,
		// avec animation et trap focus) : aucun toggle manuel ici pour éviter
		// le double-déclenchement. Ce script ne gère que la soumission.

		// Boutons « Apply » : soumission du drawer.
		mobileDrawer.addEventListener('click', function (e) {
			if (e.target.closest('[data-mobile-facets-apply]')) {
				submitFilters([mobileForm], false);
			}
		});

		// Tri mobile : soumission immédiate.
		mobileForm.addEventListener('change', function (e) {
			if (e.target.name === 'orderby') {
				submitFilters([mobileForm], false);
			}
		});

		// « Enter » sur les champs prix du drawer.
		mobileForm.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && e.target.name && (e.target.name === 'min_price' || e.target.name === 'max_price')) {
				e.preventDefault();
				submitFilters([mobileForm], false);
			}
		});
	}

	// ── Show more / show less ────────────────────────────────────────
	document.querySelectorAll('show-more-button').forEach(function (btn) {
		var button = btn.querySelector('button');
		if (!button) return;
		button.addEventListener('click', function () {
			var items = btn.closest('.parent-display') ?
				btn.closest('.parent-display').querySelectorAll('.show-more-item') :
				[];
			var showMore = btn.querySelector('.label-show-more');
			var showLess = btn.querySelector('.label-show-less');
			items.forEach(function (item) {
				item.classList.toggle('hidden');
			});
			if (showMore) showMore.classList.toggle('hidden');
			if (showLess) showLess.classList.toggle('hidden');
		});
	});
})();
