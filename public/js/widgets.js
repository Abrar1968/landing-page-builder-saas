/**
 * Interactive Widgets JavaScript
 * Handles all interactive widget functionality for published pages
 */

(function() {
    'use strict';

    /**
     * Initialize all widgets when DOM is ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initializeTabs();
        initializeAccordions();
        initializeToggles();
        initializeCountdowns();
        initializeCounters();
        initializeProgressBars();
        initializeSliders();
        initializeImageCarousels();
        initializeBasicGalleries();
        initializeForms();
    });

    /**
     * Tabs Widget
     */
    function initializeTabs() {
        const tabWidgets = document.querySelectorAll('[data-widget-type="tabs"]');

        tabWidgets.forEach(function(widget) {
            const tabButtons = widget.querySelectorAll('.tabs-nav button');
            const tabContents = widget.querySelectorAll('.tab-content');

            tabButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const tabIndex = this.getAttribute('data-tab');

                    // Remove active class from all buttons and contents
                    tabButtons.forEach(function(btn) { btn.classList.remove('active'); });
                    tabContents.forEach(function(content) { content.classList.remove('active'); });

                    // Add active class to clicked button and corresponding content
                    this.classList.add('active');
                    const targetContent = widget.querySelector('[data-tab-content="' + tabIndex + '"]');
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        });
    }

    /**
     * Accordion Widget
     */
    function initializeAccordions() {
        const accordionWidgets = document.querySelectorAll('[data-widget-type="accordion"]');

        accordionWidgets.forEach(function(widget) {
            const headers = widget.querySelectorAll('.accordion-header');

            headers.forEach(function(header) {
                header.addEventListener('click', function() {
                    const index = this.getAttribute('data-accordion-index');
                    const content = widget.querySelector('[data-accordion-content="' + index + '"]');
                    const isActive = this.classList.contains('active');

                    // Close all items first (accordion behavior - only one open at a time)
                    widget.querySelectorAll('.accordion-header').forEach(function(h) {
                        h.classList.remove('active');
                    });
                    widget.querySelectorAll('.accordion-content').forEach(function(c) {
                        c.classList.remove('active');
                    });

                    // If wasn't active, open it
                    if (!isActive && content) {
                        this.classList.add('active');
                        content.classList.add('active');
                    }
                });
            });
        });
    }

    /**
     * Toggle Widget (multiple can be open at same time)
     */
    function initializeToggles() {
        const toggleWidgets = document.querySelectorAll('[data-widget-type="toggle"]');

        toggleWidgets.forEach(function(widget) {
            const headers = widget.querySelectorAll('.toggle-header');

            headers.forEach(function(header) {
                header.addEventListener('click', function() {
                    const index = this.getAttribute('data-toggle-index');
                    const content = widget.querySelector('[data-toggle-content="' + index + '"]');

                    // Toggle current item
                    this.classList.toggle('active');
                    if (content) {
                        content.classList.toggle('active');
                    }
                });
            });
        });
    }

    /**
     * Countdown Widget
     */
    function initializeCountdowns() {
        const countdownWidgets = document.querySelectorAll('[data-widget-type="countdown"]');

        countdownWidgets.forEach(function(widget) {
            const dueDate = widget.getAttribute('data-due-date');
            const dueTime = widget.getAttribute('data-due-time') || '00:00';

            if (!dueDate) return;

            const targetDate = new Date(dueDate + 'T' + dueTime + ':00');

            function updateCountdown() {
                const now = new Date();
                const diff = targetDate - now;

                if (diff <= 0) {
                    // Countdown finished
                    const days = widget.querySelector('.countdown-days');
                    const hours = widget.querySelector('.countdown-hours');
                    const minutes = widget.querySelector('.countdown-minutes');
                    const seconds = widget.querySelector('.countdown-seconds');

                    if (days) days.textContent = '00';
                    if (hours) hours.textContent = '00';
                    if (minutes) minutes.textContent = '00';
                    if (seconds) seconds.textContent = '00';
                    return;
                }

                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                const days = widget.querySelector('.countdown-days');
                const hours = widget.querySelector('.countdown-hours');
                const minutes = widget.querySelector('.countdown-minutes');
                const seconds = widget.querySelector('.countdown-seconds');

                if (days) days.textContent = String(d).padStart(2, '0');
                if (hours) hours.textContent = String(h).padStart(2, '0');
                if (minutes) minutes.textContent = String(m).padStart(2, '0');
                if (seconds) seconds.textContent = String(s).padStart(2, '0');
            }

            // Initial update and then every second
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    }

    /**
     * Counter Widget (count-up animation)
     */
    function initializeCounters() {
        const counterWidgets = document.querySelectorAll('[data-widget-type="counter"]');

        counterWidgets.forEach(function(widget) {
            const target = parseInt(widget.getAttribute('data-target')) || 0;
            const duration = parseInt(widget.getAttribute('data-duration')) || 2000;
            const counterEl = widget.querySelector('.counter-number');

            if (!counterEl) return;

            // Use Intersection Observer for scroll-triggered animation
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        animateCounter(counterEl, target, duration);
                        observer.unobserve(widget);
                    }
                });
            }, { threshold: 0.5 });

            observer.observe(widget);
        });
    }

    function animateCounter(element, target, duration) {
        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(start + (target - start) * easeOutQuart);

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        requestAnimationFrame(update);
    }

    /**
     * Progress Bar Widget (scroll-triggered animation)
     */
    function initializeProgressBars() {
        const progressWidgets = document.querySelectorAll('[data-widget-type="progress-bar"]');

        progressWidgets.forEach(function(widget) {
            const percent = parseInt(widget.getAttribute('data-percent')) || 0;
            const percentEl = widget.querySelector('.progress-percent');

            // Use Intersection Observer for scroll-triggered animation
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        // Trigger the CSS animation
                        widget.classList.add('animated');

                        // Animate the percentage text
                        if (percentEl) {
                            animatePercent(percentEl, percent);
                        }

                        observer.unobserve(widget);
                    }
                });
            }, { threshold: 0.5 });

            observer.observe(widget);
        });
    }

    function animatePercent(element, target) {
        const duration = 1000;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(progress * target);

            element.textContent = current + '%';

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target + '%';
            }
        }

        requestAnimationFrame(update);
    }

    /**
     * Slider Widget
     */
    function initializeSliders() {
        const sliderWidgets = document.querySelectorAll('[data-widget-type="slider"]');

        sliderWidgets.forEach(function(widget) {
            const track = widget.querySelector('.slider-track');
            const slides = widget.querySelectorAll('.slider-slide');
            const prevBtn = widget.querySelector('.slider-arrow-prev');
            const nextBtn = widget.querySelector('.slider-arrow-next');
            const dots = widget.querySelectorAll('.slider-dot');
            const autoplay = widget.getAttribute('data-autoplay') === 'true';
            const autoplaySpeed = parseInt(widget.getAttribute('data-autoplay-speed')) || 3000;

            if (!track || slides.length === 0) return;

            let currentSlide = 0;
            let autoplayInterval = null;

            function goToSlide(index) {
                if (index < 0) index = slides.length - 1;
                if (index >= slides.length) index = 0;

                currentSlide = index;
                track.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';

                // Update dots
                dots.forEach(function(dot, i) {
                    dot.classList.toggle('active', i === currentSlide);
                });
            }

            function nextSlide() {
                goToSlide(currentSlide + 1);
            }

            function prevSlide() {
                goToSlide(currentSlide - 1);
            }

            function startAutoplay() {
                if (autoplay && !autoplayInterval) {
                    autoplayInterval = setInterval(nextSlide, autoplaySpeed);
                }
            }

            function stopAutoplay() {
                if (autoplayInterval) {
                    clearInterval(autoplayInterval);
                    autoplayInterval = null;
                }
            }

            // Event listeners
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    stopAutoplay();
                    prevSlide();
                    startAutoplay();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    stopAutoplay();
                    nextSlide();
                    startAutoplay();
                });
            }

            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-slide'));
                    stopAutoplay();
                    goToSlide(index);
                    startAutoplay();
                });
            });

            // Pause on hover
            widget.addEventListener('mouseenter', stopAutoplay);
            widget.addEventListener('mouseleave', startAutoplay);

            // Start autoplay
            startAutoplay();
        });
    }

    /**
     * Image Carousel Widget
     */
    function initializeImageCarousels() {
        const carouselWidgets = document.querySelectorAll('[data-widget-type="image-carousel"]');

        carouselWidgets.forEach(function(widget) {
            const track = widget.querySelector('.carousel-track');
            const slides = widget.querySelectorAll('.carousel-slide');
            const prevBtn = widget.querySelector('.carousel-arrow-prev');
            const nextBtn = widget.querySelector('.carousel-arrow-next');
            const dots = widget.querySelectorAll('.carousel-dot');
            const slidesToShow = parseInt(widget.getAttribute('data-slides-to-show')) || 3;
            const autoplay = widget.getAttribute('data-autoplay') === 'true';
            const autoplaySpeed = parseInt(widget.getAttribute('data-autoplay-speed')) || 3000;
            const infinite = widget.getAttribute('data-infinite') === 'true';

            if (!track || slides.length === 0) return;

            let currentIndex = 0;
            const maxIndex = Math.max(0, slides.length - slidesToShow);
            let autoplayInterval = null;

            function goToIndex(index) {
                if (infinite) {
                    if (index < 0) index = maxIndex;
                    if (index > maxIndex) index = 0;
                } else {
                    index = Math.max(0, Math.min(index, maxIndex));
                }

                currentIndex = index;
                const slideWidth = slides[0].offsetWidth;
                const gap = parseInt(getComputedStyle(track).gap) || 0;
                track.style.transform = 'translateX(-' + (currentIndex * (slideWidth + gap)) + 'px)';

                // Update dots
                dots.forEach(function(dot, i) {
                    dot.classList.toggle('active', i === currentIndex);
                });
            }

            function nextSlide() {
                goToIndex(currentIndex + 1);
            }

            function prevSlide() {
                goToIndex(currentIndex - 1);
            }

            function startAutoplay() {
                if (autoplay && !autoplayInterval) {
                    autoplayInterval = setInterval(nextSlide, autoplaySpeed);
                }
            }

            function stopAutoplay() {
                if (autoplayInterval) {
                    clearInterval(autoplayInterval);
                    autoplayInterval = null;
                }
            }

            // Event listeners
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    stopAutoplay();
                    prevSlide();
                    startAutoplay();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    stopAutoplay();
                    nextSlide();
                    startAutoplay();
                });
            }

            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    stopAutoplay();
                    goToIndex(index);
                    startAutoplay();
                });
            });

            // Pause on hover
            widget.addEventListener('mouseenter', stopAutoplay);
            widget.addEventListener('mouseleave', startAutoplay);

            // Start autoplay
            startAutoplay();
        });
    }

    /**
     * Basic Gallery Widget (Lightbox)
     */
    function initializeBasicGalleries() {
        const galleryWidgets = document.querySelectorAll('[data-widget-type="basic-gallery"]');

        galleryWidgets.forEach(function(widget) {
            const enableLightbox = widget.getAttribute('data-lightbox') === 'true';

            if (!enableLightbox) return;

            const items = widget.querySelectorAll('.gallery-item');

            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    const src = this.getAttribute('data-src');
                    if (src) {
                        openLightbox(src);
                    }
                });
            });
        });
    }

    function openLightbox(src) {
        // Create lightbox elements
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; display: flex; align-items: center; justify-content: center; cursor: pointer;';

        const img = document.createElement('img');
        img.src = src;
        img.style.cssText = 'max-width: 90%; max-height: 90%; object-fit: contain;';

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = 'position: absolute; top: 1rem; right: 1rem; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;';

        overlay.appendChild(img);
        overlay.appendChild(closeBtn);
        document.body.appendChild(overlay);

        // Close on click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay || e.target === closeBtn) {
                document.body.removeChild(overlay);
            }
        });

        // Close on ESC
        document.addEventListener('keydown', function escHandler(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(overlay);
                document.removeEventListener('keydown', escHandler);
            }
        });
    }

    /**
     * Form Widget (AJAX submission)
     */
    function initializeForms() {
        const formWidgets = document.querySelectorAll('[data-widget-type="form"]');

        formWidgets.forEach(function(widget) {
            const form = widget.querySelector('form.widget-form');
            const messageEl = widget.querySelector('.form-message');
            const successMessage = widget.getAttribute('data-success-message') || 'Thank you! Your message has been sent.';

            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';

                // Clear previous messages
                if (messageEl) {
                    messageEl.className = 'form-message';
                    messageEl.textContent = '';
                }

                // Get form data
                const formData = new FormData(form);

                // Get page URL for context
                formData.append('page_url', window.location.href);

                // Submit via fetch
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    if (data.success || data.message) {
                        // Success
                        if (messageEl) {
                            messageEl.className = 'form-message success';
                            messageEl.textContent = data.message || successMessage;
                        }
                        form.reset();
                    } else {
                        // Error from server
                        if (messageEl) {
                            messageEl.className = 'form-message error';
                            messageEl.textContent = data.error || 'Something went wrong. Please try again.';
                        }
                    }
                })
                .catch(function(error) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    if (messageEl) {
                        messageEl.className = 'form-message error';
                        messageEl.textContent = 'Network error. Please try again.';
                    }
                    console.error('Form submission error:', error);
                });
            });
        });
    }

})();
