<!DOCTYPE html>
<html>
<head>
    <title>🏋️ How to View Check-in History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: white;
            padding: 20px;
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }
        .guide-container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255, 68, 68, 0.1);
            border: 2px solid #ff4444;
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
        }
        .step-card {
            background: rgba(0,0,0,0.7);
            border: 2px solid #ff4444;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .step-card:hover {
            background: rgba(255, 68, 68, 0.1);
            box-shadow: 0 10px 25px rgba(255, 68, 68, 0.3);
            transform: translateY(-2px);
        }
        .step-number {
            background: linear-gradient(135deg, #ff4444, #ff6666);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2em;
            margin-right: 15px;
        }
        .btn-beast {
            background: linear-gradient(135deg, #ff4444, #ff6666);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.4);
        }
        .btn-beast:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 25px rgba(255, 68, 68, 0.6);
            color: white;
        }
        .highlight-box {
            background: rgba(255, 68, 68, 0.2);
            border: 2px dashed #ff4444;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="guide-container">
        <div class="text-center mb-5">
            <h1 class="display-4">🏋️ How to View Check-in History</h1>
            <p class="lead">Your gym visits are tracked and ready to view!</p>
        </div>

        <div class="highlight-box text-center">
            <h3>✅ Your Check-in Status:</h3>
            <p><strong>Current Check-ins:</strong> 1 active session</p>
            <p><strong>User:</strong> Michael Jackson (mj@l9fitness.com)</p>
            <p><strong>Status:</strong> <span style="color: #28a745;">🔥 Currently checked in since today</span></p>
        </div>

        <div class="step-card">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number">1</div>
                <h4>Go to Your Profile Page</h4>
            </div>
            <p>Click the profile button or use the direct link below to access your profile.</p>
            <a href="profile.php" class="btn-beast">
                <i class="fas fa-user"></i> Go to Profile
            </a>
        </div>

        <div class="step-card">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number">2</div>
                <h4>Click the "Check-ins" Tab</h4>
            </div>
            <p>Once on the profile page, look for the navigation tabs at the top. Click on <strong>"🏋️ Check-ins"</strong> tab.</p>
            <div class="highlight-box">
                <p><strong>Tab Navigation:</strong> Personal Info | Fitness | Photos | Announcements | Payments | <span style="background: #ff4444; padding: 5px 10px; border-radius: 5px;">🏋️ Check-ins</span> | Messages | Security</p>
            </div>
        </div>

        <div class="step-card">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number">3</div>
                <h4>View Your Complete History</h4>
            </div>
            <p>In the Check-ins tab, you'll see:</p>
            <ul>
                <li>✅ Complete table of all your gym visits</li>
                <li>🕒 Check-in and check-out times</li>
                <li>⏱️ Duration of each visit</li>
                <li>🔥 Current active sessions</li>
                <li>📊 Statistics (total visits, average duration, etc.)</li>
            </ul>
        </div>

        <div class="step-card">
            <div class="d-flex align-items-center mb-3">
                <div class="step-number">🚀</div>
                <h4>Quick Access Options</h4>
            </div>
            <p>Use these direct links that will automatically open the correct tab:</p>
            <div class="text-center">
                <a href="profile.php#checkins" class="btn-beast" onclick="localStorage.setItem('openTab', 'checkins')">
                    <i class="fas fa-clock"></i> Direct to Check-in History
                </a>
                <a href="dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="alert alert-success mt-4">
            <h5><i class="fas fa-lightbulb"></i> Pro Tip:</h5>
            <p class="mb-0">
                <strong>From the Dashboard:</strong> Click on "🏋️ Visit History" in the Quick Actions section, 
                or the "View All History" button in the Recent Check-ins card. These will automatically 
                take you to the correct tab!
            </p>
        </div>

        <div class="text-center mt-5">
            <h4>🔥 Ready to Check Your History?</h4>
            <a href="profile.php#checkins" class="btn-beast btn-lg" onclick="localStorage.setItem('openTab', 'checkins')">
                <i class="fas fa-history"></i> View My Check-in History Now!
            </a>
        </div>
    </div>

    <script>
    // Show success message after redirect
    if (document.referrer.includes('dashboard.php')) {
        setTimeout(() => {
            const toast = document.createElement('div');
            toast.className = 'alert alert-success position-fixed fade show';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    Instructions loaded! Follow the steps to view your check-in history.
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }, 1000);
    }
    </script>
</body>
</html>