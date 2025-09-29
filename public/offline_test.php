<?php
/**
 * Offline Support Test & Demo Page
 * Demonstrates offline functionality and caching
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

$pageTitle = "Offline Support Test";
$pageCSS = "assets/css/home.css";

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1><i class="fas fa-dumbbell text-danger"></i> L9 FITNESS</h1>
        <h2>Offline Support Demo</h2>
        <p class="text-muted">Test offline functionality and plan caching</p>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h4><i class="fas fa-wifi"></i> Connection Status</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Online Status:</span>
                        <span id="onlineStatus" class="badge bg-success">Online</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Service Worker:</span>
                        <span id="swStatus" class="badge bg-secondary">Checking...</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Cached Plans Data:</span>
                        <span id="cacheStatus" class="badge bg-secondary">Checking...</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Last Sync:</span>
                        <span id="lastSync" class="text-muted">Never</span>
                    </div>
                </div>
            </div>
            
            <div class="card bg-dark text-light mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-cog"></i> Test Actions</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-primary w-100" onclick="testCacheData()">
                                <i class="fas fa-download"></i> Cache Plans Data
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <button class="btn btn-info w-100" onclick="viewCachedData()">
                                <i class="fas fa-eye"></i> View Cached Data
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <button class="btn btn-warning w-100" onclick="clearCache()">
                                <i class="fas fa-trash"></i> Clear Cache
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <a href="offline_plans.html" class="btn btn-success w-100">
                                <i class="fas fa-dumbbell"></i> View Offline Plans
                            </a>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5>Offline Simulation</h5>
                    <p class="text-muted small">
                        To test offline mode: Open Developer Tools (F12) → Network tab → Check "Offline" → Reload page
                    </p>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> How Offline Support Works:</h6>
                        <ul class="small mb-0">
                            <li><strong>Service Worker:</strong> Caches essential files and API responses</li>
                            <li><strong>Local Storage:</strong> Stores user plans data for offline access</li>
                            <li><strong>Offline Detection:</strong> Shows offline banner when connection is lost</li>
                            <li><strong>Auto-Sync:</strong> Updates cached data every 5 minutes when online</li>
                            <li><strong>Fallback Page:</strong> Dedicated offline interface for viewing plans</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card bg-dark text-light mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> Features Available Offline</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">✅ Available Offline:</h6>
                            <ul class="small">
                                <li>View workout plans</li>
                                <li>View diet/nutrition plans</li>
                                <li>Check exercise instructions</li>
                                <li>Review meal details</li>
                                <li>View progress history</li>
                                <li>Check profile information</li>
                                <li>Browse cached content</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-danger">❌ Requires Internet:</h6>
                            <ul class="small">
                                <li>Login/logout</li>
                                <li>Update plans</li>
                                <li>Log new workouts</li>
                                <li>Record progress</li>
                                <li>Chat with trainers</li>
                                <li>Make payments</li>
                                <li>Upload photos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="testResults" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Test Results</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="testOutput" class="bg-black p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
// Update status indicators
function updateStatus() {
    // Online status
    const onlineEl = document.getElementById('onlineStatus');
    if (navigator.onLine) {
        onlineEl.textContent = 'Online';
        onlineEl.className = 'badge bg-success';
    } else {
        onlineEl.textContent = 'Offline';
        onlineEl.className = 'badge bg-danger';
    }
    
    // Service Worker status
    const swEl = document.getElementById('swStatus');
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(registration => {
            if (registration) {
                swEl.textContent = 'Active';
                swEl.className = 'badge bg-success';
            } else {
                swEl.textContent = 'Not Registered';
                swEl.className = 'badge bg-warning';
            }
        });
    } else {
        swEl.textContent = 'Not Supported';
        swEl.className = 'badge bg-danger';
    }
    
    // Cache status
    const cacheEl = document.getElementById('cacheStatus');
    const cachedData = localStorage.getItem('l9_plans_data');
    if (cachedData) {
        const data = JSON.parse(cachedData);
        const planCount = (data.workout_plans?.length || 0) + (data.diet_plans?.length || 0);
        cacheEl.textContent = `${planCount} plans cached`;
        cacheEl.className = 'badge bg-success';
    } else {
        cacheEl.textContent = 'No cached data';
        cacheEl.className = 'badge bg-warning';
    }
    
    // Last sync
    const syncEl = document.getElementById('lastSync');
    const lastSync = localStorage.getItem('l9_last_sync');
    if (lastSync) {
        const date = new Date(parseInt(lastSync));
        syncEl.textContent = date.toLocaleString();
    } else {
        syncEl.textContent = 'Never';
    }
}

async function testCacheData() {
    try {
        showResult('Caching plans data...');
        
        const response = await fetch('/Capstone-latest/public/api/offline_plans.php?action=all');
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('l9_plans_data', JSON.stringify(data.data));
            localStorage.setItem('l9_last_sync', Date.now().toString());
            
            showResult(`✅ Success! Cached ${JSON.stringify(data.data, null, 2)}`);
            updateStatus();
        } else {
            showResult(`❌ Failed: ${data.error}`);
        }
    } catch (error) {
        showResult(`❌ Error: ${error.message}`);
    }
}

function viewCachedData() {
    const cachedData = localStorage.getItem('l9_plans_data');
    if (cachedData) {
        const data = JSON.parse(cachedData);
        showResult(`📋 Cached Data:\n\n${JSON.stringify(data, null, 2)}`);
    } else {
        showResult('❌ No cached data found');
    }
}

function clearCache() {
    localStorage.removeItem('l9_plans_data');
    localStorage.removeItem('l9_last_sync');
    
    // Clear service worker cache
    if ('serviceWorker' in navigator) {
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
        }).then(() => {
            showResult('✅ All caches cleared successfully');
            updateStatus();
        });
    } else {
        showResult('✅ Local storage cleared');
        updateStatus();
    }
}

function showResult(text) {
    document.getElementById('testOutput').textContent = text;
    new bootstrap.Modal(document.getElementById('testResults')).show();
}

// Update status on page load and periodically
document.addEventListener('DOMContentLoaded', updateStatus);
setInterval(updateStatus, 5000);

// Listen for online/offline events
window.addEventListener('online', updateStatus);
window.addEventListener('offline', updateStatus);
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>