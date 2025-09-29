<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$pageTitle = "Contact Us";
$pageCSS = ["/assets/css/contact.css", "/assets/css/chatbot.css"];

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $contact_method = $_POST['contact_method'] ?? 'email';

        // Validation
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
        if (!email_valid($email)) {
            $errors['email'] = 'Valid email address is required';
        }
        if (empty($subject)) {
            $errors['subject'] = 'Subject is required';
        }
        if (empty($message)) {
            $errors['message'] = 'Message is required';
        }
        if (strlen($message) < 10) {
            $errors['message'] = 'Message must be at least 10 characters long';
        }

        if (empty($errors)) {
            // Create contact_messages table if it doesn't exist
            try {
                $pdo->exec('CREATE TABLE IF NOT EXISTS contact_messages (
                    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(191) NOT NULL,
                    phone VARCHAR(30),
                    subject VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    contact_method ENUM("email", "phone", "both") DEFAULT "email",
                    status ENUM("new", "in_progress", "resolved") DEFAULT "new",
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');
            } catch (PDOException $e) {
                // Table might already exist
            }

            // Insert contact message
            $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, contact_method) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $phone, $subject, $message, $contact_method]);

            // In a real application, you would send an email notification to staff
            $success = 'Thank you for contacting us! We will get back to you within 24 hours.';
            
            // Clear form data on success
            $_POST = [];
        }

    } catch (Exception $e) {
        $errors['general'] = 'An error occurred while sending your message. Please try again.';
    }
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="contact-container">
    <!-- Hero Section -->
    <div class="contact-hero">
        <div class="container text-center py-5">
            <h1 class="display-4 fw-bold mb-3">Get In Touch</h1>
            <p class="lead mb-4">
                Have questions? Need support? We're here to help you on your fitness journey.
            </p>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-card">
                    <div class="form-header">
                        <h2><i class="bi bi-chat-dots me-2"></i>Send us a Message</h2>
                        <p class="text-muted">Fill out the form below and we'll get back to you as soon as possible.</p>
                    </div>

                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($success); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Message -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <!-- Name and Email Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                   placeholder="+1 (555) 123-4567">
                        </div>

                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                                    id="subject" 
                                    name="subject" 
                                    required>
                                <option value="">Select a topic</option>
                                <option value="membership" <?php echo ($_POST['subject'] ?? '') === 'membership' ? 'selected' : ''; ?>>
                                    Membership Questions
                                </option>
                                <option value="billing" <?php echo ($_POST['subject'] ?? '') === 'billing' ? 'selected' : ''; ?>>
                                    Billing & Payments
                                </option>
                                <option value="classes" <?php echo ($_POST['subject'] ?? '') === 'classes' ? 'selected' : ''; ?>>
                                    Class Schedules & Booking
                                </option>
                                <option value="facilities" <?php echo ($_POST['subject'] ?? '') === 'facilities' ? 'selected' : ''; ?>>
                                    Facilities & Equipment
                                </option>
                                <option value="personal-training" <?php echo ($_POST['subject'] ?? '') === 'personal-training' ? 'selected' : ''; ?>>
                                    Personal Training
                                </option>
                                <option value="technical" <?php echo ($_POST['subject'] ?? '') === 'technical' ? 'selected' : ''; ?>>
                                    Technical Support
                                </option>
                                <option value="feedback" <?php echo ($_POST['subject'] ?? '') === 'feedback' ? 'selected' : ''; ?>>
                                    Feedback & Suggestions
                                </option>
                                <option value="other" <?php echo ($_POST['subject'] ?? '') === 'other' ? 'selected' : ''; ?>>
                                    Other
                                </option>
                            </select>
                            <?php if (isset($errors['subject'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['subject']); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                      id="message" 
                                      name="message" 
                                      rows="6" 
                                      placeholder="Please provide details about your inquiry..." 
                                      required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span> / 1000 characters
                            </div>
                            <?php if (isset($errors['message'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['message']); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Contact Preference -->
                        <div class="mb-4">
                            <label class="form-label">Preferred Contact Method</label>
                            <div class="contact-methods">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contact_method" id="method_email" value="email" 
                                           <?php echo ($_POST['contact_method'] ?? 'email') === 'email' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="method_email">
                                        <i class="bi bi-envelope me-2"></i>Email
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contact_method" id="method_phone" value="phone"
                                           <?php echo ($_POST['contact_method'] ?? '') === 'phone' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="method_phone">
                                        <i class="bi bi-telephone me-2"></i>Phone Call
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="contact_method" id="method_both" value="both"
                                           <?php echo ($_POST['contact_method'] ?? '') === 'both' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="method_both">
                                        <i class="bi bi-chat-square me-2"></i>Either Method
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                            <i class="bi bi-send me-2"></i>
                            <span class="btn-text">Send Message</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-4">
                <div class="contact-info-card">
                    <h3><i class="bi bi-info-circle me-2"></i>Contact Information</h3>
                    
                    <div class="contact-items">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Address</h5>
                                <p>123 Fitness Street<br>New York, NY 10001<br>United States</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Phone</h5>
                                <p>
                                    <a href="tel:+15551234567">(555) 123-4567</a><br>
                                    <small class="text-muted">Mon-Fri: 6AM-10PM<br>Weekends: 7AM-9PM</small>
                                </p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Email</h5>
                                <p>
                                    <a href="mailto:info@l9fitness.com">info@l9fitness.com</a><br>
                                    <small class="text-muted">We respond within 24 hours</small>
                                </p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Hours</h5>
                                <p>
                                    <strong>Mon-Fri:</strong> 5:00 AM - 11:00 PM<br>
                                    <strong>Saturday:</strong> 6:00 AM - 10:00 PM<br>
                                    <strong>Sunday:</strong> 7:00 AM - 9:00 PM<br>
                                    <small class="text-muted">Holiday hours may vary</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="social-section">
                        <h5>Follow Us</h5>
                        <div class="social-links">
                            <a href="#" class="social-link facebook" title="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-link instagram" title="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-link twitter" title="Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="social-link youtube" title="YouTube">
                                <i class="bi bi-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="emergency-section">
                        <div class="emergency-box">
                            <h6><i class="bi bi-exclamation-triangle-fill me-2"></i>Emergency</h6>
                            <p>For medical emergencies during gym hours, immediately notify staff or call 911.</p>
                            <p><strong>After Hours Emergency:</strong> <a href="tel:+15551234567">(555) 123-4567</a></p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="faq-section mt-4">
                    <h4><i class="bi bi-question-circle me-2"></i>Quick Answers</h4>
                    <div class="accordion" id="contactFAQ">
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How quickly will I get a response?
                                </button>
                            </h6>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                                <div class="accordion-body">
                                    We aim to respond to all inquiries within 24 hours during business days. Urgent matters are typically handled within 2-4 hours.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I schedule a tour before joining?
                                </button>
                            </h6>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                                <div class="accordion-body">
                                    Absolutely! We offer free facility tours. Call us at (555) 123-4567 or mention "tour request" in your message above.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you offer corporate memberships?
                                </button>
                            </h6>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                                <div class="accordion-body">
                                    Yes! We have special corporate rates and group packages. Please contact us for a custom quote for your organization.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    const submitBtn = document.getElementById('submitBtn');
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');

    // Character counter for message
    if (messageTextarea && charCount) {
        function updateCharCount() {
            const length = messageTextarea.value.length;
            charCount.textContent = length;
            
            if (length > 1000) {
                charCount.style.color = '#dc3545';
                messageTextarea.value = messageTextarea.value.substring(0, 1000);
                charCount.textContent = '1000';
            } else if (length > 900) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = '#6c757d';
            }
        }

        messageTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count
    }

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `+1 (${value}`;
                } else if (value.length <= 6) {
                    value = `+1 (${value.slice(0, 3)}) ${value.slice(3)}`;
                } else {
                    value = `+1 (${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                }
            }
            e.target.value = value;
        });
    }

    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-text').textContent = 'Sending...';
                submitBtn.querySelector('.spinner-border').classList.remove('d-none');
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-resize message textarea
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
