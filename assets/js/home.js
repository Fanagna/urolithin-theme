/**
 * Home Panstellar — assets/js/home.js
 *
 * Classes SliderComponent + SlideshowComponent, adaptées de
 * assets/global.js (Dawn 15.1.0) pour la page d'accueil, sans dépendance
 * à jQuery ni à global.js. Le HTML généré par les template-parts home
 * utilise les mêmes ids (#Slider-*, #Slide-*) et boutons [name=previous|next].
 */
(function () {
	'use strict';

	/* ─── SliderComponent (équivalent Dawn) ─────────────────────────── */
	class SliderComponent extends HTMLElement {
		constructor() {
			super();
			this.slider = this.querySelector('[id^="Slider-"]');
			this.sliderItems = this.querySelectorAll('[id^="Slide-"]');
			this.enableSliderLooping = false;
			this.currentPageElement = this.querySelector('.slider-counter--current');
			this.pageTotalElement = this.querySelector('.slider-counter--total');
			this.prevButton = this.querySelector('button[name="previous"]');
			this.nextButton = this.querySelector('button[name="next"]');

			if (!this.slider || !this.nextButton) return;

			this.initPages();
			new ResizeObserver(() => this.initPages()).observe(this.slider);
			this.slider.addEventListener('scroll', this.update.bind(this));
			this.prevButton.addEventListener('click', this.onButtonClick.bind(this));
			this.nextButton.addEventListener('click', this.onButtonClick.bind(this));
		}

		initPages() {
			this.sliderItemsToShow = Array.from(this.sliderItems).filter((element) => element.clientWidth > 0);
			if (this.sliderItemsToShow.length < 2) return;
			this.sliderItemOffset =
				this.sliderItemsToShow[1].offsetLeft - this.sliderItemsToShow[0].offsetLeft;
			this.slidesPerPage = Math.floor(
				(this.slider.clientWidth - this.sliderItemsToShow[0].offsetLeft) / this.sliderItemOffset
			);
			this.totalPages = this.sliderItemsToShow.length - this.slidesPerPage + 1;
			this.update();
		}

		resetPages() {
			this.sliderItems = this.querySelectorAll('[id^="Slide-"]');
			this.initPages();
		}

		update() {
			if (!this.slider || !this.nextButton) return;
			const previousPage = this.currentPage;
			this.currentPage = Math.round(this.slider.scrollLeft / this.sliderItemOffset) + 1;
			if (this.currentPageElement && this.pageTotalElement) {
				this.currentPageElement.textContent = this.currentPage;
				this.pageTotalElement.textContent = this.totalPages;
			}

			if (this.currentPage !== previousPage) {
				this.dispatchEvent(
					new CustomEvent('slideChanged', {
						detail: {
							currentPage: this.currentPage,
							currentElement: this.sliderItemsToShow[this.currentPage - 1],
						},
					})
				);
			}

			if (this.enableSliderLooping) return;

			if (this.isSlideVisible(this.sliderItemsToShow[0]) && this.slider.scrollLeft === 0) {
				this.prevButton.setAttribute('disabled', 'disabled');
			} else {
				this.prevButton.removeAttribute('disabled');
			}

			if (this.isSlideVisible(this.sliderItemsToShow[this.sliderItemsToShow.length - 1])) {
				this.nextButton.setAttribute('disabled', 'disabled');
			} else {
				this.nextButton.removeAttribute('disabled');
			}
		}

		isSlideVisible(element, offset = 0) {
			const slideVisiblePosition =
				this.slider.clientWidth + this.slider.scrollLeft - offset;
			return (
				element.offsetLeft + element.clientWidth <= slideVisiblePosition &&
				element.offsetLeft >= this.slider.scrollLeft
			);
		}

		onButtonClick(event) {
			event.preventDefault();
			const step = event.currentTarget.dataset.step || 1;
			this.slideScrollPosition =
				event.currentTarget.name === 'next'
					? this.slider.scrollLeft + step * this.sliderItemOffset
					: this.slider.scrollLeft - step * this.sliderItemOffset;
			this.setSlidePosition(this.slideScrollPosition);
		}

		setSlidePosition(position) {
			this.slider.scrollTo({ left: position });
		}
	}

	/* ─── SlideshowComponent (équivalent Dawn) ───────────────────────── */
	class SlideshowComponent extends SliderComponent {
		constructor() {
			super();
			this.sliderControlWrapper = this.querySelector('.slider-buttons');
			this.enableSliderLooping = true;
			if (!this.sliderControlWrapper) return;

			this.sliderFirstItemNode = this.slider.querySelector('.slideshow__slide');
			if (this.sliderItemsToShow.length > 0) {
				this.currentPage = 1;
			}

			this.announcementBarSlider = this.querySelector('.announcement-bar-slider');
			this.announcerBarAnimationDelay = this.announcementBarSlider ? 250 : 0;

			this.sliderControlLinksArray = Array.from(
				this.sliderControlWrapper.querySelectorAll('.slider-counter__link')
			);
			this.sliderControlLinksArray.forEach((link) =>
				link.addEventListener('click', this.linkToSlide.bind(this))
			);

			this.slider.addEventListener('scroll', this.setSlideVisibility.bind(this));
			this.setSlideVisibility();

			if ('true' === this.slider.getAttribute('data-autoplay')) {
				this.setAutoPlay();
			}
		}

		setAutoPlay() {
			this.autoplaySpeed = 1000 * this.slider.dataset.speed;
			this.addEventListener('mouseover', this.focusInHandling.bind(this));
			this.addEventListener('mouseleave', this.focusOutHandling.bind(this));
			this.addEventListener('focusin', this.focusInHandling.bind(this));
			this.addEventListener('focusout', this.focusOutHandling.bind(this));

			if (this.querySelector('.slideshow__autoplay')) {
				this.sliderAutoplayButton = this.querySelector('.slideshow__autoplay');
				this.sliderAutoplayButton.addEventListener('click', this.autoPlayToggle.bind(this));
				this.autoplayButtonIsSetToPlay = true;
				this.play();
			} else {
				const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
				if (reducedMotion.matches) {
					this.pause();
				} else {
					this.play();
				}
			}
		}

		onButtonClick(event) {
			super.onButtonClick(event);
			this.wasClicked = true;
			const isFirstSlide = this.currentPage === 1;
			const isLastSlide = this.currentPage === this.sliderItemsToShow.length;
			if (isFirstSlide || isLastSlide) {
				if (isFirstSlide && event.currentTarget.name === 'previous') {
					this.slideScrollPosition =
						this.slider.scrollLeft + this.sliderFirstItemNode.clientWidth * this.sliderItemsToShow.length;
				} else if (isLastSlide && event.currentTarget.name === 'next') {
					this.slideScrollPosition = 0;
				}
				this.setSlidePosition(this.slideScrollPosition);
			}
		}

		setSlidePosition(position) {
			if (this.setPositionTimeout) clearTimeout(this.setPositionTimeout);
			this.setPositionTimeout = setTimeout(() => {
				this.slider.scrollTo({ left: position });
			}, this.announcerBarAnimationDelay);
		}

		update() {
			super.update();
			this.sliderControlButtons = this.querySelectorAll('.slider-counter__link');
			this.prevButton.removeAttribute('disabled');
			if (!this.sliderControlButtons.length) return;
			this.sliderControlButtons.forEach((button) => {
				button.classList.remove('slider-counter__link--active');
				button.removeAttribute('aria-current');
			});
			this.sliderControlButtons[this.currentPage - 1].classList.add('slider-counter__link--active');
			this.sliderControlButtons[this.currentPage - 1].setAttribute('aria-current', true);
		}

		setSlideVisibility() {
			const slideElements = this.sliderItemsToShow;
			if (slideElements.length === 0) return;
			const visibleElements = slideElements.filter((slide) => this.isSlideVisible(slide));
			visibleElements.forEach((slide) => slide.setAttribute('aria-hidden', 'false'));
			slideElements
				.filter((slide) => !visibleElements.includes(slide))
				.forEach((slide) => slide.setAttribute('aria-hidden', 'true'));
		}

		linkToSlide(event) {
			event.preventDefault();
			const slideIndex = this.sliderControlLinksArray.indexOf(event.currentTarget);
			this.slideScrollPosition = this.sliderFirstItemNode.clientWidth * slideIndex;
			this.setSlidePosition(this.slideScrollPosition);
			this.sliderControlLinksArray.forEach((link) =>
				link.classList.remove('slider-counter__link--active')
			);
			event.currentTarget.classList.add('slider-counter__link--active');
		}

		focusInHandling(event) {
			if (event.currentTarget.contains(event.relatedTarget)) return;
			this.pause();
		}

		focusOutHandling(event) {
			if (event.currentTarget.contains(event.relatedTarget)) return;
			this.play();
		}

		play() {
			this.slider.setAttribute('aria-live', 'off');
			clearInterval(this.autoplayInterval);
			this.autoplayInterval = setInterval(this.autoRotate.bind(this), this.autoplaySpeed);
		}

		pause() {
			this.slider.removeAttribute('aria-live');
			clearInterval(this.autoplayInterval);
		}

		autoRotate() {
			if (this.currentPage === this.sliderItemsToShow.length) {
				this.slideScrollPosition = 0;
			} else {
				this.slideScrollPosition = this.sliderItemOffset * this.currentPage;
			}
			this.setSlidePosition(this.slideScrollPosition);
		}

		autoPlayToggle() {
			if (this.autoplayButtonIsSetToPlay) {
				this.pause();
			} else {
				this.play();
			}
			this.autoplayButtonIsSetToPlay = !this.autoplayButtonIsSetToPlay;
		}
	}

	if (!customElements.get('slider-component')) {
		customElements.define('slider-component', SliderComponent);
	}
	if (!customElements.get('slideshow-component')) {
		customElements.define('slideshow-component', SlideshowComponent);
	}
})();
