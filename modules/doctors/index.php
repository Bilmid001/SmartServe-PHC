<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('doctor')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$doctor_id = $_SESSION['user_id'];

// Get doctor-specific statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM appointments WHERE doctor_id = :doctor_id AND DATE(appointment_date) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(DISTINCT patient_id) as total FROM appointments WHERE doctor_id = :doctor_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$stats['total_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM medical_records WHERE doctor_id = :doctor_id AND DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$stats['today_consultations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lab_tests WHERE ordered_by = :doctor_id AND status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$stats['pending_results'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get today's appointments
$query = "SELECT a.*, p.full_name, p.patient_id 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          WHERE a.doctor_id = :doctor_id 
          AND DATE(a.appointment_date) = CURDATE() 
          AND a.status = 'scheduled'
          ORDER BY a.appointment_date ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$today_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - PHCHMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
 /* ================================================
   CSS Reset and Base Styles
================================================ */
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

/* ================================================
   Main Content Area
================================================ */
.main-content {
    margin-left: 120px !important;
    padding: 20px !important;
    min-height: 100vh !important;
    transition: all 0.3s ease !important;
}

/* ================================================
   Popup Overlay
================================================ */
.popup-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.7) !important;
    display: none !important;
    justify-content: center !important;
    align-items: center !important;
    z-index: 1000 !important;
}

.popup-content {
    background: white !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
    max-width: 90% !important;
    max-height: 90% !important;
    overflow-y: auto !important;
    position: relative !important;
    animation: popupFadeIn 0.3s ease !important;
}

@keyframes popupFadeIn {
    from { opacity: 0 !important; transform: scale(0.9) !important; }
    to { opacity: 1 !important; transform: scale(1) !important; }
}

.popup-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 20px !important;
    border-bottom: 1px solid #e1e5eb !important;
    background: #f8fafc !important;
    border-radius: 12px 12px 0 0 !important;
}

.popup-header h3 {
    color: #1a365d !important;
    font-size: 20px !important;
    font-weight: 600 !important;
}

.popup-close {
    background: none !important;
    border: none !important;
    font-size: 24px !important;
    color: #718096 !important;
    cursor: pointer !important;
    padding: 5px !important;
    transition: color 0.3s ease !important;
}

.popup-close:hover {
    color: #e53e3e !important;
}

.popup-body {
    padding: 20px !important;
}

/* ================================================
   Content Header (Doctor Dashboard)
================================================ */
.doctor-content-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 30px !important;
    padding: 20px 0 !important;
    border-bottom: 1px solid #e1e5eb !important;
}

.header-left h2 {
    font-size: 28px !important;
    font-weight: 700 !important;
    color: #1a365d !important;
    margin-bottom: 5px !important;
}

.welcome-message {
    color: #718096 !important;
    font-size: 16px !important;
}

.doctor-controls {
    display: flex !important;
    align-items: center !important;
    gap: 15px !important;
}

/* ================================================
   Buttons & Status
================================================ */
.control-btn {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 10px 16px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    border: none !important;
}

.btn-primary {
    background: #4299e1 !important;
    color: white !important;
}

.btn-primary:hover {
    background: #3182ce !important;
    transform: translateY(-2px) !important;
}

.btn-success {
    background: #48bb78 !important;
    color: white !important;
}

.btn-success:hover {
    background: #38a169 !important;
    transform: translateY(-2px) !important;
}

