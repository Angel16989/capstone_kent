<?php
require_once __DIR__ . '/../config/config.php';

echo "<h1>🎯 L9 Fitness Login Testing Center</h1>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L9 Fitness Login Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            transition: border-color 0.3s;
        }
        .test-card:hover {
            border-color: #007bff;
        }
        .credential-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        
        <!-- Regular Login Test -->
        <div class="test-card">
            <h3>🔐 Regular Login Test</h3>
            <p>Test the standard email/password login system:</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Admin Account:</h5>
                    <div class="credential-box">
                        Email: admin@l9fitness.com<br>
                        Password: password123
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Trainer Account:</h5>
                    <div class="credential-box">
                        Email: trainer@l9fitness.com<br>
                        Password: password123
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>Trainer Account #2:</h5>
                    <div class="credential-box">
                        Email: trainer2@l9fitness.com<br>
                        Password: password123
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Member Account:</h5>
                    <div class="credential-box">
                        Email: member@l9fitness.com<br>
                        Password: password123
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="login.php" class="btn btn-primary btn-lg">
                    🔐 Go to Regular Login
                </a>
            </div>
        </div>

        <!-- Google Demo Login Test -->
        <div class="test-card">
            <h3>🎭 Google Demo Login Test</h3>
            <p>Test the instant login with demo Google accounts:</p>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Demo Mode:</strong> Click any account for instant login - no password needed!
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="badge bg-danger fs-6 mb-2">Admin</div>
                        <p>Full system access</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="badge bg-warning fs-6 mb-2">Trainer</div>
                        <p>Trainer dashboard</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="badge bg-success fs-6 mb-2">Trainer #2</div>
                        <p>Second trainer account</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="badge bg-primary fs-6 mb-2">Member</div>
                        <p>Member dashboard</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="auth/simple_google_demo.php" class="btn btn-danger btn-lg">
                    🎭 Go to Google Demo Login
                </a>
            </div>
        </div>

        <!-- Quick Access Links -->
        <div class="test-card">
            <h3>🚀 Quick Access Links</h3>
            <p>Direct links to test different parts of the system:</p>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="btn btn-info">
                    📊 Member Dashboard
                </a>
                <a href="trainer_dashboard.php" class="btn btn-success">
                    👨‍🏫 Trainer Dashboard
                </a>
                <a href="admin.php" class="btn btn-warning">
                    🔧 Admin Panel
                </a>
                <a href="classes.php" class="btn btn-primary">
                    🏋️ Classes
                </a>
                <a href="memberships.php" class="btn btn-secondary">
                    💳 Memberships
                </a>
                <a href="waki.php" class="btn btn-dark">
                    🤖 WAKI AI
                </a>
            </div>
        </div>

        <!-- Login Process Explanation -->
        <div class="test-card">
            <h3>📋 How It Works</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>🔐 Regular Login:</h5>
                    <ol>
                        <li>Enter email and password</li>
                        <li>System verifies credentials</li>
                        <li>Redirects based on role:
                            <ul>
                                <li>Admin → Admin Panel</li>
                                <li>Trainer → Trainer Dashboard</li>
                                <li>Member → Member Dashboard</li>
                            </ul>
                        </li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h5>🎭 Google Demo:</h5>
                    <ol>
                        <li>Click "Continue with Google (Demo)"</li>
                        <li>Select any demo account</li>
                        <li>Instant login without password</li>
                        <li>Same role-based redirection</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="test-card">
            <h3>🔧 Troubleshooting</h3>
            
            <div class="alert alert-warning">
                <h5>Common Issues & Solutions:</h5>
                <ul class="mb-0">
                    <li><strong>Login fails:</strong> Make sure you ran <code>fix_credentials.php</code> first</li>
                    <li><strong>Google demo empty:</strong> Demo accounts were created automatically</li>
                    <li><strong>Access denied:</strong> Check user role (1=Admin, 3=Trainer, 4=Member)</li>
                    <li><strong>Redirect issues:</strong> Clear browser cache and cookies</li>
                </ul>
            </div>
            
            <div class="mt-3">
                <a href="check_credentials.php" class="btn btn-outline-primary me-2">
                    🔍 Check Credentials
                </a>
                <a href="fix_credentials.php" class="btn btn-outline-success">
                    🔧 Fix Credentials
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>