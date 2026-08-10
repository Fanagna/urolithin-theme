/**
 * Recherche — assets/js/search.js
 *
 * Conversion de assets/main-search.js + assets/search-form.js (Dawn 15) :
 *   - SearchForm : toggle du bouton « reset » selon la valeur de l'input
 *   - MainSearch : synchronise tous les inputs de recherche de la page
 *     (formulaire du template + modale du header) et reset simultané
 *     (comportement Dawn, sans code mort : pas de prédictif).
 */
(function () {
	'use strict';

	var searchInputs = Array.prototype.slice.call(
		document.querySelectorAll('input[type="search"]')
	);

	// ── SearchForm : toggle du bouton reset ─────────────────────────
	function bindResetButton(input) {
		if (!input) return;
		var form = input.form;
		if (!form) return;

		var resetButton = form.querySelector('button[type="reset"]');
		if (!resetButton) return;

		var toggleReset = function () {
			if (input.value.length > 0) {
				resetButton.classList.remove('hidden');
			} else {
				resetButton.classList.add('hidden');
			}
		};

		input.addEventListener('input', function () {
			toggleReset();
			keepInSync(input.value, input);
		});
		toggleReset();

		form.addEventListener('reset', function (event) {
			// Dawn ne reset que s'il n'y a pas de résultat sélectionné
			// (pas de prédictif ici → on reset toujours).
			event.preventDefault();
			input.value = '';
			input.focus();
			toggleReset();
			keepInSync('', input);
		});
	}

	// ── MainSearch : synchro des inputs ─────────────────────────────
	function keepInSync(value, target) {
		searchInputs.forEach(function (input) {
			if (input !== target && input.value !== value) {
				input.value = value;
			}
		});
	}

	// Scroll doux vers l'input au focus sur mobile (comportement Dawn).
	searchInputs.forEach(function (input) {
		input.addEventListener('focus', function () {
			if (window.innerWidth < 750) {
				input.scrollIntoView({ behavior: 'smooth' });
			}
		});
		bindResetButton(input);
	});
})();
