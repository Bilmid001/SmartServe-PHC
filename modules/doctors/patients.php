<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('doctor') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$doctor_id = $_SESSION['user_id'];

// Get doctor's patients
$query = "SELECT p.*, a.appointment_date, a.status as appointment_status 
          FROM patients p 
          LEFT JOIN appointments a ON p.id = a.patient_id 
          WHERE a.doctor_id = :doctor_id OR a.doctor_id IS NULL
          GROUP BY p.id 
          ORDER BY a.appointment_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$db->table('users')->where('id', 1)->update([
    ''
])

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients - PHCHMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Content Header */
        .content-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 30px !important;
            padding: 20px 0 !important;
            border-bottom: 1px solid #e1e5eb !important;
        }

        .content-header h2 {
            font-size: 28px !important;
            font-weight: 700 !important;
            color: #1a365d !important;
            margin-bottom: 5px !important;
        }

        .breadcrumb {
            color: #718096 !important;
            font-size: 14px !important;
        }

        .breadcrumb span {
            color: #4299e1 !important;
        }

        /* Dashboard Sections */
        .dashboard-section {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
            padding: 25px !important;
            margin-bottom: 25px !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }

        .dashboard-section:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .dashboard-section h3 {
            color: #2d3748 !important;
            font-size: 22px !important;
            font-weight: 600 !important;
            margin-bottom: 20px !important;
            border-bottom: 2px solid #e1e5eb !important;
            padding-bottom: 10px !important;
        }

        /* Appointments List */
        .appointments-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
        }

        .appointment-card {
            display: flex !important;
            align-items: center !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            border-left: 4px solid #4299e1 !important;
            transition: all 0.3s ease !important;
        }

        .appointment-card:hover {
            background: #edf2f7 !important;
            transform: translateX(5px) !important;
        }

        .appointment-time {
            min-width: 100px !important;
            text-align: center !important;
            padding-right: 15px !important;
            border-right: 1px solid #e1e5eb !important;
        }

        .appointment-time strong {
            color: #2d3748 !important;
            font-size: 16px !important;
        }

        .appointment-info {
            flex: 1 !important;
            padding: 0 15px !important;
        }

        .appointment-info h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .appointment-info p {
            color: #718096 !important;
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }

        .appointment-actions {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            text-transform: capitalize !important;
        }

        .status-scheduled {
            background: #e6fffa !important;
            color: #234e52 !important;
            border: 1px solid #81e6d9 !important;
        }

        .status-completed {
            background: #f0fff4 !important;
            color: #22543d !important;
            border: 1px solid #9ae6b4 !important;
        }

        .status-cancelled {
            background: #fed7d7 !important;
            color: #742a2a !important;
            border: 1px solid #feb2b2 !important;
        }

        .status-new {
            background: #ebf8ff !important;
            color: #1a365d !important;
            border: 1px solid #90cdf4 !important;
        }

        /* Buttons */
        .btn-sm {
            padding: 6px 12px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
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

        .btn-secondary {
            background: #718096 !important;
            color: white !important;
        }

        .btn-secondary:hover {
            background: #4a5568 !important;
            transform: translateY(-2px) !important;
        }

        .btn-info {
            background: #0bc5ea !important;
            color: white !important;
        }

        .btn-info:hover {
            background: #00b5d8 !important;
            transform: translateY(-2px) !important;
        }

        .btn-outline {
            background: transparent !important;
            color: #718096 !important;
            border: 1px solid #e1e5eb !important;
        }

        .btn-outline:hover {
            background: #f7fafc !important;
            color: #4299e1 !important;
            border-color: #4299e1 !important;
            transform: translateY(-2px) !important;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
        }

        .data-table {
            width: 100% !important;
            border-collapse: collapse !important;
            background: white !important;
        }

        .data-table thead {
            background: #f7fafc !important;
            border-bottom: 2px solid #e1e5eb !important;
        }

        .data-table th {
            padding: 12px 15px !important;
            text-align: left !important;
            color: #2d3748 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }

        .data-table td {
            padding: 12px 15px !important;
            border-bottom: 1px solid #e1e5eb !important;
            color: #4a5568 !important;
            font-size: 14px !important;
        }

        .data-table tbody tr {
            transition: background 0.3s ease !important;
        }

        .data-table tbody tr:hover {
            background: #f7fafc !important;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .upcoming-appointment {
            color: #38a169 !important;
            font-weight: 600 !important;
            font-size: 13px !important;
        }

        /* AI Clinical Support */
        .ai-clinical-support {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 20px !important;
        }

        .support-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
        }

        .support-card:hover {
            background: #edf2f7 !important;
            border-color: #4299e1 !important;
            transform: translateY(-5px) !important;
        }

        .support-card h4 {
            color: #2d3748 !important;
            margin-bottom: 10px !important;
            font-size: 18px !important;
        }

        .support-card p {
            color: #718096 !important;
            margin-bottom: 15px !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
        }

        /* Popup Overlay */
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

        /* Form Styles */
        .form-group {
            margin-bottom: 15px !important;
        }

        .form-label {
            display: block !important;
            margin-bottom: 5px !important;
            color: #4a5568 !important;
            font-weight: 600 !important;
        }

        .form-control {
            width: 100% !important;
            padding: 10px !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            transition: border-color 0.3s ease !important;
        }

        .form-control:focus {
            outline: none !important;
            border-color: #4299e1 !important;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1) !important;
        }

        .form-textarea {
            min-height: 100px !important;
            resize: vertical !important;
        }

        .form-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 10px !important;
            margin-top: 20px !important;
            padding-top: 15px !important;
            border-top: 1px solid #e1e5eb !important;
        }

        .btn-cancel {
            background: #718096 !important;
            color: white !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: background 0.3s ease !important;
        }

        .btn-cancel:hover {
            background: #4a5568 !important;
        }

        .btn-submit {
            background: #4299e1 !important;
            color: white !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: background 0.3s ease !important;
        }

        .btn-submit:hover {
            background: #3182ce !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
            
            .content-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }
            
            .appointment-card {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            .appointment-time {
                border-right: none !important;
                border-bottom: 1px solid #e1e5eb !important;
                padding-right: 0 !important;
                padding-bottom: 10px !important;
                margin-bottom: 10px !important;
                width: 100% !important;
                text-align: left !important;
            }
            
            .appointment-actions {
                width: 100% !important;
                justify-content: flex-end !important;
                margin-top: 10px !important;
            }
            
            .ai-clinical-support {
                grid-template-columns: 1fr !important;
            }
            
            .data-table {
                font-size: 12px !important;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 10px !important;
            }
        }

        /* No Data States */
        .no-data {
            text-align: center !important;
            padding: 40px 20px !important;
            color: #718096 !important;
        }

        .no-data-icon {
            font-size: 48px !important;
            margin-bottom: 15px !important;
            opacity: 0.5 !important;
        }

        .no-data h4 {
            color: #4a5568 !important;
            margin-bottom: 10px !important;
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h2>My Patients</h2>
                <div class="breadcrumb">
                    <span>Doctor</span> / <span>Patients</span>
                </div>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="dashboard-section">
            <h3>Today's Appointments</h3>
            <div class="appointments-list">
                <?php
                $query = "SELECT a.*, p.full_name, p.patient_id 
                          FROM appointments a 
                          JOIN patients p ON a.patient_id = p.id 
                          WHERE a.doctor_id = :doctor_id 
                          AND DATE(a.appointment_date) = CURDATE() 
                          ORDER BY a.appointment_date";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':doctor_id', $doctor_id);
                $stmt->execute();
                $today_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($today_appointments) > 0): 
                    foreach ($today_appointments as $appointment): ?>
                    <div class="appointment-card">
                        <div class="appointment-time">
                            <strong><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></strong>
                        </div>
                        <div class="appointment-info">
                            <h4><?php echo $appointment['full_name']; ?></h4>
                            <p>Patient ID: <?php echo $appointment['patient_id']; ?></p>
                            <p>Reason: <?php echo $appointment['reason']; ?></p>
                        </div>
                        <div class="appointment-actions">
                            <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                <?php echo $appointment['status']; ?>
                            </span>
                            <button class="btn-sm btn-primary" onclick="openConsultationPopup(<?php echo $appointment['id']; ?>)">
                                Start Consultation
                            </button>
                        </div>
                    </div>
                    <?php endforeach; 
                else: ?>
                    <div class="no-data">
                        <div class="no-data-icon">📅</div>
                        <h4>No Appointments Today</h4>
                        <p>You have no appointments scheduled for today.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Patient List -->
        <div class="dashboard-section">
            <h3>My Patient List</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Full Name</th>
                            <th>Last Visit</th>
                            <th>Next Appointment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($patients) > 0): ?>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><strong><?php echo $patient['patient_id']; ?></strong></td>
                                <td><?php echo $patient['full_name']; ?></td>
                                <td><?php echo $patient['appointment_date'] ? date('M j, Y', strtotime($patient['appointment_date'])) : 'Never'; ?></td>
                                <td>
                                    <?php if ($patient['appointment_status'] === 'scheduled'): ?>
                                        <span class="upcoming-appointment">Upcoming</span>
                                    <?php else: ?>
                                        <button class="btn-sm btn-outline" onclick="openScheduleAppointmentPopup(<?php echo $patient['id']; ?>)">
                                            Schedule
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $patient['appointment_status'] ?? 'new'; ?>">
                                        <?php echo $patient['appointment_status'] ?? 'New'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-sm btn-primary" onclick="openPatientDetailsPopup(<?php echo $patient['id']; ?>)">
                                        View
                                    </button>
                                    <button class="btn-sm btn-secondary" onclick="openClinicalNotePopup(<?php echo $patient['id']; ?>)">
                                        Add Note
                                    </button>
                                    <button class="btn-sm btn-info" onclick="openLabTestPopup(<?php echo $patient['id']; ?>)">
                                        Lab Test
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="no-data">
                                        <div class="no-data-icon">👥</div>
                                        <h4>No Patients Found</h4>
                                        <p>You don't have any patients assigned yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AI Clinical Support -->
        <div class="dashboard-section">
            <h3>AI Clinical Support</h3>
            <div class="ai-clinical-support">
                <div class="support-card">
                    <h4>Symptom Checker</h4>
                    <p>Get AI-powered diagnostic support based on patient symptoms.</p>
                    <button class="btn-primary" onclick="openSymptomCheckerPopup()">Open Symptom Checker</button>
                </div>
                <div class="support-card">
                    <h4>Drug Interactions</h4>
                    <p>Check for potential drug interactions and contraindications.</p>
                    <button class="btn-primary" onclick="openDrugInteractionsPopup()">Check Interactions</button>
                </div>
                <div class="support-card">
                    <h4>Treatment Guidelines</h4>
                    <p>Access evidence-based treatment guidelines and protocols.</p>
                    <button class="btn-primary" onclick="openGuidelinesPopup()">View Guidelines</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Overlays -->
    <div id="consultationPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>Start Consultation</h3>
                <button class="popup-close" onclick="closePopup('consultationPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient Information</label>
                    <input type="text" class="form-control" value="John Smith (PAT001)" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Chief Complaint</label>
                    <textarea class="form-control form-textarea" placeholder="Enter the patient's main concern..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Vital Signs</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="text" class="form-control" placeholder="BP (e.g., 120/80)">
                        <input type="text" class="form-control" placeholder="Pulse">
                        <input type="text" class="form-control" placeholder="Temperature">
                        <input type="text" class="form-control" placeholder="Respiratory Rate">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('consultationPopup')">Cancel</button>
                    <button class="btn-submit" onclick="startConsultation()">Start Consultation</button>
                </div>
            </div>
        </div>
    </div>

    <div id="patientDetailsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
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
                        <p><strong>Date of Birth:</strong> 1985-03-15</p>
                        <p><strong>Gender:</strong> Male</p>
                        <p><strong>Phone:</strong> (555) 123-4567</p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Medical Information</h4>
                        <p><strong>Blood Type:</strong> O+</p>
                        <p><strong>Allergies:</strong> Penicillin</p>
                        <p><strong>Conditions:</strong> Hypertension, Diabetes</p>
                        <p><strong>Last Visit:</strong> 2024-01-15</p>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('patientDetailsPopup')">Close</button>
                    <button class="btn-submit">Edit Information</button>
                </div>
            </div>
        </div>
    </div>

    <div id="clinicalNotePopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>Add Clinical Note</h3>
                <button class="popup-close" onclick="closePopup('clinicalNotePopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <input type="text" class="form-control" value="John Smith (PAT001)" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Note Type</label>
                    <select class="form-control">
                        <option value="progress">Progress Note</option>
                        <option value="consultation">Consultation Note</option>
                        <option value="discharge">Discharge Summary</option>
                        <option value="procedure">Procedure Note</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Clinical Notes</label>
                    <textarea class="form-control form-textarea" placeholder="Enter clinical notes..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('clinicalNotePopup')">Cancel</button>
                    <button class="btn-submit">Save Note</button>
                </div>
            </div>
        </div>
    </div>

    <div id="labTestPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Order Lab Test</h3>
                <button class="popup-close" onclick="closePopup('labTestPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <input type="text" class="form-control" value="John Smith (PAT001)" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Test Type</label>
                    <select class="form-control">
                        <option value="">Select Test</option>
                        <option value="cbc">Complete Blood Count</option>
                        <option value="lipid">Lipid Panel</option>
                        <option value="liver">Liver Function Test</option>
                        <option value="thyroid">Thyroid Panel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-control">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('labTestPopup')">Cancel</button>
                    <button class="btn-submit">Order Test</button>
                </div>
            </div>
        </div>
    </div>

    <div id="scheduleAppointmentPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Schedule Appointment</h3>
                <button class="popup-close" onclick="closePopup('scheduleAppointmentPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <input type="text" class="form-control" value="John Smith (PAT001)" readonly>
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

    <div id="symptomCheckerPopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>AI Symptom Checker</h3>
                <button class="popup-close" onclick="closePopup('symptomCheckerPopup')">&times;</button>
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
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('symptomCheckerPopup')">Cancel</button>
                    <button class="btn-submit" onclick="analyzeSymptoms()">Analyze Symptoms</button>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f7fafc; border-radius: 6px; display: none;" id="symptomResults">
                    <h4 style="margin-bottom: 10px;">AI Analysis Results</h4>
                    <p>Based on the symptoms provided, the AI suggests possible conditions and recommends further evaluation.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
    function openConsultationPopup(appointmentId) {
        if (appointmentId) {
            console.log('Loading appointment:', appointmentId);
        }
        openPopup('consultationPopup');
    }

    function openPatientDetailsPopup(patientId) {
        if (patientId) {
            console.log('Loading patient details:', patientId);
        }
        openPopup('patientDetailsPopup');
    }

    function openClinicalNotePopup(patientId) {
        if (patientId) {
            console.log('Adding clinical note for patient:', patientId);
        }
        openPopup('clinicalNotePopup');
    }

    function openLabTestPopup(patientId) {
        if (patientId) {
            console.log('Ordering lab test for patient:', patientId);
        }
        openPopup('labTestPopup');
    }

    function openScheduleAppointmentPopup(patientId) {
        if (patientId) {
            console.log('Scheduling appointment for patient:', patientId);
        }
        openPopup('scheduleAppointmentPopup');
    }

    function openSymptomCheckerPopup() {
        openPopup('symptomCheckerPopup');
    }

    function openDrugInteractionsPopup() {
        alert('Opening Drug Interaction Checker...');
    }

    function openGuidelinesPopup() {
        alert('Opening Treatment Guidelines...');
    }

    // AI Functionality
    function analyzeSymptoms() {
        document.getElementById('symptomResults').style.display = 'block';
    }

    function startConsultation() {
        alert('Consultation started successfully!');
        closePopup('consultationPopup');
    }
    </script>
</body>
</html>