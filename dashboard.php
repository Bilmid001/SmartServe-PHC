<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();
if (!hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get comprehensive admin statistics
$stats = [];

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total patients
$query = "SELECT COUNT(*) as total FROM patients";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Today's appointments
$query = "SELECT COUNT(*) as total FROM appointments WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending lab tests
$query = "SELECT COUNT(*) as total FROM lab_tests WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_tests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Low stock items
$query = "SELECT COUNT(*) as total FROM pharmacy_inventory WHERE quantity <= reorder_level";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Open EHA cases
$query = "SELECT COUNT(*) as total FROM eha_reports WHERE status = 'open'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['open_eha_cases'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get system health metrics (safe)
try {
    $query = "SELECT COUNT(*) as total FROM audit_logs WHERE DATE(timestamp) = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['today_activities'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    // If table does not exist or query fails
    $stats['today_activities'] = 0;
    error_log("Audit log query failed: " . $e->getMessage());
}

require_once './includes/adminsidebar.php';

?>

        <!-- Main Content -->
        <div class="main-content">
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
    .admin-header {
        background: linear-gradient(135deg, #2c3e50, #34495e);
    }

    .admin-sidebar {
        background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
    }

    .admin-sidebar .nav-link {
        color: #bdc3c7;
        border-left-color: transparent;
    }

    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link.active {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
        border-left-color: #3498db;
    }

    .admin-sidebar .nav-section-title {
        color: #95a5a6;
    }

    .admin-content-header {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }

    .admin-content-header h2,
    .admin-content-header .welcome-message {
        color: white;
    }

    .admin-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .control-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .system-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.2rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: white;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .status-healthy {
        background: #2ecc71;
        box-shadow: 0 0 10px #2ecc71;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .stat-card-purple::before {
        background: linear-gradient(135deg, #9b59b6, #8e44ad);
    }

    .stat-actions {
        margin-top: 1rem;
    }

    .stat-action-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .stat-action-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .health-badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .health-excellent {
        background: #2ecc71;
        color: white;
    }

    .health-metrics {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .health-metric {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .metric-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .metric-name {
        color: #34495e;
        font-weight: 500;
    }

    .metric-value {
        color: #2c3e50;
        font-weight: 700;
    }

    .metric-bar {
        height: 8px;
        background: #ecf0f1;
        border-radius: 4px;
        overflow: hidden;
    }

    .bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #3498db, #2980b9);
        border-radius: 4px;
        transition: width 1s ease;
    }

    .department-performance {
        margin-top: 1rem;
    }

    .performance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .performance-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #3498db;
        transition: all 0.3s ease;
    }

    .performance-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .dept-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .dept-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .dept-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .performance-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }

    .performance-metrics .metric {
        text-align: center;
        padding: 0.8rem;
        background: white;
        border-radius: 6px;
    }

    .ai-recommendations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .recommendation-card {
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid;
    }

    .recommendation-card.critical {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), transparent);
        border-left-color: #e74c3c;
    }

    .recommendation-card.high {
        background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), transparent);
        border-left-color: #f39c12;
    }

    .recommendation-card.medium {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), transparent);
        border-left-color: #3498db;
    }

    .recommendation-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .recommendation-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-btn {
        position: relative;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 0.8rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .header-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .admin-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .performance-grid {
            grid-template-columns: 1fr;
        }

        .ai-recommendations-grid {
            grid-template-columns: 1fr;
        }

        .header-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>