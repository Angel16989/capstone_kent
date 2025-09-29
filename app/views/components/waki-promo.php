<!-- WAKI AI Assistant Promotion Banner -->
<div class="waki-promo-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-7">
                <div class="waki-promo-content">
                    <div class="waki-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="waki-text">
                        <h4>Meet WAKI - Your AI Beast Assistant</h4>
                        <p>Get instant answers about workouts, nutrition, classes, and more! WAKI is available 24/7 to help you crush your fitness goals.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-5 text-end">
                <a href="<?php echo BASE_URL; ?>waki.php" class="waki-promo-btn">
                    <i class="fas fa-comments me-2"></i>
                    Chat with WAKI
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.waki-promo-banner {
    background: linear-gradient(135deg, rgba(255, 68, 68, 0.1), rgba(255, 215, 0, 0.1));
    border: 2px solid rgba(255, 68, 68, 0.3);
    border-radius: 20px;
    padding: 1.5rem;
    margin: 2rem 0;
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.waki-promo-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.1), transparent);
    animation: wakiPromoScan 3s ease-in-out infinite;
}

.waki-promo-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.waki-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #FF4444, #FFD700);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
    animation: wakiPromoPulse 2s ease-in-out infinite;
}

.waki-text h4 {
    color: #FFD700;
    margin: 0 0 0.5rem 0;
    font-weight: 700;
}

.waki-text p {
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    font-size: 0.95rem;
}

.waki-promo-btn {
    background: linear-gradient(135deg, #FF4444, #FFD700);
    color: white;
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 8px 20px rgba(255, 68, 68, 0.3);
}

.waki-promo-btn:hover {
    color: white;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 30px rgba(255, 68, 68, 0.5);
    background: linear-gradient(135deg, #FF6666, #FFE135);
}

@keyframes wakiPromoPulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 0 20px rgba(255, 68, 68, 0.3);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
    }
}

@keyframes wakiPromoScan {
    0% { left: -100%; }
    100% { left: 100%; }
}

@media (max-width: 768px) {
    .waki-promo-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
        margin-bottom: 1rem;
    }
    
    .waki-promo-banner .col-lg-4 {
        text-align: center !important;
    }
    
    .waki-promo-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>