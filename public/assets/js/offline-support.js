/**
 * Offline Support Integration
 * Adds offline functionality to existing L9 Fitness pages
 */

class L9OfflineSupport {
    constructor() {
        this.isOnline = navigator.onLine;
        this.hasServiceWorker = 'serviceWorker' in navigator;
        this.lastSyncTime = localStorage.getItem('l9_last_sync');
        
        this.init();
    }
    
    async init() {
        // Register service worker
        if (this.hasServiceWorker) {
            await this.registerServiceWorker();
        }
        
        // Set up event listeners
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
        
        // Add offline indicator to existing pages
        this.addOfflineIndicator();
        
        // Cache user data when online
        if (this.isOnline) {
            await this.cacheUserData();
        }
        
        // Set up periodic sync
        this.setupPeriodicSync();
    }
    
    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/Capstone-latest/public/sw.js');
            console.log('L9 Fitness Service Worker registered:', registration);
            
            // Listen for messages from service worker
            navigator.serviceWorker.addEventListener('message', event => {
                if (event.data.type === 'DATA_SYNCED') {
                    this.showNotification('Plans data updated', 'success');
                }
            });
            
            return registration;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            return null;
        }
    }
    
    addOfflineIndicator() {
        // Don't add if already exists or if this is the offline page
        if (document.getElementById('l9-offline-indicator') || 
            window.location.pathname.includes('offline_plans.html')) {
            return;
        }
        
        const indicator = document.createElement('div');
        indicator.id = 'l9-offline-indicator';
        indicator.className = 'l9-offline-indicator';
        indicator.innerHTML = `
            <div class="offline-banner ${this.isOnline ? 'd-none' : ''}">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-wifi-slash"></i>
                            <strong>You're offline</strong> - Some features may be limited
                        </div>
                        <div>
                            <a href="/Capstone-latest/public/offline_plans.html" class="btn btn-sm btn-outline-light me-2">
                                <i class="fas fa-dumbbell"></i> View Plans Offline
                            </a>
                            <button class="btn btn-sm btn-light" onclick="window.l9Offline.tryReconnect()">
                                <i class="fas fa-sync"></i> Retry
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add styles
        const styles = document.createElement('style');
        styles.textContent = `
            .l9-offline-indicator {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 9999;
            }
            
            .offline-banner {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
                padding: 0.75rem 0;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                animation: slideDown 0.3s ease-out;
            }
            
            @keyframes slideDown {
                from { transform: translateY(-100%); }
                to { transform: translateY(0); }
            }
            
            .offline-banner .btn-outline-light {
                border-color: rgba(255,255,255,0.5);
                color: white;
            }
            
            .offline-banner .btn-outline-light:hover {
                background: rgba(255,255,255,0.1);
                border-color: white;
            }
            
            .offline-banner .btn-light {
                background: rgba(255,255,255,0.9);
                color: #dc2626;
                border: none;
            }
            
            /* Adjust body padding when offline banner is shown */
            body.offline-mode {
                padding-top: 60px;
            }
        `;
        
        document.head.appendChild(styles);
        document.body.insertBefore(indicator, document.body.firstChild);
        
        // Adjust body padding if offline
        if (!this.isOnline) {
            document.body.classList.add('offline-mode');
        }
    }
    
    async cacheUserData() {
        try {
            const response = await fetch('/Capstone-latest/public/api/offline_plans.php?action=all');
            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem('l9_plans_data', JSON.stringify(data.data));
                localStorage.setItem('l9_last_sync', Date.now().toString());
                this.lastSyncTime = Date.now().toString();
                
                // Send data to service worker for caching
                if (navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({
                        type: 'CACHE_PLANS_DATA',
                        data: data.data
                    });
                }
                
                console.log('User data cached successfully');
            }
        } catch (error) {
            console.error('Failed to cache user data:', error);
        }
    }
    
    setupPeriodicSync() {
        // Sync data every 5 minutes when online
        setInterval(() => {
            if (this.isOnline) {
                this.cacheUserData();
            }
        }, 5 * 60 * 1000);
    }
    
    handleOnline() {
        this.isOnline = true;
        console.log('L9 Fitness: Connection restored');
        
        // Hide offline banner
        const banner = document.querySelector('.offline-banner');
        if (banner) {
            banner.classList.add('d-none');
            document.body.classList.remove('offline-mode');
        }
        
        // Show reconnection notification
        this.showNotification('Connection restored!', 'success');
        
        // Sync data
        this.cacheUserData();
    }
    
    handleOffline() {
        this.isOnline = false;
        console.log('L9 Fitness: Connection lost');
        
        // Show offline banner
        const banner = document.querySelector('.offline-banner');
        if (banner) {
            banner.classList.remove('d-none');
            document.body.classList.add('offline-mode');
        }
        
        // Show offline notification
        this.showNotification('You are now offline. Some features may be limited.', 'warning');
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show l9-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 350px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        `;
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    tryReconnect() {
        if (navigator.onLine) {
            window.location.reload();
        } else {
            this.showNotification('No internet connection detected. Please check your connection.', 'warning');
        }
    }
    
    // Utility method to check if user has cached plans
    hasCachedPlans() {
        const cached = localStorage.getItem('l9_plans_data');
        return cached && cached !== 'null';
    }
    
    // Method to get cached plans data
    getCachedPlans() {
        try {
            const cached = localStorage.getItem('l9_plans_data');
            return cached ? JSON.parse(cached) : null;
        } catch (error) {
            console.error('Failed to parse cached plans:', error);
            return null;
        }
    }
    
    // Method to add "View Offline" button to existing pages
    addOfflineButton() {
        // Don't add if already exists or no cached data
        if (document.getElementById('l9-offline-btn') || !this.hasCachedPlans()) {
            return;
        }
        
        const navbar = document.querySelector('.navbar .navbar-nav.ms-auto');
        if (navbar) {
            const offlineBtn = document.createElement('li');
            offlineBtn.className = 'nav-item';
            offlineBtn.innerHTML = `
                <a class="nav-link" href="/Capstone-latest/public/offline_plans.html" id="l9-offline-btn">
                    <i class="fas fa-download"></i> Offline Plans
                </a>
            `;
            
            // Insert before login/logout buttons
            const lastItem = navbar.lastElementChild;
            navbar.insertBefore(offlineBtn, lastItem);
        }
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.l9Offline = new L9OfflineSupport();
    
    // Add offline button after a short delay to ensure navbar is loaded
    setTimeout(() => {
        window.l9Offline.addOfflineButton();
    }, 1000);
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = L9OfflineSupport;
}