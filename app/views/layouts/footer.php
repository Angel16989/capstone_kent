</main>
<!-- Premium L9 Fitness Footer -->
<footer class="l9-premium-footer">
    <div class="footer-background">
        <div class="footer-particles"></div>
        <div class="footer-gradient"></div>
        <div class="footer-sparkles"></div>
    </div>
    
    <div class="footer-content">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-6 text-center text-md-start mb-4 mb-lg-0">
                    <div class="footer-brand">
                        <div class="brand-logo">
                            <i class="fas fa-fire"></i>
                            <span class="brand-text">L9 FITNESS</span>
                        </div>
                        <p class="brand-tagline">Push Your Limits • Beast Mode • 24/7</p>
                        <div class="brand-stats">
                            <div class="mini-stat">
                                <span class="stat-num">500+</span>
                                <span class="stat-label">Members</span>
                            </div>
                            <div class="mini-stat">
                                <span class="stat-num">24/7</span>
                                <span class="stat-label">Access</span>
                            </div>
                            <div class="mini-stat">
                                <span class="stat-num">100%</span>
                                <span class="stat-label">Beast</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 text-center mb-4 mb-lg-0">
                    <div class="footer-links">
                        <h5 class="footer-title">Quick Links</h5>
                        <div class="link-grid">
                            <a href="<?php echo BASE_URL; ?>index.php" class="footer-link">
                                <i class="fas fa-home"></i>
                                <span>Home</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>classes.php" class="footer-link">
                                <i class="fas fa-fire"></i>
                                <span>Classes</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>memberships.php" class="footer-link">
                                <i class="fas fa-crown"></i>
                                <span>Memberships</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>waki.php" class="footer-link" style="border: 2px solid #FFD700; background: linear-gradient(135deg, rgba(255, 68, 68, 0.2), rgba(255, 215, 0, 0.2));">
                                <i class="fas fa-robot"></i>
                                <span>WAKI AI</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>contact.php" class="footer-link">
                                <i class="fas fa-phone"></i>
                                <span>Contact</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>terms.php" class="footer-link">
                                <i class="fas fa-file-contract"></i>
                                <span>Terms</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>privacy.php" class="footer-link">
                                <i class="fas fa-shield-alt"></i>
                                <span>Privacy</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 text-center text-lg-end">
                    <div class="footer-social">
                        <h5 class="footer-title">Join the Beast Community</h5>
                        <div class="social-links">
                            <a href="#" class="social-link facebook">
                                <i class="fab fa-facebook-f"></i>
                                <span class="social-tooltip">Facebook</span>
                            </a>
                            <a href="#" class="social-link instagram">
                                <i class="fab fa-instagram"></i>
                                <span class="social-tooltip">Instagram</span>
                            </a>
                            <a href="#" class="social-link twitter">
                                <i class="fab fa-twitter"></i>
                                <span class="social-tooltip">Twitter</span>
                            </a>
                            <a href="#" class="social-link youtube">
                                <i class="fab fa-youtube"></i>
                                <span class="social-tooltip">YouTube</span>
                            </a>
                            <a href="#" class="social-link tiktok">
                                <i class="fab fa-tiktok"></i>
                                <span class="social-tooltip">TikTok</span>
                            </a>
                        </div>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span>+1 (855) L9-BEAST</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>beast@l9fitness.com</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright mb-1">
                        &copy; <?php echo date('Y'); ?> L9 Fitness. All rights reserved.
                    </p>
                    <p class="beast-mode mb-0">
                        <span class="beast-mode-text">BEAST MODE ACTIVATED</span>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?php echo BASE_URL; ?>admin.php" class="admin-link btn btn-sm btn-outline-light">
                        <i class="fas fa-cog me-1"></i>Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop" title="Back to Top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/page-transitions.js"></script>

<!-- Chatbot Scripts -->
<script>
  // Pass user info to chatbot if logged in
  <?php if (isset($_SESSION['user'])): ?>
  window.userInfo = {
    id: <?php echo $_SESSION['user']['id']; ?>,
    name: "<?php echo htmlspecialchars($_SESSION['user']['name']); ?>",
    email: "<?php echo htmlspecialchars($_SESSION['user']['email']); ?>"
  };
  <?php endif; ?>
</script>
<script src="<?php echo BASE_URL; ?>assets/js/chatbot.js?v=<?php echo time(); ?>"></script>

