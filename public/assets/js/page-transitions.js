/**
 * ULTRA SMOOTH PAGE TRANSITIONS
 * Advanced page transition system for seamless navigation
 */

class UltraSmoothTransitions {
    constructor() {
        this.isTransitioning = false;
        this.transitionDuration = 600;
        this.init();
    }

    init() {
        this.createTransitionOverlay();
        this.setupEventListeners();
        this.handlePageLoad();
        this.setupImageLoading();
        this.setupSmoothScrolling();
    }

    createTransitionOverlay() {
        if (document.querySelector('.page-transition-overlay')) return;
        
        const overlay = document.createElement('div');
        overlay.className = 'page-transition-overlay';
        overlay.innerHTML = `
            <div class="transition-spinner"></div>
        `;
        document.body.appendChild(overlay);
    }

    setupEventListeners() {
        // Handle all navigation links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            // Skip if it's an external link, has no-transition class, or is a hash link
            if (
                link.classList.contains('no-transition') ||
                link.href.includes('#') ||
                link.target === '_blank' ||
                !link.href.includes(window.location.origin) ||
                link.href === window.location.href
            ) {
                return;
            }

            // Skip if already transitioning
            if (this.isTransitioning) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            this.transitionToPage(link.href);
        });

        // Handle form submissions with transitions
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('no-transition')) return;
            
            this.handleFormTransition(e);
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', (e) => {
            if (!this.isTransitioning) {
                this.transitionToPage(window.location.href, false);
            }
        });
    }

    transitionToPage(url, pushState = true) {
        if (this.isTransitioning) return;
        
        this.isTransitioning = true;
        
        // Add exit animation to current page
        document.body.classList.add('page-exit');
        
        // Show transition overlay
        const overlay = document.querySelector('.page-transition-overlay');
        overlay.classList.add('active');

        // Animate out current content
        this.animateContentOut().then(() => {
            // Navigate to new page
            if (pushState) {
                window.history.pushState({}, '', url);
            }
            window.location.href = url;
        });
    }

    animateContentOut() {
        return new Promise((resolve) => {
            // Stagger animations for different sections
            const sections = document.querySelectorAll('section, .container > *, .hero-section');
            
            sections.forEach((section, index) => {
                setTimeout(() => {
                    section.style.transform = 'translateY(-30px)';
                    section.style.opacity = '0';
                    section.style.filter = 'blur(5px)';
                }, index * 50);
            });

            setTimeout(resolve, this.transitionDuration);
        });
    }

    handlePageLoad() {
        window.addEventListener('load', () => {
            this.initPageAnimations();
            document.body.classList.add('loaded');
            this.isTransitioning = false;
            
            // Hide transition overlay
            const overlay = document.querySelector('.page-transition-overlay');
            setTimeout(() => {
                overlay.classList.remove('active');
            }, 300);
        });

        // Handle DOMContentLoaded for faster transitions
        document.addEventListener('DOMContentLoaded', () => {
            this.initPageAnimations();
        });
    }

    initPageAnimations() {
        // Animate navigation
        this.animateNavigation();
        
        // Animate hero sections
        this.animateHeroSections();
        
        // Animate cards with stagger
        this.animateCardsWithStagger();
        
        // Animate forms
        this.animateForms();
    }

    animateNavigation() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                navbar.style.transition = 'transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                navbar.style.transform = 'translateY(0)';
            }, 200);
        }
    }

    animateHeroSections() {
        const heroes = document.querySelectorAll('.hero-section, .membership-hero, .classes-hero, .contact-hero');
        heroes.forEach((hero, index) => {
            hero.style.opacity = '0';
            hero.style.transform = 'translateY(40px) scale(0.95)';
            hero.style.filter = 'blur(10px)';
            
            setTimeout(() => {
                hero.style.transition = 'all 1.2s cubic-bezier(0.16, 1, 0.3, 1)';
                hero.style.opacity = '1';
                hero.style.transform = 'translateY(0) scale(1)';
                hero.style.filter = 'blur(0)';
            }, 300 + (index * 100));
        });
    }

    animateCardsWithStagger() {
        const cards = document.querySelectorAll('.card, .membership-card, .class-card, .category-card, .benefit-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px) scale(0.95)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
            }, 400 + (index * 100));
        });
    }

    animateForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach((form, index) => {
            const inputs = form.querySelectorAll('.form-control, .form-select, input, textarea');
            inputs.forEach((input, inputIndex) => {
                input.style.opacity = '0';
                input.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    input.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    input.style.opacity = '1';
                    input.style.transform = 'translateX(0)';
                }, 600 + (inputIndex * 80));
            });
        });
    }

    handleFormTransition(e) {
        const form = e.target;
        const submitBtn = form.querySelector('[type="submit"], button[type="submit"]');
        
        if (submitBtn) {
            submitBtn.style.transform = 'scale(0.95)';
            submitBtn.style.opacity = '0.7';
            
            setTimeout(() => {
                submitBtn.style.transform = 'scale(1)';
                submitBtn.style.opacity = '1';
            }, 200);
        }

        // Add loading state to form
        form.style.opacity = '0.8';
        form.style.transform = 'scale(0.98)';
        
        setTimeout(() => {
            form.style.opacity = '1';
            form.style.transform = 'scale(1)';
        }, 300);
    }

    setupImageLoading() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (img.complete) {
                img.classList.add('loaded');
            } else {
                img.addEventListener('load', () => {
                    img.classList.add('loaded');
                });
            }
        });
    }

    setupSmoothScrolling() {
        // Enhanced smooth scrolling for internal links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]');
            if (!link) return;

            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                this.smoothScrollToElement(targetElement);
            }
        });
    }

    smoothScrollToElement(element) {
        const targetPosition = element.offsetTop - 80; // Account for fixed navbar
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        const duration = 1000;
        let start = null;

        function animation(currentTime) {
            if (start === null) start = currentTime;
            const timeElapsed = currentTime - start;
            const run = easeInOutQuart(timeElapsed, startPosition, distance, duration);
            window.scrollTo(0, run);
            if (timeElapsed < duration) requestAnimationFrame(animation);
        }

        function easeInOutQuart(t, b, c, d) {
            t /= d / 2;
            if (t < 1) return c / 2 * t * t * t * t + b;
            t -= 2;
            return -c / 2 * (t * t * t * t - 2) + b;
        }

        requestAnimationFrame(animation);
    }

    // Public method to trigger page transition
    navigateTo(url) {
        this.transitionToPage(url);
    }

    // Public method to disable transitions temporarily
    disableTransitions() {
        document.body.classList.add('no-transitions');
    }

    // Public method to re-enable transitions
    enableTransitions() {
        document.body.classList.remove('no-transitions');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.ultraSmoothTransitions = new UltraSmoothTransitions();
});

// Handle page show event (for browser back button)
window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
        document.body.classList.add('loaded');
        const overlay = document.querySelector('.page-transition-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UltraSmoothTransitions;
}
