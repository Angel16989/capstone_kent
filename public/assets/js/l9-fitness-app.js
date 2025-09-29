/**
 * L9 Fitness - Comprehensive Profile & Dashboard JavaScript
 * Enhanced user experience with dynamic features
 */

class L9FitnessApp {
    constructor() {
        this.apiBase = 'api/profile_api.php';
        this.currentUser = null;
        this.notifications = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadUserData();
        this.setupNotifications();
        this.initializeFeatures();
    }

    setupEventListeners() {
        // Check-in/Check-out buttons
        document.querySelectorAll('.check-in-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleCheckInOut(e));
        });

        // Quick action buttons
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleQuickAction(e));
        });

        // Message form
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => this.sendMessage(e));
        }

        // Workout logging
        const workoutForm = document.getElementById('workoutForm');
        if (workoutForm) {
            workoutForm.addEventListener('submit', (e) => this.logWorkout(e));
        }

        // Announcement close buttons
        document.querySelectorAll('.announcement-close').forEach(btn => {
            btn.addEventListener('click', (e) => this.markAnnouncementRead(e));
        });

        // Photo upload
        const photoInput = document.getElementById('profile_photo');
        if (photoInput) {
            photoInput.addEventListener('change', (e) => this.previewPhoto(e));
        }
    }

    async loadUserData() {
        try {
            // Load user stats and recent activity
            await this.loadWorkoutStats();
            await this.loadUnreadAnnouncements();
            await this.updateQuickStats();
        } catch (error) {
            console.error('Error loading user data:', error);
        }
    }

    async handleCheckInOut(e) {
        e.preventDefault();
        const button = e.target;
        const action = button.dataset.action;
        
        button.disabled = true;
        button.innerHTML += ' <div class="loading-spinner"></div>';

        try {
            const response = await fetch(`${this.apiBase}?action=${action}`, {
                method: 'POST'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccessMessage(result.message);
                this.updateCheckInStatus(action);
            } else {
                this.showErrorMessage(result.message || 'Operation failed');
            }
        } catch (error) {
            this.showErrorMessage('Network error occurred');
            console.error('Check-in/out error:', error);
        } finally {
            button.disabled = false;
            button.innerHTML = button.innerHTML.replace(' <div class="loading-spinner"></div>', '');
        }
    }

    async sendMessage(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        const messageData = {
            receiver_type: formData.get('receiver_type'),
            subject: formData.get('subject'),
            message: formData.get('message')
        };

        try {
            const response = await fetch(`${this.apiBase}?action=send_message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(messageData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccessMessage('Message sent successfully!');
                form.reset();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showErrorMessage(result.message || 'Failed to send message');
            }
        } catch (error) {
            this.showErrorMessage('Network error occurred');
            console.error('Message send error:', error);
        }
    }

    async logWorkout(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        const workoutData = {
            workout_type: formData.get('workout_type'),
            duration_minutes: formData.get('duration_minutes'),
            calories_burned: formData.get('calories_burned'),
            notes: formData.get('notes'),
            workout_date: formData.get('workout_date')
        };

        try {
            const response = await fetch(`${this.apiBase}?action=log_workout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(workoutData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccessMessage('Workout logged successfully!');
                form.reset();
                await this.loadWorkoutStats();
            } else {
                this.showErrorMessage(result.message || 'Failed to log workout');
            }
        } catch (error) {
            this.showErrorMessage('Network error occurred');
            console.error('Workout log error:', error);
        }
    }

    async markAnnouncementRead(e) {
        e.preventDefault();
        const button = e.target;
        const announcementId = button.dataset.announcementId;
        const announcementCard = button.closest('.announcement-item');

        try {
            const response = await fetch(`${this.apiBase}?action=mark_announcement_read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ announcement_id: announcementId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                announcementCard.style.transition = 'all 0.3s ease';
                announcementCard.style.opacity = '0';
                announcementCard.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    announcementCard.remove();
                    this.updateNotificationCount();
                }, 300);
            }
        } catch (error) {
            console.error('Error marking announcement as read:', error);
        }
    }

    async loadWorkoutStats() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_workout_stats`);
            const stats = await response.json();
            
            this.updateStatsDisplay(stats);
        } catch (error) {
            console.error('Error loading workout stats:', error);
        }
    }

    async loadUnreadAnnouncements() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_unread_announcements`);
            const announcements = await response.json();
            
            this.displayAnnouncements(announcements);
            this.updateNotificationCount(announcements.length);
        } catch (error) {
            console.error('Error loading announcements:', error);
        }
    }

    updateStatsDisplay(stats) {
        const elements = {
            totalWorkouts: document.getElementById('totalWorkouts'),
            avgDuration: document.getElementById('avgDuration'),
            totalCalories: document.getElementById('totalCalories')
        };

        if (elements.totalWorkouts) {
            elements.totalWorkouts.textContent = stats.total_workouts || 0;
        }
        if (elements.avgDuration) {
            elements.avgDuration.textContent = Math.round(stats.avg_duration || 0) + ' min';
        }
        if (elements.totalCalories) {
            elements.totalCalories.textContent = (stats.total_calories || 0).toLocaleString();
        }
    }

    displayAnnouncements(announcements) {
        const container = document.getElementById('announcementsContainer');
        if (!container) return;

        container.innerHTML = '';
        
        announcements.forEach(announcement => {
            const announcementEl = this.createAnnouncementElement(announcement);
            container.appendChild(announcementEl);
        });
    }

    createAnnouncementElement(announcement) {
        const div = document.createElement('div');
        div.className = 'announcement-item unread animate-slide-up';
        div.innerHTML = `
            <button class="announcement-close" data-announcement-id="${announcement.id}">×</button>
            <h6>${this.escapeHtml(announcement.title)}</h6>
            <p>${this.escapeHtml(announcement.content)}</p>
            <small><i class="fas fa-clock"></i> ${this.formatDate(announcement.created_at)}</small>
        `;

        // Add event listener to close button
        const closeBtn = div.querySelector('.announcement-close');
        closeBtn.addEventListener('click', (e) => this.markAnnouncementRead(e));

        return div;
    }

    updateCheckInStatus(action) {
        const statusEl = document.getElementById('checkInStatus');
        if (statusEl) {
            if (action === 'check_in') {
                statusEl.innerHTML = '<span class="badge bg-success">Checked In</span>';
            } else {
                statusEl.innerHTML = '<span class="badge bg-secondary">Checked Out</span>';
            }
        }
    }

    updateNotificationCount(count = null) {
        const badges = document.querySelectorAll('.notification-dot');
        badges.forEach(badge => {
            if (count === null || count === 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'flex';
                if (badge.classList.contains('large')) {
                    badge.textContent = count > 99 ? '99+' : count;
                }
            }
        });
    }

    previewPhoto(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('photoPreview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }

    showSuccessMessage(message) {
        this.createAlert(message, 'success');
    }

    showErrorMessage(message) {
        this.createAlert(message, 'error');
    }

    createAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `${type}-message animate-slide-up`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

        document.body.insertBefore(alertDiv, document.body.firstChild);

        setTimeout(() => {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => alertDiv.remove(), 300);
        }, 4000);
    }

    handleQuickAction(e) {
        e.preventDefault();
        const action = e.currentTarget.dataset.action;
        
        switch (action) {
            case 'book_class':
                window.location.href = 'classes.php';
                break;
            case 'view_schedule':
                window.location.href = 'dashboard.php#schedule';
                break;
            case 'update_profile':
                window.location.href = 'profile.php';
                break;
            case 'view_payments':
                window.location.href = 'profile.php#payments';
                break;
            case 'contact_trainer':
                window.location.href = 'profile.php#messages';
                break;
            default:
                console.log('Unknown action:', action);
        }
    }

    setupNotifications() {
        // Check for browser notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    initializeFeatures() {
        // Initialize tooltips
        this.initTooltips();
        
        // Setup auto-refresh for dynamic content
        this.setupAutoRefresh();
        
        // Initialize progress bars
        this.animateProgressBars();
    }

    initTooltips() {
        // Add tooltips to elements with data-tooltip attribute
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    }

    hideTooltip() {
        const tooltip = document.querySelector('.custom-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    async updateQuickStats() {
        // Update dashboard quick stats
        const statsElements = document.querySelectorAll('.stats-number');
        statsElements.forEach(element => {
            const endValue = parseInt(element.textContent);
            this.animateNumber(element, 0, endValue, 1000);
        });
    }

    animateNumber(element, start, end, duration) {
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(start + (end - start) * progress);
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
                bar.style.transition = 'width 1s ease-in-out';
            }, 100);
        });
    }

    setupAutoRefresh() {
        // Refresh notifications every 5 minutes
        setInterval(() => {
            this.loadUnreadAnnouncements();
        }, 5 * 60 * 1000);
    }

    // Utility functions
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-AU', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.L9App = new L9FitnessApp();
});

// Additional CSS for dynamic elements
const dynamicStyles = `
<style>
.custom-tooltip {
    position: absolute;
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    z-index: 1000;
    pointer-events: none;
    opacity: 0;
    animation: tooltipFadeIn 0.2s ease-out forwards;
}

@keyframes tooltipFadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.progress-bar {
    transition: width 0.5s ease-in-out;
}

.animate-number {
    transition: all 0.3s ease;
}

.success-message, .error-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    max-width: 400px;
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.fab {
    animation: fabBounce 2s ease-in-out infinite;
}

@keyframes fabBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>`;

// Inject dynamic styles
document.head.insertAdjacentHTML('beforeend', dynamicStyles);