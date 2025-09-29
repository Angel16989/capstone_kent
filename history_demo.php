<!DOCTYPE html>
<html>
<head>
    <title>🎉 L9 Fitness - History & Invoices Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: white;
            font-family: 'Arial', sans-serif;
        }
        .demo-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        .feature-card {
            background: linear-gradient(135deg, #ff4444, #ff6666);
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            color: white;
            text-align: center;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 68, 68, 0.3);
        }
        .demo-link {
            background: white;
            color: #ff4444;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
        }
        .demo-link:hover {
            background: #ff4444;
            color: white;
            transform: scale(1.05);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="text-center mb-5">
            <h1 class="display-3">🏋️ L9 FITNESS</h1>
            <h2 class="text-gradient">BEAST MODE HISTORY & INVOICES</h2>
            <p class="lead">Complete Check-in History, Payment Tracking & Downloadable Invoices System</p>
        </div>

        <div class="stats-grid">
            <div class="feature-card">
                <h3><i class="fas fa-clock-rotate-left"></i> Check-in History</h3>
                <p>📊 Complete visit tracking<br>
                ⏱️ Duration calculations<br>
                🔥 Active session monitoring<br>
                📈 Statistics & analytics</p>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-credit-card"></i> Payment History</h3>
                <p>💳 All payment records<br>
                📄 Invoice management<br>
                📊 Financial statistics<br>
                🔍 Transaction details</p>
            </div>
            
            <div class="feature-card">
                <h3><i class="fas fa-download"></i> Downloadable Invoices</h3>
                <p>📋 Professional invoices<br>
                🎨 L9 Fitness branding<br>
                💾 PDF-ready format<br>
                🔐 Secure access</p>
            </div>
        </div>

        <div class="text-center my-5">
            <h3>🚀 Experience the Ultimate Fitness Management System</h3>
            <p class="lead">Login as a celebrity member to see comprehensive history!</p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <h5>🕺 Michael Jackson</h5>
                    <p>mj@l9fitness.com</p>
                    <a href="login.php" class="demo-link">LOGIN AS MJ</a>
                </div>
                <div class="col-md-4">
                    <h5>💪 Arnold Schwarzenegger</h5>
                    <p>arnold@l9fitness.com</p>
                    <a href="login.php" class="demo-link">LOGIN AS ARNOLD</a>
                </div>
                <div class="col-md-4">
                    <h5>🗿 The Rock</h5>
                    <p>therock@l9fitness.com</p>
                    <a href="login.php" class="demo-link">LOGIN AS ROCK</a>
                </div>
            </div>
            
            <div class="mt-4">
                <strong>Password for all:</strong> <code style="background: #333; padding: 5px 10px; border-radius: 5px;">beast123</code>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="feature-card">
                    <h4>🏋️ Dashboard Features</h4>
                    <ul class="list-unstyled">
                        <li>✅ Recent check-ins preview</li>
                        <li>✅ Payment history summary</li>
                        <li>✅ Quick check-in/out buttons</li>
                        <li>✅ Real-time status updates</li>
                        <li>✅ Direct invoice downloads</li>
                    </ul>
                    <a href="dashboard.php" class="demo-link">📊 View Dashboard</a>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-card">
                    <h4>👤 Profile Features</h4>
                    <ul class="list-unstyled">
                        <li>✅ Complete visit history table</li>
                        <li>✅ Duration & statistics tracking</li>
                        <li>✅ Comprehensive payment records</li>
                        <li>✅ Invoice download center</li>
                        <li>✅ Interactive payment details</li>
                    </ul>
                    <a href="profile.php" class="demo-link">👤 View Profile</a>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <h3>🎯 What's New & Enhanced:</h3>
            <div class="row mt-4">
                <div class="col-md-3">
                    <h5>📈 Statistics</h5>
                    <p>Total visits, active sessions, average duration, total workout time</p>
                </div>
                <div class="col-md-3">
                    <h5>📄 Invoices</h5>
                    <p>Professional L9 branded invoices with all transaction details</p>
                </div>
                <div class="col-md-3">
                    <h5>🔍 Details</h5>
                    <p>Interactive payment modals with complete transaction information</p>
                </div>
                <div class="col-md-3">
                    <h5>🔄 Real-time</h5>
                    <p>Live updates for active check-ins and status changes</p>
                </div>
            </div>
        </div>

        <div class="alert alert-success mt-5 text-center" style="background: rgba(40, 167, 69, 0.2); border: 2px solid #28a745;">
            <h4>🎉 SYSTEM READY!</h4>
            <p class="mb-0">Complete check-in history, payment tracking, and downloadable invoices are now live!</p>
            <p class="mt-2"><strong>Login as any celebrity user to experience the full beast mode system! 💪🔥</strong></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>