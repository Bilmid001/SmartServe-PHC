// Dashboard Specific JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Sidebar navigation active state
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
        
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Notifications system
    initializeNotifications();
    
    // Real-time updates
    startRealTimeUpdates();
});

function initializeCharts() {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') return;
    
    // Patient Statistics Chart
    const patientCtx = document.getElementById('patientStatsChart');
    if (patientCtx) {
        new Chart(patientCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Patients Registered',
                    data: [65, 59, 80, 81, 56, 72],
                    borderColor: 'var(--primary-blue)',
                    backgroundColor: 'rgba(26, 115, 232, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
    
    // Department Distribution Chart
    const deptCtx = document.getElementById('departmentChart');
    if (deptCtx) {
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cardiology', 'Pediatrics', 'Orthopedics', 'Neurology', 'General'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        'var(--primary-blue)',
                        'var(--teal)',
                        'var(--green)',
                        'var(--accent)',
                        'var(--navy)'
                    ]
                }]
            }
        });
    }
}

function initializeNotifications() {
    // Check for new notifications every 30 seconds
    setInterval(() => {
        // Simulate notification check
        const hasNewNotifications = Math.random() > 0.7;
        
        if (hasNewNotifications) {
            showNotification('New update available in the system');
        }
    }, 30000);
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">🔔</span>
            <span class="notification-text">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: var(--white);
        padding: 1rem;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1001;
        border-left: 4px solid var(--primary-blue);
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.remove();
    });
}

function startRealTimeUpdates() {
    // Update dashboard stats every minute
    setInterval(updateDashboardStats, 60000);
}

function updateDashboardStats() {
    // Simulate real-time data updates
    const statCards = document.querySelectorAll('.stat-card h3');
    statCards.forEach(card => {
        const currentValue = parseInt(card.textContent);
        const change = Math.floor(Math.random() * 5) - 2; // Random change between -2 and +2
        const newValue = Math.max(0, currentValue + change);
        
        // Animate the value change
        animateValueChange(card, currentValue, newValue);
    });
}

function animateValueChange(element, oldValue, newValue) {
    let current = oldValue;
    const increment = newValue > oldValue ? 1 : -1;
    const stepTime = 50;
    
    const timer = setInterval(() => {
        current += increment;
        element.textContent = current;
        
        if (current === newValue) {
            clearInterval(timer);
        }
    }, stepTime);
}

// Search functionality
function searchPatients(query) {
    // Simulate AJAX search
    console.log('Searching for:', query);
    // In real implementation, make API call to search endpoint
}

// AI Recommendation actions
function implementRecommendation(recommendationId) {
    if (confirm('Implement this AI recommendation?')) {
        // Simulate API call
        fetch(`/api/ai-recommendations/${recommendationId}/implement`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            showNotification('Recommendation implemented successfully');
        })
        .catch(error => {
            showNotification('Error implementing recommendation');
        });
    }
}

function dismissRecommendation(recommendationId) {
    if (confirm('Dismiss this recommendation?')) {
        // Simulate API call
        fetch(`/api/ai-recommendations/${recommendationId}/dismiss`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            showNotification('Recommendation dismissed');
        })
        .catch(error => {
            showNotification('Error dismissing recommendation');
        });
    }
}