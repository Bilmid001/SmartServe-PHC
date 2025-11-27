// Main JavaScript for PHCHMS

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileMenu) {
        mobileMenu.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
        });
    }

    // Login Modal
    const loginOptions = document.querySelectorAll('.login-option');
    const loginModal = document.getElementById('loginModal');
    const modalTitle = document.getElementById('modalTitle');
    const departmentInput = document.getElementById('departmentInput');
    const closeModal = document.querySelector('.close-modal');
    
    if (loginOptions.length > 0) {
        loginOptions.forEach(option => {
            option.addEventListener('click', function() {
                const department = this.getAttribute('data-department');
                let title = '';
                
                switch(department) {
                    case 'admin':
                        title = 'Admin Login';
                        break;
                    case 'records':
                        title = 'Records Department Login';
                        break;
                    case 'doctors':
                        title = 'Doctors/Clinicians Login';
                        break;
                    case 'pharmacy':
                        title = 'Pharmacy Login';
                        break;
                    case 'lab':
                        title = 'Laboratory Login';
                        break;
                    case 'eha':
                        title = 'Environmental Health Assistance Login';
                        break;
                    case 'patient':
                        title = 'Patient Portal Login';
                        break;
                }
                
                modalTitle.textContent = title;
                departmentInput.value = department;
                loginModal.style.display = 'flex';
            });
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            loginModal.style.display = 'none';
        });
    }
    
    if (loginModal) {
        window.addEventListener('click', function(event) {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
            }
        });
    }
    
    // Animated Statistics
    function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        if (!obj) return;
        
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            obj.innerHTML = value;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    // Initialize statistics when the page loads
    setTimeout(() => {
        animateValue('patients-today', 0, 127, 2000);
        animateValue('prescriptions', 0, 89, 2000);
        animateValue('lab-tests', 0, 64, 2000);
        animateValue('ai-recommendations', 0, 42, 2000);
    }, 500);

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = 'var(--danger)';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// AI Symptom Checker Function
function openSymptomChecker() {
    const symptoms = prompt('Please describe your symptoms:');
    if (symptoms) {
        // Simulate AI analysis
        const conditions = ['Common Cold', 'Flu', 'Allergies', 'Migraine'];
        const randomCondition = conditions[Math.floor(Math.random() * conditions.length)];
        const urgency = Math.random() > 0.7 ? 'Seek immediate medical attention' : 'Schedule an appointment';
        
        alert(`AI Analysis Results:\n\nSymptoms: ${symptoms}\nPossible Condition: ${randomCondition}\nRecommendation: ${urgency}`);
    }
}

// Export functionality
function exportToPDF(elementId, filename) {
    // Simplified PDF export simulation
    const element = document.getElementById(elementId);
    if (element) {
        alert(`Exporting ${filename}.pdf...`);
        // In real implementation, use a library like jsPDF
    }
}

function exportToExcel(data, filename) {
    // Simplified Excel export simulation
    alert(`Exporting ${filename}.xlsx...`);
    // In real implementation, use a library like SheetJS
}