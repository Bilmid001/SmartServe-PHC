<?php
// session_start();
require_once '../../config/init.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
// $db = $database->getConnection();

// Get comprehensive admin statistics
$stats = [];
$stats['total_users'] = $db->table('users')->count();
$stats['total_patients'] = $db->table('patients')->count();

// $query = "SELECT COUNT(*) as total FROM appointments WHERE DATE(created_at) = CURDATE()";
// $stmt = $db->prepare($query);
// $stmt->execute();
$stats['today_appointments'] = $db->table('appointments')->whereRaw('DATE(created_at) = CURDATE()')->count();
$stats['pending_tests'] = $db->table('lab_tests')->where('status', 'pending')->count();

// $query = "SELECT COUNT(*) as total FROM pharmacy_inventory WHERE quantity <= reorder_level";
// $stmt = $db->prepare($query);
// $stmt->execute();
$stats['low_stock'] = $db->table('pharmacy_inventory')->where('quantity', '<=', 'reorder_level')->count();

// $query = "SELECT COUNT(*) as total FROM eha_reports WHERE status = 'open'";
// $stmt = $db->prepare($query);
// $stmt->execute();
$stats['open_eha_cases'] = $db->table('eha_reports')->where('status', 'open')->count();

// Get system health metrics
// $query = "SELECT COUNT(*) as total FROM audit_logs WHERE DATE(timestamp) = CURDATE()";
// $stmt = $db->prepare($query);
// $stmt->execute();
$stats['today_activities'] = $db->table('audit_logs')->whereRaw('DATE(timestamp) = CURDATE()')->count();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
        <!-- Main Content -->
            <div class="content-header admin-content-header">
                <div class="header-left">
                    <h2>Administration Dashboard</h2>
                    <p class="welcome-message">Complete system overview and management controls</p>
                </div>
                <div class="header-right">
                    <div class="admin-controls">
                        <button class="control-btn btn-primary" onclick="runSystemDiagnostic()">
                            <span class="btn-icon">🔧</span>
                            System Check
                        </button>
                        <button class="control-btn btn-success" onclick="generateDailyReport()">
                            <span class="btn-icon">📄</span>
                            Daily Report
                        </button>
                        <div class="system-status">
                            <span class="status-indicator status-healthy"></span>
                            <span class="status-text">All Systems Operational</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Overview Cards -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            👨‍💼
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>System Users</p>
                        <span class="stat-trend trend-up">+5 this month</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="manageUsers()">Manage</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            👥
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_patients']; ?></h3>
                        <p>Total Patients</p>
                        <span class="stat-trend trend-up">+12% growth</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewPatients()">View</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['today_appointments']; ?></h3>
                        <p>Today's Appointments</p>
                        <span class="stat-trend trend-neutral">Scheduled</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewAppointments()">Schedule</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🔬
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending_tests']; ?></h3>
                        <p>Pending Lab Tests</p>
                        <span class="stat-trend trend-down">-8% from yesterday</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewLabTests()">Monitor</button>
                    </div>
                </div>

                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💊
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['low_stock']; ?></h3>
                        <p>Low Stock Items</p>
                        <span class="stat-trend trend-up">Attention needed</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="manageInventory()">Reorder</button>
                    </div>
                </div>

                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🌿
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['open_eha_cases']; ?></h3>
                        <p>EHA Cases</p>
                        <span class="stat-trend trend-up">Active</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewEHACases()">Review</button>
                    </div>
                </div>
            </div>

            <!-- System Health Monitoring -->
            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header">
                        <h3>System Health Monitor</h3>
                        <span class="health-badge health-excellent">Excellent</span>
                    </div>
                    <div class="health-metrics">
                        <div class="health-metric">
                            <div class="metric-info">
                                <span class="metric-name">Server Uptime</span>
                                <span class="metric-value">99.98%</span>
                            </div>
                            <div class="metric-bar">
                                <div class="bar-fill" style="width: 99.98%"></div>
                            </div>
                        </div>
                        <div class="health-metric">
                            <div class="metric-info">
                                <span class="metric-name">Database Performance</span>
                                <span class="metric-value">98.5%</span>
                            </div>
                            <div class="metric-bar">
                                <div class="bar-fill" style="width: 98.5%"></div>
                            </div>
                        </div>
                        <div class="health-metric">
                            <div class="metric-info">
                                <span class="metric-name">API Response Time</span>
                                <span class="metric-value">125ms</span>
                            </div>
                            <div class="metric-bar">
                                <div class="bar-fill" style="width: 95%"></div>
                            </div>
                        </div>
                        <div class="health-metric">
                            <div class="metric-info">
                                <span class="metric-name">Storage Capacity</span>
                                <span class="metric-value">72%</span>
                            </div>
                            <div class="metric-bar">
                                <div class="bar-fill" style="width: 72%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent System Activities</h3>
                        <a href="audit-logs.php" class="view-all">View All</a>
                    </div>
                    <div class="activities-list">
                        <div class="activity-item">
                            <div class="activity-icon success">✓</div>
                            <div class="activity-content">
                                <p><strong>New user registration:</strong> Dr. Sarah Johnson</p>
                                <span class="activity-time">2 minutes ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon warning">⚠️</div>
                            <div class="activity-content">
                                <p><strong>Security alert:</strong> Multiple failed login attempts</p>
                                <span class="activity-time">15 minutes ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon info">🔄</div>
                            <div class="activity-content">
                                <p><strong>System backup:</strong> Nightly backup completed</p>
                                <span class="activity-time">1 hour ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon primary">📊</div>
                            <div class="activity-content">
                                <p><strong>Report generated:</strong> Monthly analytics</p>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Performance -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Department Performance</h3>
                    <span class="ai-badge">AI Optimized</span>
                </div>
                <div class="department-performance">
                    <div class="performance-grid">
                        <div class="performance-card">
                            <div class="dept-icon">👨‍⚕️</div>
                            <div class="dept-info">
                                <h4>Medical</h4>
                                <p>Doctors & Clinicians</p>
                            </div>
                            <div class="performance-metrics">
                                <div class="metric">
                                    <span class="metric-value">94%</span>
                                    <span class="metric-label">Efficiency</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">125</span>
                                    <span class="metric-label">Patients</span>
                                </div>
                            </div>
                        </div>

                        <div class="performance-card">
                            <div class="dept-icon">💊</div>
                            <div class="dept-info">
                                <h4>Pharmacy</h4>
                                <p>Medication Management</p>
                            </div>
                            <div class="performance-metrics">
                                <div class="metric">
                                    <span class="metric-value">89%</span>
                                    <span class="metric-label">Stock Level</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">98%</span>
                                    <span class="metric-label">Accuracy</span>
                                </div>
                            </div>
                        </div>

                        <div class="performance-card">
                            <div class="dept-icon">🔬</div>
                            <div class="dept-info">
                                <h4>Laboratory</h4>
                                <p>Testing & Analysis</p>
                            </div>
                            <div class="performance-metrics">
                                <div class="metric">
                                    <span class="metric-value">96%</span>
                                    <span class="metric-label">On Time</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">99.2%</span>
                                    <span class="metric-label">Accuracy</span>
                                </div>
                            </div>
                        </div>

                        <div class="performance-card">
                            <div class="dept-icon">📋</div>
                            <div class="dept-info">
                                <h4>Records</h4>
                                <p>Patient Management</p>
                            </div>
                            <div class="performance-metrics">
                                <div class="metric">
                                    <span class="metric-value">91%</span>
                                    <span class="metric-label">Efficiency</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">100%</span>
                                    <span class="metric-label">Compliance</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI System Recommendations -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI System Recommendations</h3>
                    <span class="ai-badge">Machine Learning</span>
                </div>
                <div class="ai-recommendations-grid">
                    <div class="recommendation-card critical">
                        <div class="recommendation-header">
                            <h4>🚨 Security Enhancement</h4>
                            <span class="priority-badge">Critical</span>
                        </div>
                        <p>Implement multi-factor authentication for all admin accounts to enhance security.</p>
                        <div class="recommendation-actions">
                            <button class="btn-action btn-primary" onclick="implementSecurity()">Implement Now</button>
                            <button class="btn-action btn-outline" onclick="scheduleSecurity()">Schedule</button>
                        </div>
                    </div>

                    <div class="recommendation-card high">
                        <div class="recommendation-header">
                            <h4>📈 Performance Optimization</h4>
                            <span class="priority-badge">High</span>
                        </div>
                        <p>Database query optimization needed for faster patient search functionality.</p>
                        <div class="recommendation-actions">
                            <button class="btn-action btn-primary" onclick="optimizeDatabase()">Optimize</button>
                            <button class="btn-action btn-outline" onclick="viewDetails()">Details</button>
                        </div>
                    </div>

                    <div class="recommendation-card medium">
                        <div class="recommendation-header">
                            <h4>💾 Backup Strategy</h4>
                            <span class="priority-badge">Medium</span>
                        </div>
                        <p>Consider implementing incremental backups to reduce storage requirements.</p>
                        <div class="recommendation-actions">
                            <button class="btn-action btn-primary" onclick="updateBackup()">Update</button>
                            <button class="btn-action btn-outline" onclick="dismissRecommendation()">Later</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showSystemAlerts() {
        alert('Showing system alerts and notifications...');
    }

    function openQuickSettings() {
        alert('Opening quick settings panel...');
    }

    function runSystemDiagnostic() {
        alert('Running comprehensive system diagnostic...');
    }

    function generateDailyReport() {
        alert('Generating daily system report...');
    }

    function manageUsers() {
        window.location.href = 'users.php';
    }

    function viewPatients() {
        window.location.href = '../records/patients.php';
    }

    function viewAppointments() {
        window.location.href = '../records/appointments.php';
    }

    function viewLabTests() {
        window.location.href = '../laboratory/tests.php';
    }

    function manageInventory() {
        window.location.href = '../pharmacy/inventory.php';
    }

    function viewEHACases() {
        window.location.href = '../eha/inspections.php';
    }

    function implementSecurity() {
        alert('Implementing security enhancements...');
    }

    function scheduleSecurity() {
        alert('Scheduling security implementation...');
    }

    function optimizeDatabase() {
        alert('Optimizing database performance...');
    }

    function updateBackup() {
        alert('Updating backup strategy...');
    }

    function dismissRecommendation() {
        alert('Recommendation dismissed.');
    }

    // Real-time system monitoring
    setInterval(() => {
        // Simulate real-time updates
        console.log('System monitoring active...');
    }, 30000);
    </script>

    <style>
  /* ===============================
   GLOBAL STYLING
================================= */
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
}

