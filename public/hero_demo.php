<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L9 FITNESS - EPIC HERO DEMO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/hero-enhanced.css" rel="stylesheet">
    <style>
        body {
            background: #0a0a0a;
            color: white;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
        }
        
        .btn-hero {
            padding: 15px 30px;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            border: 2px solid #FF4444;
            background: transparent;
            color: #FF4444;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        
        .btn-hero:hover {
            background: #FF4444;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 68, 68, 0.4);
        }
        
        .btn-hero.btn-primary {
            background: #FF4444;
            color: white;
        }
        
        .btn-hero.btn-primary:hover {
            background: #FF6666;
            transform: translateY(-3px) scale(1.05);
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #FF4444;
            text-shadow: 0 0 20px rgba(255, 68, 68, 0.5);
        }
        
        .stat-label {
            font-size: 1rem;
            color: #ccc;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .hero-badge {
            background: rgba(255, 68, 68, 0.2);
            border: 2px solid #FF4444;
            border-radius: 50px;
            padding: 10px 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            color: #FF4444;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 1s ease-out;
        }
        
        .animate-slideInRight {
            animation: slideInRight 1s ease-out;
        }
        
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
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .min-vh-75 {
            min-height: 75vh;
        }
        
        .demo-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            z-index: 1000;
            border-bottom: 2px solid #FF4444;
        }
        
        .demo-nav {
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <!-- Demo Header -->
    <div class="demo-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-0">🔥 <span class="text-gradient">L9 FITNESS</span> - EPIC HERO DEMO</h3>
                </div>
                <div class="col-md-6 text-end">
                    <a href="http://localhost/Capstone-latest/public/" class="btn-hero btn-primary">
                        <i class="bi bi-house-fill"></i> FULL SITE
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Amazing Hero Section with Epic Visual -->
    <div class="hero-section demo-nav">
        <div class="container py-5">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <div class="hero-content animate-fadeInUp">
                        <div class="hero-badge mb-4">
                            <svg width="24" height="24" fill="currentColor" class="bi bi-lightning-charge-fill">
                                <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
                            </svg>
                            Beast Mode • 24/7 Access
                        </div>
                        
                        <h1 class="display-1 fw-bold mb-4">
                            UNLEASH <br>
                            <span class="text-gradient">THE BEAST</span>
                        </h1>
                        
                        <p class="lead mb-5" style="font-size: 1.4rem;">
                            🔥 <strong>NO EXCUSES. NO SHORTCUTS. JUST PURE GRIND.</strong><br>
                            Step into L9 Fitness and transform your body with hardcore training, iron discipline, and relentless dedication. This is where champions are forged.
                        </p>
                        
                        <div class="hero-buttons">
                            <a class="btn-hero btn-primary" href="memberships.php">
                                <span>🚀 START TRAINING</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a class="btn-hero" href="classes.php">
                                <span>💪 BEAST CLASSES</span>
                                <i class="bi bi-play-circle"></i>
                            </a>
                        </div>
                        
                        <div class="hero-stats mt-5">
                            <div class="row g-4">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number">500+</div>
                                        <div class="stat-label">Warriors</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number">50+</div>
                                        <div class="stat-label">Beast Classes</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number">15+</div>
                                        <div class="stat-label">Elite Trainers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="hero-image animate-slideInRight">
                        <div class="hero-visual">
                            <!-- Epic Geometric Hero Design with Animated Athlete Silhouette -->
                            <div class="hero-shape hero-shape-1"></div>
                            <div class="hero-shape hero-shape-2"></div>
                            <div class="hero-shape hero-shape-3"></div>
                            
                            <!-- Epic Floating Cards -->
                            <div class="floating-card floating-card-1">
                                <i class="bi bi-heart-pulse-fill"></i>
                                <span>Heart Rate Training</span>
                            </div>
                            <div class="floating-card floating-card-2">
                                <i class="bi bi-trophy-fill"></i>
                                <span>Personal Records</span>
                            </div>
                            <div class="floating-card floating-card-3">
                                <i class="bi bi-people-fill"></i>
                                <span>Beast Squad</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Info -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div style="background: rgba(255, 68, 68, 0.1); border: 2px solid #FF4444; border-radius: 15px; padding: 30px;">
                    <h2 class="mb-4">🎨 <span class="text-gradient">EPIC HERO IMAGE</span> FEATURES</h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5>🔥 Animated Athlete Silhouette</h5>
                            <p>Dynamic CSS-created fitness figure with floating animation</p>
                        </div>
                        <div class="col-md-6">
                            <h5>💪 Glowing Barbell Effect</h5>
                            <p>Animated barbell with pulsing glow effects</p>
                        </div>
                        <div class="col-md-6">
                            <h5>🌟 Floating UI Cards</h5>
                            <p>Interactive floating elements with fitness metrics</p>
                        </div>
                        <div class="col-md-6">
                            <h5>⚡ Geometric Patterns</h5>
                            <p>Modern background with grid overlays and accent shapes</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="http://localhost/Capstone-latest/public/memberships.php" class="btn-hero btn-primary">
                            <span>🚀 SEE MEMBERSHIPS</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="http://localhost/Capstone-latest/public/status_check.php" class="btn-hero">
                            <span>🔧 STATUS CHECK</span>
                            <i class="bi bi-gear-fill"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>