<!-- Enhanced Footer JavaScript -->
<script>
$(document).ready(function() {
    // Ensure proper scrolling behavior
    $('html').css('scroll-behavior', 'smooth');
    $('body').css('overflow-y', 'auto');
    
    // Scroll to top functionality
    const scrollToTopBtn = $('#scrollToTop');
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 200) {
            scrollToTopBtn.addClass('show');
        } else {
            scrollToTopBtn.removeClass('show');
        }
    });
    
    scrollToTopBtn.click(function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 600, 'swing');
    });
    
    // Ensure footer doesn't block content
    function adjustContentSpacing() {
        const footerHeight = $('.l9-premium-footer').outerHeight();
        $('main, .main-content').css('margin-bottom', '2rem');
    }
    
    // Adjust on load and resize
    adjustContentSpacing();
    $(window).resize(adjustContentSpacing);
    
    // Add floating animation to footer stats
    $('.mini-stat').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.2) + 's'
        });
    });
    
    // Add stagger animation to footer links
    $('.footer-link').each(function(index) {
        $(this).css({
            'animation': 'fadeInUp 0.6s ease forwards',
            'animation-delay': (index * 0.1) + 's',
            'opacity': '0'
        });
    });
    
    // Add stagger animation to social links
    $('.social-link').each(function(index) {
        $(this).css({
            'animation': 'socialPopIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards',
            'animation-delay': (index * 0.15) + 's',
            'opacity': '0',
            'transform': 'scale(0)'
        });
    });
    
    // Dynamic sparkles
    function createSparkle() {
        const sparkle = $('<div class="dynamic-sparkle">✨</div>');
        const footer = $('.l9-premium-footer');
        const x = Math.random() * footer.width();
        const y = Math.random() * footer.height();
        
        sparkle.css({
            position: 'absolute',
            left: x + 'px',
            top: y + 'px',
            fontSize: '0.8rem',
            opacity: '0',
            pointerEvents: 'none',
            zIndex: '3',
            animation: 'sparkleLife 3s ease-out forwards'
        });
        
        footer.append(sparkle);
        
        setTimeout(() => {
            sparkle.remove();
        }, 3000);
    }
    
    // Create sparkles periodically
    setInterval(createSparkle, 2000);
});

// Add CSS animations via JavaScript
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes socialPopIn {
        from {
            opacity: 0;
            transform: scale(0) rotate(-180deg);
        }
        to {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
    }
    
    @keyframes sparkleLife {
        0% {
            opacity: 0;
            transform: scale(0) rotate(0deg);
        }
        50% {
            opacity: 1;
            transform: scale(1.2) rotate(180deg);
        }
        100% {
            opacity: 0;
            transform: scale(0) rotate(360deg);
        }
    }
`;
document.head.appendChild(style);
</script>

<!-- Go to Top Button -->
<button id="goToTop" class="go-to-top-btn" title="Go to Top">
    <i class="fas fa-chevron-up"></i>
</button>

<style>
/* === GO TO TOP BUTTON === */
.go-to-top-btn {
    position: fixed;
    bottom: 20px;
    left: 20px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #FF4444, #FF6666);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 18px;
    cursor: pointer;
    z-index: 99999;
    opacity: 0;
    visibility: hidden;
    transform: scale(0.8);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 
        0 6px 20px rgba(255, 68, 68, 0.4),
        0 3px 10px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.go-to-top-btn.show {
    opacity: 1;
    visibility: visible;
    transform: scale(1);
}

.go-to-top-btn:hover {
    background: linear-gradient(135deg, #FF6666, #FF8888);
    transform: scale(1.1);
    box-shadow: 
        0 8px 25px rgba(255, 68, 68, 0.5),
        0 4px 15px rgba(0, 0, 0, 0.4);
}

.go-to-top-btn:active {
    transform: scale(0.95);
}

/* Responsive Design */
@media (max-width: 768px) {
    .go-to-top-btn {
        bottom: 25px;
        left: 25px;
        width: 45px;
        height: 45px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .go-to-top-btn {
        bottom: 30px;
        left: 30px;
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
}
</style>

<script>
// Go to Top Button Functionality
document.addEventListener('DOMContentLoaded', function() {
    const goToTopBtn = document.getElementById('goToTop');
    
    if (!goToTopBtn) return;
    
    // Show/hide button based on scroll position
    function toggleGoToTopButton() {
        if (window.pageYOffset > 300) {
            goToTopBtn.classList.add('show');
        } else {
            goToTopBtn.classList.remove('show');
        }
    }
    
    // Smooth scroll to top
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Event listeners
    window.addEventListener('scroll', toggleGoToTopButton, { passive: true });
    goToTopBtn.addEventListener('click', scrollToTop);
    
    // Initial check
    toggleGoToTopButton();
    
    console.log('✅ Go to Top button initialized!');
});
</script>

</body>
</html>