.availability-status {
    display: flex !important;
    align-items: center !important;
    background: #e6fffa !important;
    padding: 8px 16px !important;
    border-radius: 20px !important;
    border: 1px solid #81e6d9 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}

.availability-status:hover {
    background: #b2f5ea !important;
}

.status-indicator {
    width: 10px !important;
    height: 10px !important;
    border-radius: 50% !important;
    margin-right: 8px !important;
    animation: pulse 2s infinite !important;
}

.status-available { background: #38b2ac !important; }
.status-busy { background: #e53e3e !important; }

.status-text {
    color: #234e52 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
}

@keyframes pulse {
    0% { opacity: 1 !important; }
    50% { opacity: 0.5 !important; }
    100% { opacity: 1 !important; }
}

/* ================================================
   Stats Cards
================================================ */
.stats-cards-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
    gap: 20px !important;
    margin-bottom: 30px !important;
}

.stat-card {
    background: white !important;
    border-radius: 12px !important;
    padding: 20px !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
    display: flex !important;
    align-items: center !important;
    transition: transform 0.3s ease, box-shadow 0.3s ease !important;
    border-left: 4px solid !important;
    position: relative !important;
}

.stat-card:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
}

.stat-card-primary { border-left-color: #4299e1 !important; }
.stat-card-success { border-left-color: #48bb78 !important; }
.stat-card-info    { border-left-color: #0bc5ea !important; }
.stat-card-warning { border-left-color: #ed8936 !important; }

.stat-icon .icon-circle {
    width: 50px !important;
    height: 50px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 20px !important;
    background: rgba(66, 153, 225, 0.1) !important;
}

.stat-card-success .icon-circle { background: rgba(72, 187, 120, 0.1) !important; }
.stat-card-info .icon-circle    { background: rgba(11, 197, 234, 0.1) !important; }
.stat-card-warning .icon-circle { background: rgba(237, 137, 54, 0.1) !important; }

.stat-content h3 {
    font-size: 24px !important;
    font-weight: 700 !important;
    color: #2d3748 !important;
    margin-bottom: 5px !important;
}

.stat-content p {
    color: #718096 !important;
    font-size: 14px !important;
    margin-bottom: 5px !important;
}

/* Trend Colors */
.trend-up      { color: #48bb78 !important; }
.trend-down    { color: #e53e3e !important; }
.trend-neutral { color: #718096 !important; }

/* Stat Card Actions */
.stat-actions { position: absolute !important; top: 15px !important; right: 15px !important; }
.stat-action-btn {
    background: transparent !important;
    border: 1px solid #e1e5eb !important;
    color: #718096 !important;
    padding: 4px 12px !important;
    border-radius: 6px !important;
    font-size: 12px !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}

.stat-action-btn:hover {
    background: #f7fafc !important;
    color: #4299e1 !important;
    border-color: #4299e1 !important;
}

/* ================================================
   Responsive Grids & Cards
================================================ */
.content-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
    margin-bottom: 20px !important;
}

@media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr !important; } }
@media (max-width: 768px) {
    .main-content { margin-left: 0 !important; }
    .doctor-content-header { flex-direction: column !important; align-items: flex-start !important; gap: 15px !important; }
    .doctor-controls { width: 100% !important; justify-content: space-between !important; }
    .stats-cards-grid { grid-template-columns: 1fr !important; }
    .appointment-slot { flex-direction: column !important; align-items: flex-start !important; }
    .slot-time { border-right: none !important; border-bottom: 1px solid #e1e5eb !important; padding-right: 0 !important; padding-bottom: 10px !important; margin-bottom: 10px !important; width: 100% !important; text-align: left !important; }
    .slot-actions { width: 100% !important; justify-content: flex-end !important; margin-top: 10px !important; }
}

/* ================================================
   Forms
================================================ */
.form-group { margin-bottom: 15px !important; }
.form-label { display: block !important; margin-bottom: 5px !important; color: #4a5568 !important; font-weight: 600 !important; }
.form-control, .form-select { width: 100% !important; padding: 10px !important; border: 1px solid #e1e5eb !important; border-radius: 6px !important; font-size: 14px !important; transition: border-color 0.3s ease !important; background: white !important; }
.form-control:focus { outline: none !important; border-color: #4299e1 !important; box-shadow: 0 0 0 3px rgba(66,153,225,0.1) !important; }
.form-textarea { min-height: 100px !important; resize: vertical !important; }
.form-actions { display: flex !important; justify-content: flex-end !important; gap: 10px !important; margin-top: 20px !important; padding-top: 15px !important; border-top: 1px solid #e1e5eb !important; }

.btn-cancel { background: #718096 !important; color: white !important; border: none !important; padding: 10px 20px !important; border-radius: 6px !important; cursor: pointer !important; transition: background 0.3s ease !important; }
.btn-cancel:hover { background: #4a5568 !important; }

.btn-submit { background: #4299e1 !important; color: white !important; border: none !important; padding: 10px 20px !important; border-radius: 6px !important; cursor: pointer !important; transition: background 0.3s ease !important; }
.btn-submit:hover { background: #3182ce !important; }

    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header doctor-content-header">
            <div class="header-left">
                <h2>Clinical Dashboard</h2>
                <p class="welcome-message">Welcome back, Doctor. Here's your schedule for today.</p>
            </div>
            <div class="header-right">
                <div class="doctor-controls">
                    <button class="control-btn btn-primary" onclick="openNewConsultationPopup()">
                        <span class="btn-icon">➕</span>
                        New Consultation
                    </button>
                    <button class="control-btn btn-success" onclick="openTodaySchedulePopup()">
                        <span class="btn-icon">📅</span>
                        Today's Schedule
                    </button>
                    <div class="availability-status" onclick="toggleAvailability()">
                        <span class="status-indicator status-available"></span>
                        <span class="status-text">Available</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Statistics -->
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
                    <span class="stat-trend trend-up">+3 from yesterday</span>
                </div>
                <div class="stat-actions">
                    <button class="stat-action-btn" onclick="openAppointmentsPopup()">View</button>
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
                    <span class="stat-trend trend-up">+5 this month</span>
                </div>
                <div class="stat-actions">
                    <button class="stat-action-btn" onclick="openPatientsPopup()">Manage</button>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <div class="icon-circle">
                        💬
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['today_consultations']; ?></h3>
                    <p>Today's Consultations</p>
                    <span class="stat-trend trend-neutral">In progress</span>
                </div>
                <div class="stat-actions">
                    <button class="stat-action-btn" onclick="openConsultationPopup()">Start</button>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ⏳
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_results']; ?></h3>
                    <p>Pending Results</p>
                    <span class="stat-trend trend-down">-2 from yesterday</span>
                </div>
                <div class="stat-actions">
                    <button class="stat-action-btn" onclick="openLabResultsPopup()">Review</button>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="content-grid">
            <div class="content-card">
                <div class="card-header">
                    <h3>Today's Appointments</h3>
                    <a href="appointments.php" class="view-all">View All</a>
                </div>
                <div class="appointments-timeline">
                    <?php if (count($today_appointments) > 0): ?>
                        <?php foreach ($today_appointments as $appointment): ?>
                        <div class="appointment-slot">
                            <div class="slot-time">
                                <strong><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></strong>
                            </div>
                            <div class="slot-info">
                                <h4><?php echo $appointment['full_name']; ?></h4>
                                <p class="patient-id">ID: <?php echo $appointment['patient_id']; ?></p>
                                <p class="appointment-reason"><?php echo $appointment['reason']; ?></p>
                            </div>
                            <div class="slot-actions">
                                <button class="btn-action btn-primary" onclick="openConsultationPopup(<?php echo $appointment['id']; ?>)">
                                    Start
                                </button>
                                <button class="btn-action btn-outline" onclick="openPatientChartPopup(<?php echo $appointment['patient_id']; ?>)">
                                    Chart
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-appointments">
                            <div class="no-appointments-icon">📅</div>
                            <h4>No Appointments Scheduled</h4>
                            <p>You have no appointments scheduled for today.</p>
                            <button class="btn-primary" onclick="openScheduleAppointmentPopup()">Schedule Appointment</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                    <span class="ai-badge">AI Powered</span>
                </div>
                <div class="quick-actions-grid">
                    <button class="quick-action-btn" onclick="openNewConsultationPopup()">
                        <span class="action-icon">💬</span>
                        <span class="action-text">New Consultation</span>
                    </button>
                    <button class="quick-action-btn" onclick="openPrescriptionPopup()">
                        <span class="action-icon">💊</span>
                        <span class="action-text">Write Prescription</span>
                    </button>
                    <button class="quick-action-btn" onclick="openLabTestPopup()">
                        <span class="action-icon">🔬</span>
                        <span class="action-text">Order Lab Test</span>
                    </button>
                    <button class="quick-action-btn" onclick="openClinicalGuidelinesPopup()">
                        <span class="action-icon">📚</span>
                        <span class="action-text">Clinical Guidelines</span>
                    </button>
                    <button class="quick-action-btn" onclick="openDrugInteractionsPopup()">
                        <span class="action-icon">⚠️</span>
                        <span class="action-text">Drug Checker</span>
                    </button>
                    <button class="quick-action-btn" onclick="openEMRAccessPopup()">
                        <span class="action-icon">📋</span>
                        <span class="action-text">EMR Access</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Patients -->
        <div class="content-card">
            <div class="card-header">
                <h3>Recent Patients</h3>
                <a href="patients.php" class="view-all">View All</a>
            </div>
            <div class="patients-grid">
                <div class="patient-card">
                    <div class="patient-avatar">
                        <span class="avatar-icon">👤</span>
                    </div>
                    <div class="patient-details">
                        <h4>John Smith</h4>
                        <p class="patient-id">ID: PAT001</p>
                        <p class="last-visit">Last visit: Today</p>
                        <div class="patient-conditions">
                            <span class="condition-tag">Hypertension</span>
                            <span class="condition-tag">Diabetes</span>
                        </div>
                    </div>
                    <div class="patient-actions">
                        <button class="btn-action btn-primary" onclick="openPatientDetailsPopup(1)">View</button>
                        <button class="btn-action btn-outline" onclick="openMessagePatientPopup(1)">Message</button>
                    </div>
                </div>

                <div class="patient-card">
                    <div class="patient-avatar">
                        <span class="avatar-icon">👤</span>
                    </div>
                    <div class="patient-details">
                        <h4>Maria Garcia</h4>
                        <p class="patient-id">ID: PAT002</p>
                        <p class="last-visit">Last visit: 2 days ago</p>
                        <div class="patient-conditions">
                            <span class="condition-tag">Asthma</span>
                        </div>
                    </div>
                    <div class="patient-actions">
                        <button class="btn-action btn-primary" onclick="openPatientDetailsPopup(2)">View</button>
                        <button class="btn-action btn-outline" onclick="openMessagePatientPopup(2)">Message</button>
                    </div>
                </div>

                <div class="patient-card">
                    <div class="patient-avatar">
                        <span class="avatar-icon">👤</span>
                    </div>
                    <div class="patient-details">
                        <h4>Robert Johnson</h4>
                        <p class="patient-id">ID: PAT003</p>
                        <p class="last-visit">Last visit: 1 week ago</p>
                        <div class="patient-conditions">
                            <span class="condition-tag">Arthritis</span>
                            <span class="condition-tag">Obesity</span>
                        </div>
                    </div>
                    <div class="patient-actions">
                        <button class="btn-action btn-primary" onclick="openPatientDetailsPopup(3)">View</button>
                        <button class="btn-action btn-outline" onclick="openMessagePatientPopup(3)">Message</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Clinical Support -->
        <div class="content-card">
            <div class="card-header">
                <h3>AI Clinical Support</h3>
                <span class="ai-badge">Real-time Assistance</span>
            </div>
            <div class="clinical-support">
                <div class="support-feature">
                    <div class="feature-icon">🔍</div>
                    <div class="feature-content">
                        <h4>Symptom Analysis</h4>
                        <p>AI-powered differential diagnosis based on patient symptoms</p>
                        <button class="btn-feature" onclick="openSymptomAnalyzerPopup()">Analyze Symptoms</button>
                    </div>
                </div>

                <div class="support-feature">
                    <div class="feature-icon">💊</div>
                    <div class="feature-content">
                        <h4>Medication Advisor</h4>
                        <p>Get AI recommendations for medication and dosage</p>
                        <button class="btn-feature" onclick="openMedicationAdvisorPopup()">Get Advice</button>
                    </div>
                </div>

                <div class="support-feature">
                    <div class="feature-icon">📊</div>
                    <div class="feature-content">
                        <h4>Treatment Optimizer</h4>
                        <p>Optimize treatment plans based on patient history</p>
                        <button class="btn-feature" onclick="openTreatmentOptimizerPopup()">Optimize</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Popup Overlays -->
    <div id="newConsultationPopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>New Consultation</h3>
                <button class="popup-close" onclick="closePopup('newConsultationPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <select class="form-control">
                        <option value="">Select Patient</option>
                        <option value="1">John Smith (PAT001)</option>
                        <option value="2">Maria Garcia (PAT002)</option>
                        <option value="3">Robert Johnson (PAT003)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Chief Complaint</label>
                    <textarea class="form-control form-textarea" placeholder="Describe the patient's main concern..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Symptoms</label>
                    <textarea class="form-control form-textarea" placeholder="List all symptoms and their duration..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Vital Signs</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="text" class="form-control" placeholder="BP (e.g., 120/80)">
                        <input type="text" class="form-control" placeholder="Pulse (e.g., 72)">
                        <input type="text" class="form-control" placeholder="Temp (e.g., 98.6°F)">
                        <input type="text" class="form-control" placeholder="Resp Rate (e.g., 16)">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('newConsultationPopup')">Cancel</button>
                    <button class="btn-submit" onclick="startConsultation()">Start Consultation</button>
                </div>
            </div>
        </div>
    </div>

    <div id="todaySchedulePopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
            <div class="popup-header">
                <h3>Today's Schedule</h3>
                <button class="popup-close" onclick="closePopup('todaySchedulePopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (count($today_appointments) > 0): ?>
                        <?php foreach ($today_appointments as $appointment): ?>
                        <div class="appointment-slot">
                            <div class="slot-time">
                                <strong><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></strong>
                            </div>
                            <div class="slot-info">
                                <h4><?php echo $appointment['full_name']; ?></h4>
                                <p class="patient-id">ID: <?php echo $appointment['patient_id']; ?></p>
                                <p class="appointment-reason"><?php echo $appointment['reason']; ?></p>
                            </div>
                            <div class="slot-actions">
                                <button class="btn-action btn-primary" onclick="openConsultationPopup(<?php echo $appointment['id']; ?>)">
                                    Start
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-appointments">
                            <div class="no-appointments-icon">📅</div>
                            <h4>No Appointments Scheduled</h4>
                            <p>You have no appointments scheduled for today.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="appointmentsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>All Appointments</h3>
                <button class="popup-close" onclick="closePopup('appointmentsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <button class="btn-action btn-primary">Today</button>
                    <button class="btn-action btn-outline">This Week</button>
                    <button class="btn-action btn-outline">This Month</button>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <!-- Sample appointment data -->
                    <div class="appointment-slot">
                        <div class="slot-time">
                            <strong>09:00 AM</strong>
                        </div>
                        <div class="slot-info">
                            <h4>John Smith</h4>
                            <p class="patient-id">ID: PAT001</p>
                            <p class="appointment-reason">Follow-up: Hypertension Management</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-primary">Start</button>
                        </div>
                    </div>
                    <div class="appointment-slot">
                        <div class="slot-time">
                            <strong>10:30 AM</strong>
                        </div>
                        <div class="slot-info">
                            <h4>Maria Garcia</h4>
                            <p class="patient-id">ID: PAT002</p>
                            <p class="appointment-reason">Asthma Check-up</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-primary">Start</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="patientsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>Patient Management</h3>
                <button class="popup-close" onclick="closePopup('patientsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <input type="text" class="form-control" placeholder="Search patients..." style="width: 300px;">
                    <button class="btn-action btn-primary">Add New Patient</button>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <div class="patients-grid">
                        <div class="patient-card">
                            <div class="patient-avatar">
                                <span class="avatar-icon">👤</span>
                            </div>
                            <div class="patient-details">
                                <h4>John Smith</h4>
                                <p class="patient-id">ID: PAT001</p>
                                <p class="last-visit">Last visit: Today</p>
                                <div class="patient-conditions">
                                    <span class="condition-tag">Hypertension</span>
                                    <span class="condition-tag">Diabetes</span>
                                </div>
                            </div>
                            <div class="patient-actions">
                                <button class="btn-action btn-primary">View</button>
                                <button class="btn-action btn-outline">Chart</button>
                            </div>
                        </div>
                        <div class="patient-card">
                            <div class="patient-avatar">
                                <span class="avatar-icon">👤</span>
                            </div>
                            <div class="patient-details">
                                <h4>Maria Garcia</h4>
                                <p class="patient-id">ID: PAT002</p>
                                <p class="last-visit">Last visit: 2 days ago</p>
                                <div class="patient-conditions">
                                    <span class="condition-tag">Asthma</span>
                                </div>
                            </div>
                            <div class="patient-actions">
                                <button class="btn-action btn-primary">View</button>
                                <button class="btn-action btn-outline">Chart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="consultationPopup" class="popup-overlay">
        <div class="popup-content" style="width: 900px;">
            <div class="popup-header">
                <h3>Patient Consultation</h3>
                <button class="popup-close" onclick="closePopup('consultationPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Patient Information</h4>
                        <div class="form-group">
                            <label class="form-label">Patient Name</label>
                            <input type="text" class="form-control" value="John Smith" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Patient ID</label>
                            <input type="text" class="form-control" value="PAT001" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Chief Complaint</label>
                            <textarea class="form-control form-textarea">Headache and fever for 2 days</textarea>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Clinical Notes</h4>
                        <div class="form-group">
                            <label class="form-label">Symptoms</label>
                            <textarea class="form-control form-textarea">Headache, fever (38.5°C), mild cough</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Examination Findings</label>
                            <textarea class="form-control form-textarea" placeholder="Physical examination findings..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('consultationPopup')">Cancel</button>
                    <button class="btn-submit">Save & Complete</button>
                </div>
            </div>
        </div>
    </div>

    <div id="labResultsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>Pending Lab Results</h3>
                <button class="popup-close" onclick="closePopup('labResultsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="max-height: 400px; overflow-y: auto;">
                    <div class="appointment-slot">
                        <div class="slot-info">
                            <h4>Complete Blood Count (CBC)</h4>
                            <p class="patient-id">Patient: John Smith (PAT001)</p>
                            <p class="appointment-reason">Ordered: Today, 09:00 AM | Status: Processing</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-outline">View Details</button>
                        </div>
                    </div>
                    <div class="appointment-slot">
                        <div class="slot-info">
                            <h4>Lipid Panel</h4>
                            <p class="patient-id">Patient: Maria Garcia (PAT002)</p>
                            <p class="appointment-reason">Ordered: Yesterday, 02:30 PM | Status: Ready for Review</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-primary">Review</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="patientChartPopup" class="popup-overlay">
        <div class="popup-content" style="width: 900px;">
            <div class="popup-header">
                <h3>Patient Medical Chart</h3>
                <button class="popup-close" onclick="closePopup('patientChartPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 10px; color: #2d3748;">Patient Details</h4>
                        <p><strong>Name:</strong> John Smith</p>
                        <p><strong>ID:</strong> PAT001</p>
                        <p><strong>Age:</strong> 45</p>
                        <p><strong>Gender:</strong> Male</p>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 10px; color: #2d3748;">Medical History</h4>
                        <div class="patient-conditions">
                            <span class="condition-tag">Hypertension</span>
                            <span class="condition-tag">Diabetes Type 2</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 style="margin-bottom: 10px; color: #2d3748;">Recent Visits</h4>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <div class="appointment-slot">
                            <div class="slot-time">
                                <strong>Today</strong>
                            </div>
                            <div class="slot-info">
                                <p class="appointment-reason">Follow-up: Hypertension Management</p>
                            </div>
                        </div>
                        <div class="appointment-slot">
                            <div class="slot-time">
                                <strong>1 week ago</strong>
                            </div>
                            <div class="slot-info">
                                <p class="appointment-reason">Routine Check-up</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="scheduleAppointmentPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Schedule New Appointment</h3>
                <button class="popup-close" onclick="closePopup('scheduleAppointmentPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <select class="form-control">
                        <option value="">Select Patient</option>
                        <option value="1">John Smith (PAT001)</option>
                        <option value="2">Maria Garcia (PAT002)</option>
                        <option value="3">Robert Johnson (PAT003)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Time</label>
                    <input type="time" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <textarea class="form-control form-textarea" placeholder="Reason for appointment..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('scheduleAppointmentPopup')">Cancel</button>
                    <button class="btn-submit">Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <div id="prescriptionPopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
            <div class="popup-header">
                <h3>Write Prescription</h3>
                <button class="popup-close" onclick="closePopup('prescriptionPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <select class="form-control">
                        <option value="">Select Patient</option>
                        <option value="1">John Smith (PAT001)</option>
                        <option value="2">Maria Garcia (PAT002)</option>
                        <option value="3">Robert Johnson (PAT003)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Medication</label>
                    <input type="text" class="form-control" placeholder="Search medication...">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label class="form-label">Dosage</label>
                        <input type="text" class="form-control" placeholder="e.g., 500mg">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frequency</label>
                        <input type="text" class="form-control" placeholder="e.g., Twice daily">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" placeholder="e.g., 7 days">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Instructions</label>
                    <textarea class="form-control form-textarea" placeholder="Additional instructions..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('prescriptionPopup')">Cancel</button>
                    <button class="btn-submit">Save Prescription</button>
                </div>
            </div>
        </div>
    </div>

    <div id="labTestPopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>Order Lab Test</h3>
                <button class="popup-close" onclick="closePopup('labTestPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <select class="form-control">
                        <option value="">Select Patient</option>
                        <option value="1">John Smith (PAT001)</option>
                        <option value="2">Maria Garcia (PAT002)</option>
                        <option value="3">Robert Johnson (PAT003)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Test Type</label>
                    <select class="form-control">
                        <option value="">Select Test</option>
                        <option value="cbc">Complete Blood Count (CBC)</option>
                        <option value="lipid">Lipid Panel</option>
                        <option value="liver">Liver Function Test</option>
                        <option value="thyroid">Thyroid Panel</option>
                        <option value="urine">Urinalysis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-control">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="stat">STAT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Clinical Notes</label>
                    <textarea class="form-control form-textarea" placeholder="Reason for test..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('labTestPopup')">Cancel</button>
                    <button class="btn-submit">Order Test</button>
                </div>
            </div>
        </div>
    </div>

    <div id="clinicalGuidelinesPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>Clinical Guidelines</h3>
                <button class="popup-close" onclick="closePopup('clinicalGuidelinesPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="max-height: 400px; overflow-y: auto;">
                    <div class="appointment-slot">
                        <div class="slot-info">
                            <h4>Hypertension Management</h4>
                            <p class="appointment-reason">JNC 8 Guidelines for management of high blood pressure in adults</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-primary">View</button>
                        </div>
                    </div>
                    <div class="appointment-slot">
                        <div class="slot-info">
                            <h4>Diabetes Care Standards</h4>
                            <p class="appointment-reason">ADA 2023 Standards of Medical Care in Diabetes</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-primary">View</button>
                        </div>
                    </div>
                    <div class="appointment-slot">
                        <div class="slot-info">
                            <h4>Asthma Treatment Protocol</h4>
                            <p class="appointment-reason">GINA 2023 guidelines for asthma management and prevention</p>
                        </div>
                        <div class="slot-actions">
                            <button class="btn-action btn-primary">View</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="drugInteractionsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
            <div class="popup-header">
                <h3>Drug Interaction Checker</h3>
                <button class="popup-close" onclick="closePopup('drugInteractionsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Medications</label>
                    <textarea class="form-control form-textarea" placeholder="Enter medication names (one per line)"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Patient Conditions</label>
                    <input type="text" class="form-control" placeholder="e.g., Hypertension, Diabetes">
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('drugInteractionsPopup')">Cancel</button>
                    <button class="btn-submit">Check Interactions</button>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f7fafc; border-radius: 6px; display: none;" id="interactionResults">
                    <h4 style="margin-bottom: 10px;">Interaction Results</h4>
                    <p>No significant drug interactions found.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="emrAccessPopup" class="popup-overlay">
        <div class="popup-content" style="width: 900px;">
            <div class="popup-header">
                <h3>Electronic Medical Records</h3>
                <button class="popup-close" onclick="closePopup('emrAccessPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Quick Access</h4>
                        <div class="quick-actions-grid">
                            <button class="quick-action-btn" onclick="openPatientSearchPopup()">
                                <span class="action-icon">🔍</span>
                                <span class="action-text">Patient Search</span>
                            </button>
                            <button class="quick-action-btn" onclick="openRecordsPopup()">
                                <span class="action-icon">📋</span>
                                <span class="action-text">Medical Records</span>
                            </button>
                            <button class="quick-action-btn" onclick="openLabsPopup()">
                                <span class="action-icon">🔬</span>
                                <span class="action-text">Lab Results</span>
                            </button>
                            <button class="quick-action-btn" onclick="openImagingPopup()">
                                <span class="action-icon">🖼️</span>
                                <span class="action-text">Imaging</span>
                            </button>
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Recent Patients</h4>
                        <div style="max-height: 200px; overflow-y: auto;">
                            <div class="appointment-slot">
                                <div class="slot-info">
                                    <h4>John Smith</h4>
                                    <p class="patient-id">ID: PAT001</p>
                                </div>
                                <div class="slot-actions">
                                    <button class="btn-action btn-primary">View</button>
                                </div>
                            </div>
                            <div class="appointment-slot">
                                <div class="slot-info">
                                    <h4>Maria Garcia</h4>
                                    <p class="patient-id">ID: PAT002</p>
                                </div>
                                <div class="slot-actions">
                                    <button class="btn-action btn-primary">View</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="patientDetailsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>Patient Details</h3>
                <button class="popup-close" onclick="closePopup('patientDetailsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Personal Information</h4>
                        <p><strong>Name:</strong> John Smith</p>
                        <p><strong>Patient ID:</strong> PAT001</p>
                        <p><strong>Date of Birth:</strong> 1978-05-15</p>
                        <p><strong>Gender:</strong> Male</p>
                        <p><strong>Phone:</strong> (555) 123-4567</p>
                        <p><strong>Email:</strong> john.smith@example.com</p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Medical Information</h4>
                        <p><strong>Blood Type:</strong> O+</p>
                        <p><strong>Allergies:</strong> Penicillin</p>
                        <p><strong>Primary Care Physician:</strong> Dr. Wilson</p>
                        <div class="patient-conditions" style="margin-top: 10px;">
                            <span class="condition-tag">Hypertension</span>
                            <span class="condition-tag">Diabetes Type 2</span>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('patientDetailsPopup')">Close</button>
                    <button class="btn-submit">Edit Information</button>
                </div>
            </div>
        </div>
    </div>

    <div id="messagePatientPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Message Patient</h3>
                <button class="popup-close" onclick="closePopup('messagePatientPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <input type="text" class="form-control" value="John Smith (PAT001)" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" placeholder="Message subject...">
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-control form-textarea" placeholder="Type your message..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('messagePatientPopup')">Cancel</button>
                    <button class="btn-submit">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <div id="symptomAnalyzerPopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
            <div class="popup-header">
                <h3>AI Symptom Analyzer</h3>
                <button class="popup-close" onclick="closePopup('symptomAnalyzerPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient Symptoms</label>
                    <textarea class="form-control form-textarea" placeholder="Describe the patient's symptoms in detail..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <input type="text" class="form-control" placeholder="How long have symptoms been present?">
                </div>
                <div class="form-group">
                    <label class="form-label">Severity (1-10)</label>
                    <input type="range" class="form-control" min="1" max="10" value="5">
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('symptomAnalyzerPopup')">Cancel</button>
                    <button class="btn-submit" onclick="analyzeSymptoms()">Analyze Symptoms</button>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f7fafc; border-radius: 6px; display: none;" id="symptomResults">
                    <h4 style="margin-bottom: 10px;">AI Analysis Results</h4>
                    <p>Based on the symptoms provided, the AI suggests possible conditions and recommends further evaluation.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="medicationAdvisorPopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
            <div class="popup-header">
                <h3>AI Medication Advisor</h3>
                <button class="popup-close" onclick="closePopup('medicationAdvisorPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient Condition</label>
                    <input type="text" class="form-control" placeholder="e.g., Hypertension, Diabetes, Infection">
                </div>
                <div class="form-group">
                    <label class="form-label">Current Medications</label>
                    <textarea class="form-control form-textarea" placeholder="List current medications..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Allergies</label>
                    <input type="text" class="form-control" placeholder="List any drug allergies...">
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('medicationAdvisorPopup')">Cancel</button>
                    <button class="btn-submit" onclick="getMedicationAdvice()">Get Recommendations</button>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f7fafc; border-radius: 6px; display: none;" id="medicationResults">
                    <h4 style="margin-bottom: 10px;">AI Medication Recommendations</h4>
                    <p>The AI has analyzed the condition and provided medication recommendations based on current guidelines.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="treatmentOptimizerPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>AI Treatment Optimizer</h3>
                <button class="popup-close" onclick="closePopup('treatmentOptimizerPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient Diagnosis</label>
                    <input type="text" class="form-control" placeholder="Enter the primary diagnosis...">
                </div>
                <div class="form-group">
                    <label class="form-label">Current Treatment Plan</label>
                    <textarea class="form-control form-textarea" placeholder="Describe the current treatment approach..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Patient Response</label>
                    <textarea class="form-control form-textarea" placeholder="How has the patient responded to current treatment?"></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('treatmentOptimizerPopup')">Cancel</button>
                    <button class="btn-submit" onclick="optimizeTreatment()">Optimize Treatment</button>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f7fafc; border-radius: 6px; display: none;" id="treatmentResults">
                    <h4 style="margin-bottom: 10px;">AI Treatment Optimization</h4>
                    <p>The AI has analyzed the treatment plan and suggested optimizations based on clinical evidence and patient factors.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Popup Management Functions
    function openPopup(popupId) {
        document.getElementById(popupId).style.display = 'flex';
    }

    function closePopup(popupId) {
        document.getElementById(popupId).style.display = 'none';
    }

    // Close popup when clicking outside content
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('popup-overlay')) {
            event.target.style.display = 'none';
        }
    });

    // Popup Opening Functions
    function openNewConsultationPopup() {
        openPopup('newConsultationPopup');
    }

    function openTodaySchedulePopup() {
        openPopup('todaySchedulePopup');
    }

    function openAppointmentsPopup() {
        openPopup('appointmentsPopup');
    }

    function openPatientsPopup() {
        openPopup('patientsPopup');
    }

    function openConsultationPopup(appointmentId = null) {
        if (appointmentId) {
            // Load appointment data here if needed
            console.log('Loading appointment:', appointmentId);
        }
        openPopup('consultationPopup');
    }

    function openLabResultsPopup() {
        openPopup('labResultsPopup');
    }

    function openPatientChartPopup(patientId) {
        if (patientId) {
            // Load patient chart data here if needed
            console.log('Loading patient chart:', patientId);
        }
        openPopup('patientChartPopup');
    }

    function openScheduleAppointmentPopup() {
        openPopup('scheduleAppointmentPopup');
    }

    function openPrescriptionPopup() {
        openPopup('prescriptionPopup');
    }

    function openLabTestPopup() {
        openPopup('labTestPopup');
    }

    function openClinicalGuidelinesPopup() {
        openPopup('clinicalGuidelinesPopup');
    }

    function openDrugInteractionsPopup() {
        openPopup('drugInteractionsPopup');
    }

    function openEMRAccessPopup() {
        openPopup('emrAccessPopup');
    }

    function openPatientDetailsPopup(patientId) {
        if (patientId) {
            // Load patient details here if needed
            console.log('Loading patient details:', patientId);
        }
        openPopup('patientDetailsPopup');
    }

    function openMessagePatientPopup(patientId) {
        if (patientId) {
            // Set patient for messaging here if needed
            console.log('Messaging patient:', patientId);
        }
        openPopup('messagePatientPopup');
    }

    function openSymptomAnalyzerPopup() {
        openPopup('symptomAnalyzerPopup');
    }

    function openMedicationAdvisorPopup() {
        openPopup('medicationAdvisorPopup');
    }

    function openTreatmentOptimizerPopup() {
        openPopup('treatmentOptimizerPopup');
    }

    // AI Functionality
    function analyzeSymptoms() {
        // Simulate AI analysis
        document.getElementById('symptomResults').style.display = 'block';
    }

    function getMedicationAdvice() {
        // Simulate AI medication advice
        document.getElementById('medicationResults').style.display = 'block';
    }

    function optimizeTreatment() {
        // Simulate AI treatment optimization
        document.getElementById('treatmentResults').style.display = 'block';
    }

    function startConsultation() {
        alert('Consultation started successfully!');
        closePopup('newConsultationPopup');
    }

    // Set doctor availability
    function toggleAvailability() {
        const status = document.querySelector('.availability-status');
        const indicator = status.querySelector('.status-indicator');
        const text = status.querySelector('.status-text');
        
        if (indicator.classList.contains('status-available')) {
            indicator.className = 'status-indicator status-busy';
            text.textContent = 'Busy';
        } else {
            indicator.className = 'status-indicator status-available';
            text.textContent = 'Available';
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>