/* Utility */
.text-center { text-align: center; }
.hidden { display: none; }

/* Colors */
:root {
    --primary: #3498db;
    --primary-dark: #2980b9;
    --success: #2ecc71;
    --warning: #f39c12;
    --danger: #e74c3c;
    --purple: #8e44ad;
    --gray-light: #ecf0f1;
    --gray-dark: #34495e;
}

/* ===============================
   ADMIN TOP HEADER
================================= */
.admin-content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.8rem;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    margin-bottom: 1.5rem;
    color: white;
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-content-header h2 {
    margin: 0;
    font-size: 1.7rem;
}

.welcome-message {
    margin: 0;
    opacity: 0.9;
}

/* Control Buttons */
.admin-controls {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.control-btn {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: white;
    color: var(--primary-dark);
    padding: .7rem 1.2rem;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    font-weight: 600;
    transition: .3s ease;
}

.control-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.btn-primary { background: #2980b9; color: white; }
.btn-success { background: #27ae60; color: white; }

/* Status Box */
.system-status {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.2);
    padding: .6rem 1rem;
    border-radius: 8px;
    gap: .5rem;
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-healthy {
    background: var(--success);
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: .5; }
    100% { opacity: 1; }
}

/* ===============================
   STATS CARDS
================================= */
.stats-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: .3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.09);
}

