<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('patient')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$patient_id = $_SESSION['user_id'];

// Get patient statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM appointments WHERE patient_id = :patient_id AND DATE(appointment_date) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM appointments WHERE patient_id = :patient_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$stats['total_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lab_tests WHERE patient_id = :patient_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$stats['lab_tests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM medical_records WHERE patient_id = :patient_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$stats['medical_records'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get upcoming appointments
$query = "SELECT a.*, u.full_name as doctor_name, u.department 
          FROM appointments a 
          JOIN users u ON a.doctor_id = u.id 
          WHERE a.patient_id = :patient_id 
          AND a.appointment_date >= NOW() 
          AND a.status = 'scheduled'
          ORDER BY a.appointment_date ASC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$upcoming_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent medical records
$query = "SELECT mr.*, u.full_name as doctor_name 
          FROM medical_records mr 
          JOIN users u ON mr.doctor_id = u.id 
          WHERE mr.patient_id = :patient_id 
          ORDER BY mr.visit_date DESC 
          LIMIT 3";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$recent_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - PHCHMS</title>
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
            <div class="content-header patient-content-header">
                <div class="header-left">
                    <h2>My Health Dashboard</h2>
                    <p class="welcome-message">Welcome to your personal health portal</p>
                </div>
                <div class="header-right">
                    <div class="patient-controls">
                        <button class="control-btn btn-primary" onclick="bookAppointment()">
                            <span class="btn-icon">📅</span>
                            Book Appointment
                        </button>
                        <button class="control-btn btn-success" onclick="emergencyHelp()">
                            <span class="btn-icon">🆘</span>
                            Emergency Help
                        </button>
                        <div class="health-status">
                            <span class="status-indicator status-good"></span>
                            <span class="status-text">Good Health</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
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
                        <button class="stat-action-btn" onclick="viewAppointments()">View</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🏥
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_appointments']; ?></h3>
                        <p>Total Visits</p>
                        <span class="stat-trend trend-up">All time</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewHistory()">History</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🔬
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['lab_tests']; ?></h3>
                        <p>Lab Tests</p>
                        <span class="stat-trend trend-up">+2 this month</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewLabResults()">Results</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📋
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['medical_records']; ?></h3>
                        <p>Medical Records</p>
                        <span class="stat-trend trend-up">Updated</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewRecords()">View</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                        <span class="ai-badge">Easy Access</span>
                    </div>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="bookAppointment()">
                            <span class="action-icon">📅</span>
                            <span class="action-text">Book Appointment</span>
                        </button>
                        <button class="quick-action-btn" onclick="viewPrescriptions()">
                            <span class="action-icon">💊</span>
                            <span class="action-text">My Prescriptions</span>
                        </button>
                        <button class="quick-action-btn" onclick="checkSymptoms()">
                            <span class="action-icon">🔍</span>
                            <span class="action-text">Symptom Checker</span>
                        </button>
                        <button class="quick-action-btn" onclick="messageDoctor()">
                            <span class="action-icon">💬</span>
                            <span class="action-text">Message Doctor</span>
                        </button>
                        <button class="quick-action-btn" onclick="requestRecords()">
                            <span class="action-icon">📄</span>
                            <span class="action-text">Request Records</span>
                        </button>
                        <button class="quick-action-btn" onclick="payBills()">
                            <span class="action-icon">💰</span>
                            <span class="action-text">Pay Bills</span>
                        </button>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3>Upcoming Appointments</h3>
                        <a href="appointments.php" class="view-all">View All</a>
                    </div>
                    <div class="appointments-list">
                        <?php if (count($upcoming_appointments) > 0): ?>
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                            <div class="appointment-item">
                                <div class="appointment-date">
                                    <strong><?php echo date('M j', strtotime($appointment['appointment_date'])); ?></strong>
                                    <span><?php echo date('D', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="appointment-details">
                                    <h4>Dr. <?php echo $appointment['doctor_name']; ?></h4>
                                    <p class="appointment-time"><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></p>
                                    <p class="appointment-department"><?php echo $appointment['department']; ?></p>
                                    <p class="appointment-reason"><?php echo $appointment['reason']; ?></p>
                                </div>
                                <div class="appointment-actions">
                                    <button class="btn-action btn-primary" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                        Details
                                    </button>
                                    <button class="btn-action btn-outline" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">
                                        Reschedule
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-appointments">
                                <p>No upcoming appointments scheduled.</p>
                                <button class="btn-primary" onclick="bookAppointment()">Book Your First Appointment</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Medical Activity -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Medical Activity</h3>
                    <a href="records.php" class="view-all">View All</a>
                </div>
                <div class="medical-activity">
                    <?php if (count($recent_records) > 0): ?>
                        <?php foreach ($recent_records as $record): ?>
                        <div class="activity-item">
                            <div class="activity-icon medical">🏥</div>
                            <div class="activity-content">
                                <h4>Visit with Dr. <?php echo $record['doctor_name']; ?></h4>
                                <p class="activity-details"><?php echo $record['diagnosis'] ?: 'Routine checkup'; ?></p>
                                <p class="activity-date"><?php echo date('F j, Y', strtotime($record['visit_date'])); ?></p>
                            </div>
                            <div class="activity-actions">
                                <button class="btn-action btn-outline" onclick="viewRecord(<?php echo $record['id']; ?>)">
                                    View Details
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-activity">
                            <p>No recent medical activity recorded.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Health Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Health Insights</h3>
                    <span class="ai-badge">Personalized</span>
                </div>
                <div class="health-insights">
                    <div class="insight-card">
                        <div class="insight-icon">💡</div>
                        <div class="insight-content">
                            <h4>Health Trend Analysis</h4>
                            <p>Your overall health has improved by 12% over the past 3 months.</p>
                            <div class="insight-metrics">
                                <div class="metric">
                                    <span class="metric-value">95%</span>
                                    <span class="metric-label">Medication Adherence</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">88%</span>
                                    <span class="metric-label">Appointment Attendance</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">🎯</div>
                        <div class="insight-content">
                            <h4>Personalized Recommendations</h4>
                            <ul class="recommendations-list">
                                <li>Continue with current medication schedule</li>
                                <li>Schedule follow-up appointment in 2 weeks</li>
                                <li>Consider light exercise routine</li>
                                <li>Monitor blood pressure weekly</li>
                            </ul>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">⚠️</div>
                        <div class="insight-content">
                            <h4>Health Alerts</h4>
                            <div class="alert-status status-good">
                                No critical alerts at this time
                            </div>
                            <p class="alert-note">Continue with your current health management plan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency & Quick Help -->
            <div class="content-card emergency-card">
                <div class="card-header">
                    <h3>Emergency & Quick Help</h3>
                    <span class="emergency-badge">24/7 Available</span>
                </div>
                <div class="emergency-actions">
                    <button class="emergency-btn critical" onclick="callEmergency()">
                        <span class="btn-icon">🚑</span>
                        <span class="btn-text">Emergency Services</span>
                        <span class="btn-subtext">Call 911</span>
                    </button>
                    <button class="emergency-btn urgent" onclick="contactDoctor()">
                        <span class="btn-icon">👨‍⚕️</span>
                        <span class="btn-text">Contact Doctor</span>
                        <span class="btn-subtext">Urgent Message</span>
                    </button>
                    <button class="emergency-btn info" onclick="findHospital()">
                        <span class="btn-icon">🏥</span>
                        <span class="btn-text">Find Hospital</span>
                        <span class="btn-subtext">Nearby Locations</span>
                    </button>
                    <button class="emergency-btn warning" onclick="symptomChecker()">
                        <span class="btn-icon">🔍</span>
                        <span class="btn-text">Symptom Checker</span>
                        <span class="btn-subtext">AI Assessment</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showNotifications() {
        alert('Showing your notifications and health alerts...');
    }

    function emergencyHelp() {
        if (confirm('Are you experiencing a medical emergency? Please call 911 immediately for life-threatening conditions.')) {
            window.location.href = 'emergency.php';
        }
    }

    function bookAppointment() {
        window.location.href = 'appointments.php?action=book';
    }

    function viewAppointments() {
        window.location.href = 'appointments.php';
    }

    function viewHistory() {
        window.location.href = 'records.php';
    }

    function viewLabResults() {
        window.location.href = 'lab-results.php';
    }

    function viewRecords() {
        window.location.href = 'records.php';
    }

    function viewPrescriptions() {
        window.location.href = 'prescriptions.php';
    }

    function checkSymptoms() {
        window.location.href = 'symptom-checker.php';
    }

    function messageDoctor() {
        window.location.href = 'messages.php';
    }

    function requestRecords() {
        alert('Requesting medical records...');
    }

    function payBills() {
        window.location.href = 'billing.php';
    }

    function viewAppointment(appointmentId) {
        window.location.href = 'appointment-details.php?id=' + appointmentId;
    }

    function rescheduleAppointment(appointmentId) {
        if (confirm('Reschedule this appointment?')) {
            window.location.href = 'appointments.php?reschedule=' + appointmentId;
        }
    }

    function viewRecord(recordId) {
        window.location.href = 'record-details.php?id=' + recordId;
    }

    function callEmergency() {
        if (confirm('Call emergency services (911)?')) {
            window.location.href = 'tel:911';
        }
    }

    function contactDoctor() {
        window.location.href = 'messages.php?urgent=true';
    }

    function findHospital() {
        alert('Finding nearby hospitals...');
    }

    function symptomChecker() {
        window.location.href = 'symptom-checker.php';
    }

    // Health status indicator
    function updateHealthStatus() {
        const status = document.querySelector('.health-status');
        const indicator = status.querySelector('.status-indicator');
        const text = status.querySelector('.status-text');
        
        // Simulate health status check
        const healthStatus = Math.random() > 0.3 ? 'good' : 'attention';
        
        if (healthStatus === 'good') {
            indicator.className = 'status-indicator status-good';
            text.textContent = 'Good Health';
        } else {
            indicator.className = 'status-indicator status-attention';
            text.textContent = 'Needs Attention';
        }
    }

    // Update health status every minute
    setInterval(updateHealthStatus, 60000);
    </script>

    <style>
    .patient-header {
        background: linear-gradient(135deg, #c0392b, #a93226);
    }

    .patient-sidebar {
        background: linear-gradient(180deg, #c0392b 0%, #a93226 100%);
    }

    .patient-sidebar .nav-link {
        color: #fadbd8;
        border-left-color: transparent;
    }

    .patient-sidebar .nav-link:hover,
    .patient-sidebar .nav-link.active {
        background: rgba(192, 57, 43, 0.2);
        color: white;
        border-left-color: white;
    }

    .patient-sidebar .nav-section-title {
        color: #f1948a;
    }

    .patient-content-header {
        background: linear-gradient(135deg, #c0392b, #a93226);
        color: white;
    }

    .patient-content-header h2,
    .patient-content-header .welcome-message {
        color: white;
    }

    .patient-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .health-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.2rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: white;
    }

    .status-good {
        background: #2ecc71;
        box-shadow: 0 0 10px #2ecc71;
    }

    .status-attention {
        background: #f39c12;
        box-shadow: 0 0 10px #f39c12;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 1rem;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .quick-action-btn:hover {
        background: #c0392b;
        color: white;
        border-color: #c0392b;
        transform: translateY(-3px);
    }

    .appointments-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .appointment-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #c0392b;
    }

    .appointment-date {
        text-align: center;
        min-width: 80px;
    }

    .appointment-date strong {
        display: block;
        color: #c0392b;
        font-size: 1.2rem;
    }

    .appointment-date span {
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .appointment-details {
        flex: 1;
    }

    .appointment-details h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .appointment-time {
        color: #c0392b;
        font-weight: 600;
        margin: 0.2rem 0;
    }

    .appointment-department {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin: 0.2rem 0;
    }

    .appointment-reason {
        color: #34495e;
        font-style: italic;
        margin: 0.5rem 0 0 0;
    }

    .appointment-actions {
        display: flex;
        gap: 0.5rem;
    }

    .medical-activity {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .activity-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .activity-icon.medical {
        color: #c0392b;
    }

    .activity-content {
        flex: 1;
    }

    .activity-content h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .activity-details {
        color: #7f8c8d;
        margin: 0.2rem 0;
    }

    .activity-date {
        color: #95a5a6;
        font-size: 0.9rem;
        margin: 0.5rem 0 0 0;
    }

    .health-insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #c0392b;
    }

    .insight-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .insight-content h4 {
        margin: 0 0 1rem 0;
        color: #2c3e50;
    }

    .insight-content p {
        margin: 0 0 1.5rem 0;
        color: #7f8c8d;
        line-height: 1.5;
    }

    .insight-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .recommendations-list {
        margin: 1rem 0 0 0;
        padding-left: 1.5rem;
    }

    .recommendations-list li {
        margin-bottom: 0.5rem;
        color: #495057;
    }

    .alert-status {
        padding: 1rem;
        border-radius: 6px;
        text-align: center;
        margin: 1rem 0;
        font-weight: 500;
    }

    .status-good {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-note {
        color: #6c757d;
        font-size: 0.9rem;
        text-align: center;
        margin: 0;
    }

    .emergency-card {
        border: 2px solid #e74c3c;
    }

    .emergency-badge {
        background: #e74c3c;
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .emergency-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .emergency-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem 1rem;
        border: none;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .emergency-btn.critical {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .emergency-btn.urgent {
        background: linear-gradient(135deg, #e67e22, #d35400);
    }

    .emergency-btn.info {
        background: linear-gradient(135deg, #3498db, #2980b9);
    }

    .emergency-btn.warning {
        background: linear-gradient(135deg, #f39c12, #e67e22);
    }

    .emergency-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .btn-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .btn-text {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .btn-subtext {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .no-appointments,
    .no-activity {
        text-align: center;
        padding: 2rem;
        color: #7f8c8d;
    }

    @media (max-width: 768px) {
        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }

        .health-insights {
            grid-template-columns: 1fr;
        }

        .emergency-actions {
            grid-template-columns: 1fr;
        }

        .appointment-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .appointment-actions {
            align-self: stretch;
            justify-content: stretch;
        }

        .appointment-actions .btn-action {
            flex: 1;
        }

        .patient-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .activity-item {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>
</body>
</html>