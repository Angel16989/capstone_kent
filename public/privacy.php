<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = "Privacy Policy";
$pageCSS = "/assets/css/legal.css";
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="legal-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="legal-header text-center mb-5">
                    <h1 class="display-4 fw-bold mb-3">Privacy Policy</h1>
                    <p class="lead text-muted">
                        Last updated: <?php echo date('F d, Y'); ?>
                    </p>
                    <div class="header-divider"></div>
                </div>

                <!-- Table of Contents -->
                <div class="toc-card mb-5">
                    <h4><i class="bi bi-list-ul me-2"></i>Table of Contents</h4>
                    <div class="toc-grid">
                        <a href="#introduction" class="toc-item">1. Introduction</a>
                        <a href="#information-we-collect" class="toc-item">2. Information We Collect</a>
                        <a href="#how-we-use" class="toc-item">3. How We Use Information</a>
                        <a href="#information-sharing" class="toc-item">4. Information Sharing</a>
                        <a href="#data-security" class="toc-item">5. Data Security</a>
                        <a href="#data-retention" class="toc-item">6. Data Retention</a>
                        <a href="#your-rights" class="toc-item">7. Your Rights</a>
                        <a href="#cookies" class="toc-item">8. Cookies & Tracking</a>
                        <a href="#third-party" class="toc-item">9. Third-Party Services</a>
                        <a href="#changes" class="toc-item">10. Policy Changes</a>
                        <a href="#contact" class="toc-item">11. Contact Us</a>
                    </div>
                </div>

                <!-- Privacy Policy Content -->
                <div class="legal-content">
                    
                    <!-- Section 1 -->
                    <section id="introduction" class="legal-section">
                        <h2><span class="section-number">1.</span> Introduction</h2>
                        <p>
                            At L9 Fitness ("we," "our," or "us"), we are committed to protecting your privacy and handling your personal information with care. 
                            This Privacy Policy explains how we collect, use, share, and protect your information when you use our gym facilities, 
                            services, and website.
                        </p>
                        <p>
                            By using our services, you consent to the collection, use, and sharing of your information as described in this Privacy Policy. 
                            If you do not agree with our policies and practices, please do not use our services.
                        </p>
                        
                        <div class="info-box">
                            <h5><i class="bi bi-info-circle me-2"></i>Key Points</h5>
                            <ul class="legal-list">
                                <li>We collect information to provide and improve our services</li>
                                <li>We do not sell your personal information to third parties</li>
                                <li>We implement security measures to protect your data</li>
                                <li>You have rights regarding your personal information</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 2 -->
                    <section id="information-we-collect" class="legal-section">
                        <h2><span class="section-number">2.</span> Information We Collect</h2>
                        
                        <h4>2.1 Information You Provide</h4>
                        <p>We collect information that you voluntarily provide to us, including:</p>
                        <ul class="legal-list">
                            <li><strong>Account Information:</strong> Name, email address, phone number, date of birth, gender</li>
                            <li><strong>Billing Information:</strong> Credit card details, billing address (processed securely by our payment providers)</li>
                            <li><strong>Health Information:</strong> Medical conditions, injuries, fitness goals, emergency contact information</li>
                            <li><strong>Profile Information:</strong> Preferences, interests, and any additional information you choose to share</li>
                            <li><strong>Communication:</strong> Messages you send us, feedback, and support requests</li>
                        </ul>
                        
                        <h4>2.2 Information We Collect Automatically</h4>
                        <p>When you use our services, we may automatically collect:</p>
                        <ul class="legal-list">
                            <li><strong>Usage Information:</strong> Facility check-ins, class attendance, equipment usage</li>
                            <li><strong>Website Analytics:</strong> IP address, browser type, pages visited, time spent on site</li>
                            <li><strong>Device Information:</strong> Device type, operating system, unique device identifiers</li>
                            <li><strong>Location Information:</strong> General location when using our mobile app (if enabled)</li>
                        </ul>
                        
                        <h4>2.3 Information from Third Parties</h4>
                        <p>We may receive information from:</p>
                        <ul class="legal-list">
                            <li>Payment processors for transaction verification</li>
                            <li>Marketing partners (only with your consent)</li>
                            <li>Social media platforms if you connect your accounts</li>
                            <li>Healthcare providers (only with your explicit consent)</li>
                        </ul>
                    </section>

                    <!-- Section 3 -->
                    <section id="how-we-use" class="legal-section">
                        <h2><span class="section-number">3.</span> How We Use Your Information</h2>
                        <p>We use your information for the following purposes:</p>
                        
                        <h4>3.1 Service Provision</h4>
                        <ul class="legal-list">
                            <li>Process membership registrations and payments</li>
                            <li>Provide access to facilities and book classes</li>
                            <li>Deliver personalized fitness recommendations</li>
                            <li>Maintain your account and preferences</li>
                            <li>Provide customer support and respond to inquiries</li>
                        </ul>
                        
                        <h4>3.2 Communication</h4>
                        <ul class="legal-list">
                            <li>Send important account and service updates</li>
                            <li>Notify you about class schedules and changes</li>
                            <li>Share promotional offers and newsletters (with your consent)</li>
                            <li>Conduct surveys and gather feedback</li>
                        </ul>
                        
                        <h4>3.3 Safety and Security</h4>
                        <ul class="legal-list">
                            <li>Ensure facility safety and emergency response</li>
                            <li>Prevent fraud and unauthorized access</li>
                            <li>Monitor compliance with facility rules</li>
                            <li>Investigate and resolve disputes</li>
                        </ul>
                        
                        <h4>3.4 Business Operations</h4>
                        <ul class="legal-list">
                            <li>Analyze usage patterns to improve our services</li>
                            <li>Conduct research and development</li>
                            <li>Comply with legal and regulatory requirements</li>
                            <li>Manage business operations and administration</li>
                        </ul>
                    </section>

                    <!-- Section 4 -->
                    <section id="information-sharing" class="legal-section">
                        <h2><span class="section-number">4.</span> Information Sharing</h2>
                        <p>We do not sell your personal information. We may share your information in the following circumstances:</p>
                        
                        <h4>4.1 With Your Consent</h4>
                        <p>We may share information when you explicitly consent to such sharing.</p>
                        
                        <h4>4.2 Service Providers</h4>
                        <p>We share information with trusted third-party service providers who help us operate our business:</p>
                        <ul class="legal-list">
                            <li>Payment processors for billing and transactions</li>
                            <li>Cloud storage providers for data hosting</li>
                            <li>Email and communication service providers</li>
                            <li>Analytics and marketing platforms</li>
                            <li>IT support and security services</li>
                        </ul>
                        
                        <h4>4.3 Legal Requirements</h4>
                        <p>We may disclose information when required by law or to:</p>
                        <ul class="legal-list">
                            <li>Comply with legal processes or government requests</li>
                            <li>Protect our rights, property, or safety</li>
                            <li>Protect the rights, property, or safety of our users</li>
                            <li>Prevent or investigate possible wrongdoing</li>
                        </ul>
                        
                        <h4>4.4 Business Transfers</h4>
                        <p>
                            If we are involved in a merger, acquisition, or sale of assets, your information may be transferred. 
                            We will provide notice before your information becomes subject to a different privacy policy.
                        </p>
                        
                        <h4>4.5 Emergency Situations</h4>
                        <p>
                            We may share health information with emergency responders or healthcare providers in case of medical emergencies.
                        </p>
                    </section>

                    <!-- Section 5 -->
                    <section id="data-security" class="legal-section">
                        <h2><span class="section-number">5.</span> Data Security</h2>
                        <p>We implement comprehensive security measures to protect your personal information:</p>
                        
                        <h4>5.1 Technical Safeguards</h4>
                        <ul class="legal-list">
                            <li>SSL/TLS encryption for data transmission</li>
                            <li>Encrypted storage of sensitive information</li>
                            <li>Regular security audits and assessments</li>
                            <li>Access controls and authentication systems</li>
                            <li>Firewall protection and intrusion detection</li>
                        </ul>
                        
                        <h4>5.2 Administrative Safeguards</h4>
                        <ul class="legal-list">
                            <li>Limited access to personal information on a need-to-know basis</li>
                            <li>Employee training on privacy and security practices</li>
                            <li>Background checks for employees with data access</li>
                            <li>Regular review and update of security policies</li>
                        </ul>
                        
                        <h4>5.3 Physical Safeguards</h4>
                        <ul class="legal-list">
                            <li>Secure facilities with controlled access</li>
                            <li>Protected computer systems and servers</li>
                            <li>Secure disposal of sensitive documents</li>
                        </ul>
                        
                        <div class="warning-box">
                            <h5><i class="bi bi-shield-exclamation me-2"></i>Security Limitations</h5>
                            <p>
                                While we implement strong security measures, no system is completely secure. We cannot guarantee absolute security of your information. 
                                You can help protect your account by using strong passwords and keeping your login credentials confidential.
                            </p>
                        </div>
                    </section>

                    <!-- Section 6 -->
                    <section id="data-retention" class="legal-section">
                        <h2><span class="section-number">6.</span> Data Retention</h2>
                        <p>We retain your personal information for as long as necessary to:</p>
                        <ul class="legal-list">
                            <li>Provide our services and maintain your account</li>
                            <li>Comply with legal, tax, and regulatory requirements</li>
                            <li>Resolve disputes and enforce our agreements</li>
                            <li>Prevent fraud and maintain security</li>
                        </ul>
                        
                        <h4>6.1 Retention Periods</h4>
                        <ul class="legal-list">
                            <li><strong>Active Members:</strong> Information retained while membership is active</li>
                            <li><strong>Former Members:</strong> Information retained for 7 years after membership ends</li>
                            <li><strong>Marketing Data:</strong> Retained until you opt out or request deletion</li>
                            <li><strong>Financial Records:</strong> Retained for 7 years for tax and legal purposes</li>
                            <li><strong>Health Information:</strong> Retained for 10 years or as required by law</li>
                        </ul>
                        
                        <h4>6.2 Data Deletion</h4>
                        <p>
                            When we no longer need your information, we securely delete or anonymize it. 
                            Some information may be retained in anonymized form for research and analytics purposes.
                        </p>
                    </section>

                    <!-- Section 7 -->
                    <section id="your-rights" class="legal-section">
                        <h2><span class="section-number">7.</span> Your Privacy Rights</h2>
                        <p>You have several rights regarding your personal information:</p>
                        
                        <h4>7.1 Access and Portability</h4>
                        <ul class="legal-list">
                            <li>Request access to your personal information</li>
                            <li>Receive a copy of your data in a portable format</li>
                            <li>View and download your account information</li>
                        </ul>
                        
                        <h4>7.2 Correction and Updates</h4>
                        <ul class="legal-list">
                            <li>Correct inaccurate or incomplete information</li>
                            <li>Update your profile and preferences</li>
                            <li>Modify your communication preferences</li>
                        </ul>
                        
                        <h4>7.3 Deletion</h4>
                        <ul class="legal-list">
                            <li>Request deletion of your personal information</li>
                            <li>Close your account and remove data</li>
                            <li>Note: Some information may be retained for legal compliance</li>
                        </ul>
                        
                        <h4>7.4 Opt-Out Rights</h4>
                        <ul class="legal-list">
                            <li>Unsubscribe from marketing communications</li>
                            <li>Opt out of certain data processing activities</li>
                            <li>Disable location tracking</li>
                        </ul>
                        
                        <h4>7.5 Exercising Your Rights</h4>
                        <p>To exercise any of these rights, please:</p>
                        <ul class="legal-list">
                            <li>Log in to your account and update your preferences</li>
                            <li>Contact us at privacy@l9fitness.com</li>
                            <li>Visit our facility and speak with management</li>
                            <li>Call us at (555) 123-4567</li>
                        </ul>
                    </section>

                    <!-- Section 8 -->
                    <section id="cookies" class="legal-section">
                        <h2><span class="section-number">8.</span> Cookies & Tracking Technologies</h2>
                        <p>We use cookies and similar technologies to enhance your experience:</p>
                        
                        <h4>8.1 Types of Cookies</h4>
                        <ul class="legal-list">
                            <li><strong>Essential Cookies:</strong> Required for website functionality</li>
                            <li><strong>Analytics Cookies:</strong> Help us understand how you use our site</li>
                            <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                            <li><strong>Marketing Cookies:</strong> Used to deliver relevant advertisements</li>
                        </ul>
                        
                        <h4>8.2 Cookie Management</h4>
                        <p>You can control cookies through your browser settings:</p>
                        <ul class="legal-list">
                            <li>Accept or reject all cookies</li>
                            <li>Delete existing cookies</li>
                            <li>Set preferences for different types of cookies</li>
                            <li>Receive notifications when cookies are set</li>
                        </ul>
                        
                        <h4>8.3 Other Tracking Technologies</h4>
                        <p>We may also use:</p>
                        <ul class="legal-list">
                            <li>Web beacons and pixel tags</li>
                            <li>Local storage objects</li>
                            <li>Mobile device identifiers</li>
                            <li>Analytics and measurement tools</li>
                        </ul>
                    </section>

                    <!-- Section 9 -->
                    <section id="third-party" class="legal-section">
                        <h2><span class="section-number">9.</span> Third-Party Services</h2>
                        <p>Our services may integrate with third-party platforms and services:</p>
                        
                        <h4>9.1 Payment Processors</h4>
                        <p>
                            We use secure third-party payment processors (such as Stripe, PayPal) to handle financial transactions. 
                            These services have their own privacy policies and security measures.
                        </p>
                        
                        <h4>9.2 Social Media Integration</h4>
                        <p>
                            If you connect your social media accounts, we may receive information according to your privacy settings 
                            on those platforms.
                        </p>
                        
                        <h4>9.3 Analytics Services</h4>
                        <p>
                            We use analytics services like Google Analytics to understand website usage. 
                            These services may collect information about your online activities across different websites.
                        </p>
                        
                        <h4>9.4 Marketing Platforms</h4>
                        <p>
                            We may use third-party marketing platforms to deliver targeted advertisements and communications. 
                            You can opt out of these services through their respective privacy controls.
                        </p>
                    </section>

                    <!-- Section 10 -->
                    <section id="changes" class="legal-section">
                        <h2><span class="section-number">10.</span> Changes to This Privacy Policy</h2>
                        <p>
                            We may update this Privacy Policy from time to time to reflect changes in our practices, 
                            services, or legal requirements. We will notify you of material changes by:
                        </p>
                        <ul class="legal-list">
                            <li>Sending an email to your registered email address</li>
                            <li>Posting a notice on our website</li>
                            <li>Providing notification in our mobile app</li>
                            <li>Posting notices at our facility</li>
                        </ul>
                        <p>
                            We encourage you to review this Privacy Policy periodically. 
                            Your continued use of our services after changes become effective constitutes acceptance of the updated policy.
                        </p>
                    </section>

                    <!-- Section 11 -->
                    <section id="contact" class="legal-section">
                        <h2><span class="section-number">11.</span> Contact Information</h2>
                        <p>If you have questions about this Privacy Policy or our privacy practices, please contact us:</p>
                        
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="bi bi-building"></i>
                                <div>
                                    <strong>L9 Fitness LLC</strong><br>
                                    Privacy Officer<br>
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
                                    privacy@l9fitness.com
                                </div>
                            </div>
                        </div>
                        
                        <p>
                            We will respond to your inquiry within 30 days. For urgent privacy concerns, 
                            please call us directly at (555) 123-4567.
                        </p>
                    </section>

                    <!-- Compliance Section -->
                    <div class="compliance-section">
                        <div class="compliance-box">
                            <h4><i class="bi bi-shield-check me-2"></i>Privacy Compliance</h4>
                            <p>
                                This Privacy Policy is designed to comply with applicable privacy laws and regulations, 
                                including GDPR, CCPA, and other relevant data protection requirements. 
                                We are committed to maintaining the highest standards of privacy protection.
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
    // Same functionality as terms.php for smooth scrolling and highlighting
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
                
                targetElement.classList.add('highlight');
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }
        });
    });

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

    document.querySelectorAll('.legal-section').forEach(section => {
        observer.observe(section);
    });
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