.stat-card::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 6px;
    top: 0;
    left: 0;
}

.stat-card-primary::before { background: var(--primary); }
.stat-card-success::before { background: var(--success); }
.stat-card-warning::before { background: var(--warning); }
.stat-card-info::before { background: var(--primary-dark); }
.stat-card-danger::before { background: var(--danger); }
.stat-card-purple::before { background: var(--purple); }

.icon-circle {
    width: 55px;
    height: 55px;
    background: var(--gray-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-content h3 {
    margin: 0;
    font-size: 1.6rem;
}

.stat-content p {
    margin: 0;
    color: #7f8c8d;
}

.stat-trend {
    font-size: .8rem;
}

.stat-action-btn {
    padding: .4rem .9rem;
    background: var(--primary-dark);
    color: white;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: .3s;
}

.stat-action-btn:hover {
    background: var(--primary);
}

/* ===============================
   CONTENT CARD
================================= */
.content-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.health-badge,
.ai-badge {
    padding: .4rem 1rem;
    border-radius: 20px;
    font-size: .8rem;
    font-weight: 600;
}

.health-excellent {
    background: var(--success);
    color: white;
}

/* Health Metrics */
.health-metrics {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.metric-info {
    display: flex;
    justify-content: space-between;
}

.metric-bar {
    width: 100%;
    height: 8px;
    background: #ecf0f1;
    border-radius: 6px;
}

.bar-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 6px;
}

/* ===============================
   ACTIVITIES LIST
================================= */
.activities-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    gap: 1rem;
}

.activity-icon {
    font-size: 1.4rem;
}

.activity-content p {
    margin: 0;
}

/* ===============================
   DEPARTMENT GRID
================================= */
.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px,1fr));
    gap: 1.5rem;
}

.performance-card {
    background: #f9f9f9;
    border-left: 4px solid var(--primary);
    padding: 1.2rem;
    border-radius: 10px;
    transition: .3s;
}

.performance-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.performance-metrics {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    margin-top: 1rem;
    gap: 1rem;
}

/* ===============================
   AI RECOMMENDATIONS
================================= */
.ai-recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px,1fr));
    gap: 1.5rem;
}

.recommendation-card {
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    border-left: 4px solid;
}

.recommendation-card.critical { border-left-color: var(--danger); }
.recommendation-card.high     { border-left-color: var(--warning); }
.recommendation-card.medium   { border-left-color: var(--primary); }

.priority-badge {
    padding: .3rem .7rem;
    border-radius: 5px;
    font-size: .75rem;
    color: white;
}

.btn-action {
    padding: .5rem 1rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--primary);
}

/* ===============================
   RESPONSIVE
================================= */

@media (max-width: 992px) {
    .admin-content-header {
        flex-direction: column;
        text-align: center;
    }

    .admin-controls {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .stat-card {
        flex-direction: column;
        text-align: center;
    }

    .stat-actions {
        text-align: center;
        width: 100%;
    }
}

@media (max-width: 600px) {
    .control-btn {
        width: 100%;
        justify-content: center;
    }

    .activities-list .activity-item {
        flex-direction: column;
        text-align: center;
    }
}
    </style>
</body>
</html>