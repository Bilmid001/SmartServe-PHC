<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('records') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get records department statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM patients WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['new_patients_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM appointments WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM patients";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM medical_records WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_records'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'scheduled' AND DATE(appointment_date) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_authorizations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent patient registrations
$query = "SELECT * FROM patients ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records Dashboard - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
         <style>
        /* CSS Reset and Base Styles */
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }

        body {
            background-color: #f5f7fa !important;
            color: #333 !important;
            line-height: 1.6 !important;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 120px !important;
            padding: 20px !important;
            min-height: 100vh !important;
            transition: all 0.3s ease !important;
        }

    </style>
</head>
<body>
    
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header records-content-header">
                <div class="header-left">
                    <h2>Records Management Dashboard</h2>
                    <p class="welcome-message">Complete patient records management and data oversight</p>
                </div>
                <div class="header-right">
                    <div class="records-controls">
                        <button class="control-btn btn-primary" onclick="registerNewPatient()">
                            <span class="btn-icon">➕</span>
                            New Patient
                        </button>
                        <button class="control-btn btn-success" onclick="scheduleAppointment()">
                            <span class="btn-icon">📅</span>
                            Schedule
                        </button>
                        <div class="system-status">
                            <span class="status-indicator status-healthy"></span>
                            <span class="status-text">Records Updated</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Records Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            👥
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['new_patients_today']; ?></h3>
                        <p>New Patients Today</p>
                        <span class="stat-trend trend-up">+15% from yesterday</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewNewPatients()">View</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['today_appointments']; ?></h3>
                        <p>Today's Appointments</p>
                        <span class="stat-trend trend-up">+8 scheduled</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewAppointments()">Manage</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📋
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_patients']; ?></h3>
                        <p>Total Patients</p>
                        <span class="stat-trend trend-up">+12% growth</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewAllPatients()">Browse</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending_authorizations']; ?></h3>
                        <p>Pending Auth</p>
                        <span class="stat-trend trend-up">Needs review</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewAuthorizations()">Review</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div class="content-grid">
                <!-- Quick Actions -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                        <span class="ai-badge">AI Assisted</span>
                    </div>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="registerNewPatient()">
                            <span class="action-icon">👤</span>
                            <span class="action-text">Register Patient</span>
                        </button>
                        <button class="quick-action-btn" onclick="scheduleAppointment()">
                            <span class="action-icon">📅</span>
                            <span class="action-text">Schedule Appointment</span>
                        </button>
                        <button class="quick-action-btn" onclick="searchRecords()">
                            <span class="action-icon">🔍</span>
                            <span class="action-text">Search Records</span>
                        </button>
                        <button class="quick-action-btn" onclick="generateReport()">
                            <span class="action-icon">📊</span>
                            <span class="action-text">Generate Report</span>
                        </button>
                        <button class="quick-action-btn" onclick="dataExport()">
                            <span class="action-icon">📤</span>
                            <span class="action-text">Data Export</span>
                        </button>
                        <button class="quick-action-btn" onclick="runAudit()">
                            <span class="action-icon">🔍</span>
                            <span class="action-text">Data Audit</span>
                        </button>
                    </div>
                </div>

                <!-- Recent Patient Registrations -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Patient Registrations</h3>
                        <a href="patients.php" class="view-all">View All</a>
                    </div>
                    <div class="recent-patients">
                        <?php if (count($recent_patients) > 0): ?>
                            <?php foreach ($recent_patients as $patient): ?>
                            <div class="patient-item">
                                <div class="patient-avatar">
                                    <span class="avatar-icon">👤</span>
                                </div>
                                <div class="patient-info">
                                    <h4><?php echo $patient['full_name']; ?></h4>
                                    <p class="patient-id">ID: <?php echo $patient['patient_id']; ?></p>
                                    <p class="registration-date">
                                        Registered: <?php echo date('M j, Y g:i A', strtotime($patient['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="patient-actions">
                                    <button class="btn-action btn-primary" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                        View
                                    </button>
                                    <button class="btn-action btn-outline" onclick="editPatient(<?php echo $patient['id']; ?>)">
                                        Edit
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-patients">
                                <p>No recent patient registrations.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Treatment Authorization Queue -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Treatment Authorization Queue</h3>
                    <span class="alert-badge">Pending Review</span>
                </div>
                <div class="authorization-queue">
                    <div class="queue-item urgent">
                        <div class="queue-priority">
                            <span class="priority-badge">Urgent</span>
                        </div>
                        <div class="queue-info">
                            <h4>John Smith - Surgical Procedure</h4>
                            <p>Patient ID: PAT001 | Requested by: Dr. Johnson</p>
                            <p class="request-time">Submitted: Today, 09:30 AM</p>
                        </div>
                        <div class="queue-actions">
                            <button class="btn-action btn-success" onclick="approveAuthorization(1)">Approve</button>
                            <button class="btn-action btn-danger" onclick="rejectAuthorization(1)">Reject</button>
                            <button class="btn-action btn-outline" onclick="viewDetails(1)">Details</button>
                        </div>
                    </div>

                    <div class="queue-item normal">
                        <div class="queue-priority">
                            <span class="priority-badge">Normal</span>
                        </div>
                        <div class="queue-info">
                            <h4>Maria Garcia - Medication Refill</h4>
                            <p>Patient ID: PAT002 | Requested by: Dr. Smith</p>
                            <p class="request-time">Submitted: Today, 10:15 AM</p>
                        </div>
                        <div class="queue-actions">
                            <button class="btn-action btn-success" onclick="approveAuthorization(2)">Approve</button>
                            <button class="btn-action btn-danger" onclick="rejectAuthorization(2)">Reject</button>
                            <button class="btn-action btn-outline" onclick="viewDetails(2)">Details</button>
                        </div>
                    </div>

                    <div class="queue-item low">
                        <div class="queue-priority">
                            <span class="priority-badge">Low</span>
                        </div>
                        <div class="queue-info">
                            <h4>Robert Johnson - Physical Therapy</h4>
                            <p>Patient ID: PAT003 | Requested by: Dr. Brown</p>
                            <p class="request-time">Submitted: Yesterday, 3:45 PM</p>
                        </div>
                        <div class="queue-actions">
                            <button class="btn-action btn-success" onclick="approveAuthorization(3)">Approve</button>
                            <button class="btn-action btn-danger" onclick="rejectAuthorization(3)">Reject</button>
                            <button class="btn-action btn-outline" onclick="viewDetails(3)">Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Quality Dashboard -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Data Quality Dashboard</h3>
                    <span class="ai-badge">AI Monitoring</span>
                </div>
                <div class="data-quality-metrics">
                    <div class="quality-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="98">
                                <span class="chart-value">98%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Data Completeness</h4>
                            <p>All required fields populated</p>
                        </div>
                    </div>

                    <div class="quality-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="99">
                                <span class="chart-value">99%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Data Accuracy</h4>
                            <p>Verified and validated data</p>
                        </div>
                    </div>

                    <div class="quality-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="95">
                                <span class="chart-value">95%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Timeliness</h4>
                            <p>Records updated within 24 hours</p>
                        </div>
                    </div>

                    <div class="quality-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="100">
                                <span class="chart-value">100%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Compliance</h4>
                            <p>Meeting all regulatory requirements</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Data Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Data Insights</h3>
                    <span class="ai-badge">Machine Learning</span>
                </div>
                <div class="ai-insights">
                    <div class="insight-card">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Patient Growth Trend</h4>
                            <p>15% increase in new patient registrations this month compared to last month.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="viewGrowthReport()">View Report</button>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">🔍</div>
                        <div class="insight-content">
                            <h4>Data Anomaly Detected</h4>
                            <p>Unusual pattern in appointment cancellations detected. Recommend review.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="investigateAnomaly()">Investigate</button>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">💡</div>
                        <div class="insight-content">
                            <h4>Optimization Opportunity</h4>
                            <p>AI suggests streamlining patient registration process could reduce time by 25%.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="viewOptimization()">Learn More</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showRecordsAlerts() {
        alert('Showing records alerts and notifications...');
    }

    function quickPatientSearch() {
        const searchTerm = prompt('Enter patient name or ID:');
        if (searchTerm) {
            window.location.href = 'records-search.php?q=' + encodeURIComponent(searchTerm);
        }
    }

    function registerNewPatient() {
        window.location.href = 'patient-registration.php';
    }

    function scheduleAppointment() {
        window.location.href = 'appointments.php?action=new';
    }

    function viewNewPatients() {
        window.location.href = 'patients.php?filter=today';
    }

    function viewAppointments() {
        window.location.href = 'appointments.php';
    }

    function viewAllPatients() {
        window.location.href = 'patients.php';
    }

    function viewAuthorizations() {
        window.location.href = 'authorization.php';
    }

    function searchRecords() {
        window.location.href = 'records-search.php';
    }

    function generateReport() {
        window.location.href = 'reports.php';
    }

    function dataExport() {
        window.location.href = 'data-export.php';
    }

    function runAudit() {
        window.location.href = 'data-audit.php';
    }

    function viewPatient(patientId) {
        window.location.href = 'patient-details.php?id=' + patientId;
    }

    function editPatient(patientId) {
        window.location.href = 'edit-patient.php?id=' + patientId;
    }

    function approveAuthorization(authId) {
        if (confirm('Approve this treatment authorization?')) {
            alert('Authorization ' + authId + ' approved successfully.');
            // In real implementation, update database
        }
    }

    function rejectAuthorization(authId) {
        const reason = prompt('Please provide reason for rejection:');
        if (reason) {
            alert('Authorization ' + authId + ' rejected. Reason: ' + reason);
            // In real implementation, update database
        }
    }

    function viewDetails(authId) {
        alert('Viewing details for authorization ' + authId);
    }

    function viewGrowthReport() {
        alert('Opening patient growth report...');
    }

    function investigateAnomaly() {
        alert('Opening anomaly investigation dashboard...');
    }

    function viewOptimization() {
        alert('Showing process optimization recommendations...');
    }

    // Initialize quality metric charts
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.chart-circle').forEach(chart => {
            const percentage = chart.getAttribute('data-percentage');
            chart.style.background = `conic-gradient(#2980b9 ${percentage}%, #e9ecef ${percentage}% 100%)`;
        });
    });
    </script>

    <style>
    .records-header {
        background: linear-gradient(135deg, #2980b9, #2471a3) !important;
    }

    .records-sidebar {
        background: linear-gradient(180deg, #2980b9 0%, #2471a3 100%) !important;
    }

    .records-sidebar .nav-link {
        color: #d6eaf8 !important;
        border-left-color: transparent;
    }

    .records-sidebar .nav-link:hover,
    .records-sidebar .nav-link.active {
        background: rgba(41, 128, 185, 0.2);
        color: white !important;
        border-left-color: white;
    }

    .records-sidebar .nav-section-title {
        color: #aed6f1 !important;
    }

    .records-content-header {
        background: linear-gradient(135deg, #2980b9, #2471a3) !important;
        color: white !important;
    }

    .records-content-header h2,
    .records-content-header .welcome-message {
        color: white !important;
    }

    .records-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .recent-patients {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .patient-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #2980b9;
    }

    .patient-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #2980b9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .patient-info {
        flex: 1;
    }

    .patient-info h4 {
        margin: 0 0 0.3rem 0;
        color: #2c3e50;
    }

    .patient-id, .registration-date {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin: 0.1rem 0;
    }

    .patient-actions {
        display: flex;
        gap: 0.5rem;
    }

    .authorization-queue {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .queue-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid;
    }

    .queue-item.urgent {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), transparent);
        border-left-color: #e74c3c;
    }

    .queue-item.normal {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), transparent);
        border-left-color: #3498db;
    }

    .queue-item.low {
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), transparent);
        border-left-color: #2ecc71;
    }

    .queue-priority {
        min-width: 80px;
    }

    .priority-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .queue-item.urgent .priority-badge {
        background: #e74c3c;
        color: white;
    }

    .queue-item.normal .priority-badge {
        background: #3498db;
        color: white;
    }

    .queue-item.low .priority-badge {
        background: #2ecc71;
        color: white;
    }

    .queue-info {
        flex: 1;
    }

    .queue-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .queue-info p {
        margin: 0.2rem 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .request-time {
        font-style: italic;
    }

    .queue-actions {
        display: flex;
        gap: 0.5rem;
    }

    .data-quality-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }

    .quality-metric {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .metric-chart {
        margin-bottom: 1rem;
    }

    .chart-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: conic-gradient(#2980b9 0%, #e9ecef 0% 100%);
        transition: all 1s ease;
    }

    .chart-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2980b9;
    }

    .metric-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .metric-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .ai-insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #2980b9;
    }

    .insight-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .insight-content {
        flex: 1;
    }

    .insight-content h4 {
        margin: 0 0 0.8rem 0;
        color: #2c3e50;
    }

    .insight-content p {
        margin: 0 0 1rem 0;
        color: #7f8c8d;
        line-height: 1.5;
    }

    .insight-actions {
        display: flex;
        gap: 0.5rem;
    }

    .no-patients {
        text-align: center;
        padding: 2rem;
        color: #7f8c8d;
    }

    @media (max-width: 768px) {
        .data-quality-metrics {
            grid-template-columns: repeat(2, 1fr);
        }

        .ai-insights {
            grid-template-columns: 1fr;
        }

        .queue-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .queue-actions {
            align-self: stretch;
            justify-content: stretch;
        }

        .queue-actions .btn-action {
            flex: 1;
        }

        .patient-item {
            flex-direction: column;
            text-align: center;
        }

        .patient-actions {
            align-self: stretch;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .data-quality-metrics {
            grid-template-columns: 1fr;
        }

        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    </style>
</body>
</html>