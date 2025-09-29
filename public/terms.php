<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = "Terms of Service";
$pageCSS = "/assets/css/legal.css";
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="legal-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="legal-header text-center mb-5">
                    <h1 class="display-4 fw-bold mb-3">Terms of Service</h1>
                    <p class="lead text-muted">
                        Last updated: <?php echo date('F d, Y'); ?>
                    </p>
                    <div class="header-divider"></div>
                </div>

                <!-- Table of Contents -->
                <div class="toc-card mb-5">
                    <h4><i class="bi bi-list-ul me-2"></i>Table of Contents</h4>
                    <div class="toc-grid">
                        <a href="#acceptance" class="toc-item">1. Acceptance of Terms</a>
                        <a href="#services" class="toc-item">2. Our Services</a>
                        <a href="#membership" class="toc-item">3. Membership & Fees</a>
                        <a href="#facility-rules" class="toc-item">4. Facility Rules</a>
                        <a href="#safety" class="toc-item">5. Health & Safety</a>
                        <a href="#liability" class="toc-item">6. Liability & Risks</a>
                        <a href="#privacy" class="toc-item">7. Privacy & Data</a>
                        <a href="#termination" class="toc-item">8. Termination</a>
                        <a href="#changes" class="toc-item">9. Changes to Terms</a>
                        <a href="#contact" class="toc-item">10. Contact Information</a>
                    </div>
                </div>

                <!-- Terms Content -->
                <div class="legal-content">
                    
                    <!-- Section 1 -->
                    <section id="acceptance" class="legal-section">
                        <h2><span class="section-number">1.</span> Acceptance of Terms</h2>
                        <p>
                            By accessing or using L9 Fitness gym facilities, services, or website, you agree to be bound by these Terms of Service ("Terms"). 
                            If you do not agree to these terms, please do not use our services.
                        </p>
                        <p>
                            These Terms constitute a legally binding agreement between you and L9 Fitness LLC ("we," "us," or "our"). 
                            By signing up for membership or using our facilities, you acknowledge that you have read, understood, and agree to be bound by these Terms.
                        </p>
                    </section>

                    <!-- Section 2 -->
                    <section id="services" class="legal-section">
                        <h2><span class="section-number">2.</span> Our Services</h2>
                        <p>L9 Fitness provides:</p>
                        <ul class="legal-list">
                            <li>Access to gym equipment and facilities during operating hours</li>
                            <li>Group fitness classes led by certified instructors</li>
                            <li>Personal training services (additional fees may apply)</li>
                            <li>Locker room and shower facilities</li>
                            <li>Member support and assistance</li>
                            <li>Online booking system for classes and services</li>
                        </ul>
                        <p>
                            Services may be modified, suspended, or discontinued at our discretion with reasonable notice to members.
                        </p>
                    </section>

                    <!-- Section 3 -->
                    <section id="membership" class="legal-section">
                        <h2><span class="section-number">3.</span> Membership & Fees</h2>
                        
                        <h4>3.1 Membership Types</h4>
                        <p>We offer various membership plans with different durations and benefits. All membership details, including pricing and benefits, are outlined during the signup process.</p>
                        
                        <h4>3.2 Payment Terms</h4>
                        <ul class="legal-list">
                            <li>Membership fees are due in full at the time of purchase</li>
                            <li>All sales are final unless otherwise stated</li>
                            <li>We reserve the right to change membership fees with 30 days' notice</li>
                            <li>Late payment may result in membership suspension</li>
                        </ul>
                        
                        <h4>3.3 Refunds</h4>
                        <p>
                            We offer a 30-day money-back guarantee for new members. Refund requests must be submitted within 30 days of initial membership purchase. 
                            Refunds are processed within 5-10 business days.
                        </p>
                    </section>

                    <!-- Section 4 -->
                    <section id="facility-rules" class="legal-section">
                        <h2><span class="section-number">4.</span> Facility Rules</h2>
                        <p>All members must comply with the following rules:</p>
                        
                        <h4>4.1 General Conduct</h4>
                        <ul class="legal-list">
                            <li>Treat all staff, members, and equipment with respect</li>
                            <li>Follow all posted safety guidelines and instructions</li>
                            <li>Clean equipment after use</li>
                            <li>Wear appropriate workout attire and closed-toe shoes</li>
                            <li>No loud or disruptive behavior</li>
                            <li>No photography or recording without prior permission</li>
                        </ul>
                        
                        <h4>4.2 Equipment Usage</h4>
                        <ul class="legal-list">
                            <li>Use equipment for its intended purpose only</li>
                            <li>Report any damaged or malfunctioning equipment immediately</li>
                            <li>Time limits may apply during peak hours</li>
                            <li>Personal items should not be left unattended</li>
                        </ul>
                        
                        <h4>4.3 Prohibited Items</h4>
                        <ul class="legal-list">
                            <li>Weapons or dangerous items</li>
                            <li>Illegal substances</li>
                            <li>Glass containers in workout areas</li>
                            <li>Personal training equipment without permission</li>
                        </ul>
                    </section>

                    <!-- Section 5 -->
                    <section id="safety" class="legal-section">
                        <h2><span class="section-number">5.</span> Health & Safety</h2>
                        
                        <h4>5.1 Health Requirements</h4>
                        <p>
                            You represent that you are physically capable of participating in fitness activities. 
                            We recommend consulting with a healthcare provider before beginning any exercise program.
                        </p>
                        
                        <h4>5.2 Medical Conditions</h4>
                        <p>
                            You must inform us of any medical conditions, injuries, or limitations that might affect your ability to exercise safely. 
                            We reserve the right to require medical clearance before allowing participation in certain activities.
                        </p>
                        
                        <h4>5.3 Emergency Procedures</h4>
                        <p>
                            In case of emergency, immediately notify staff or call 911. We maintain first aid supplies and trained staff, 
                            but are not responsible for providing medical treatment.
                        </p>
                    </section>

                    <!-- Section 6 -->
                    <section id="liability" class="legal-section">
                        <h2><span class="section-number">6.</span> Liability & Assumption of Risk</h2>
                        
                        <div class="warning-box">
                            <h4><i class="bi bi-exclamation-triangle me-2"></i>Important Legal Notice</h4>
                            <p>
                                <strong>YOU ACKNOWLEDGE AND AGREE THAT PARTICIPATION IN FITNESS ACTIVITIES INVOLVES INHERENT RISKS, INCLUDING BUT NOT LIMITED TO PERSONAL INJURY, PROPERTY DAMAGE, OR DEATH.</strong>
                            </p>
                        </div>
                        
                        <h4>6.1 Assumption of Risk</h4>
                        <p>
                            By using our facilities, you voluntarily assume all risks associated with fitness activities, including but not limited to:
                        </p>
                        <ul class="legal-list">
                            <li>Equipment malfunction or failure</li>
                            <li>Slipping, falling, or collision with objects or people</li>
                            <li>Muscle strains, sprains, or other injuries</li>
                            <li>Heart attack, stroke, or other medical emergencies</li>
                            <li>Acts or omissions of other members or staff</li>
                        </ul>
                        
                        <h4>6.2 Limitation of Liability</h4>
                        <p>
                            TO THE MAXIMUM EXTENT PERMITTED BY LAW, L9 FITNESS SHALL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
                            SPECIAL, OR CONSEQUENTIAL DAMAGES ARISING FROM YOUR USE OF OUR FACILITIES OR SERVICES.
                        </p>
                        
                        <h4>6.3 Indemnification</h4>
                        <p>
                            You agree to indemnify and hold harmless L9 Fitness, its owners, employees, and agents from any claims, 
                            damages, or expenses arising from your use of our facilities.
                        </p>
                    </section>

                    <!-- Section 7 -->
                    <section id="privacy" class="legal-section">
                        <h2><span class="section-number">7.</span> Privacy & Data Protection</h2>
                        <p>
                            Your privacy is important to us. Our collection, use, and protection of your personal information is governed by our 
                            <a href="<?php echo BASE_URL; ?>privacy.php">Privacy Policy</a>, which is incorporated into these Terms by reference.
                        </p>
                        
                        <h4>7.1 Data Collection</h4>
                        <p>We collect information necessary to provide our services, including:</p>
                        <ul class="legal-list">
                            <li>Contact information and emergency contacts</li>
                            <li>Health and fitness information</li>
                            <li>Payment and billing information</li>
                            <li>Facility usage and class attendance</li>
                        </ul>
                        
                        <h4>7.2 Security</h4>
                        <p>
                            We implement reasonable security measures to protect your personal information, but cannot guarantee absolute security.
                        </p>
                    </section>

                    <!-- Section 8 -->
                    <section id="termination" class="legal-section">
                        <h2><span class="section-number">8.</span> Membership Termination</h2>
                        
                        <h4>8.1 Termination by Member</h4>
                        <p>
                            You may cancel your membership according to the terms of your specific membership agreement. 
                            Cancellation requests must be submitted in writing.
                        </p>
                        
                        <h4>8.2 Termination by L9 Fitness</h4>
                        <p>We reserve the right to terminate or suspend membership for:</p>
                        <ul class="legal-list">
                            <li>Violation of facility rules or Terms of Service</li>
                            <li>Non-payment of fees</li>
                            <li>Disruptive or dangerous behavior</li>
                            <li>Providing false information</li>
                            <li>Any reason we deem necessary for the safety and security of our facility</li>
                        </ul>
                        
                        <h4>8.3 Effect of Termination</h4>
                        <p>
                            Upon termination, your access to facilities and services will cease immediately. 
                            No refunds will be provided except as required by law.
                        </p>
                    </section>

                    <!-- Section 9 -->
                    <section id="changes" class="legal-section">
                        <h2><span class="section-number">9.</span> Changes to Terms</h2>
                        <p>
                            We reserve the right to modify these Terms at any time. Material changes will be communicated to members via email 
                            or posted notices at our facility. Continued use of our services after such changes constitutes acceptance of the new Terms.
                        </p>
                        <p>
                            We encourage you to review these Terms periodically to stay informed of any updates.
                        </p>
                    </section>

                    <!-- Section 10 -->
                    <section id="contact" class="legal-section">
                        <h2><span class="section-number">10.</span> Contact Information</h2>
                        <p>If you have questions about these Terms of Service, please contact us:</p>
                        
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="bi bi-building"></i>
                                <div>
                                    <strong>L9 Fitness LLC</strong><br>
                                    123 Fitness Street<br>
                                    New York, NY 10001
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-telephone"></i>
                                <div>
                                    <strong>Phone:</strong><br>
                                    (555) 123-4567
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-envelope"></i>
                                <div>
                                    <strong>Email:</strong><br>
                                    legal@l9fitness.com
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Agreement Section -->
                    <div class="agreement-section">
                        <div class="agreement-box">
                            <h4><i class="bi bi-file-earmark-text me-2"></i>Agreement Acknowledgment</h4>
                            <p>
                                By using L9 Fitness services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service. 
                                These Terms, together with our Privacy Policy, constitute the entire agreement between you and L9 Fitness.
                            </p>
                            <p class="mb-0">
                                <small class="text-muted">
                                    <strong>Effective Date:</strong> <?php echo date('F d, Y'); ?> | 
                                    <strong>Version:</strong> 1.0
                                </small>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Back to Top -->
                <div class="text-center mt-5">
                    <a href="#top" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-up me-2"></i>
                        Back to Top
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for table of contents links
    document.querySelectorAll('.toc-item').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Add highlight effect
                targetElement.classList.add('highlight');
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }
        });
    });

    // Add scroll spy effect
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                document.querySelectorAll('.toc-item').forEach(link => {
                    link.classList.remove('active');
                });
                const activeLink = document.querySelector(`.toc-item[href="#${id}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }
        });
    }, {
        rootMargin: '-20% 0px -80% 0px'
    });

    // Observe all sections
    document.querySelectorAll('.legal-section').forEach(section => {
        observer.observe(section);
    });
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
