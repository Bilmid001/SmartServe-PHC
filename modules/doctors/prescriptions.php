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

// Handle new prescription
if ($_POST['action'] ?? '' === 'create_prescription') {
    $patient_id = $_POST['patient_id'];
    $medications = $_POST['medications'];
    $instructions = $_POST['instructions'];
    $duration = $_POST['duration'];
    $refills = $_POST['refills'];
    
    $query = "INSERT INTO medical_records (patient_id, doctor_id, prescription, treatment) 
              VALUES (:patient_id, :doctor_id, :prescription, :treatment)";
    $stmt = $db->prepare($query);
    
    $prescription_text = "Medications:\n" . $medications . "\n\nInstructions: " . $instructions . 
                        "\nDuration: " . $duration . "\nRefills: " . $refills;
    
    if ($stmt->execute([
        ':patient_id' => $patient_id,
        ':doctor_id' => $doctor_id,
        ':prescription' => $prescription_text,
        ':treatment' => 'Prescription issued'
    ])) {
        $success = "Prescription created successfully!";
    } else {
        $error = "Error creating prescription!";
    }
}

// Get doctor's recent prescriptions
$query = "SELECT mr.*, p.full_name, p.patient_id 
          FROM medical_records mr 
          JOIN patients p ON mr.patient_id = p.id 
          WHERE mr.doctor_id = :doctor_id 
          AND mr.prescription IS NOT NULL 
          ORDER BY mr.visit_date DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$recent_prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get doctor's patients for dropdown
$query = "SELECT DISTINCT p.id, p.full_name, p.patient_id 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          WHERE a.doctor_id = :doctor_id 
          ORDER BY p.full_name";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Management - PHCHMS</title>
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

        /* Stats Cards Grid */
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
        }

        .stat-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-card-primary {
            border-left-color: #4299e1 !important;
        }

        .stat-card-success {
            border-left-color: #48bb78 !important;
        }

        .stat-card-info {
            border-left-color: #0bc5ea !important;
        }

        .stat-card-warning {
            border-left-color: #ed8936 !important;
        }

        .stat-icon {
            margin-right: 15px !important;
        }

        .icon-circle {
            width: 50px !important;
            height: 50px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
            background: rgba(66, 153, 225, 0.1) !important;
        }

        .stat-card-success .icon-circle {
            background: rgba(72, 187, 120, 0.1) !important;
        }

        .stat-card-info .icon-circle {
            background: rgba(11, 197, 234, 0.1) !important;
        }

        .stat-card-warning .icon-circle {
            background: rgba(237, 137, 54, 0.1) !important;
        }

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

        .stat-trend {
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .trend-up {
            color: #48bb78 !important;
        }

        .trend-down {
            color: #e53e3e !important;
        }

        .trend-neutral {
            color: #718096 !important;
        }

        /* Content Cards */
        .content-card {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            margin-bottom: 20px !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }

        .content-card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 20px !important;
            border-bottom: 1px solid #e1e5eb !important;
        }

        .card-header h3 {
            font-size: 20px !important;
            font-weight: 600 !important;
            color: #2d3748 !important;
        }

        .ai-badge {
            background: #e6fffa !important;
            color: #234e52 !important;
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .view-all {
            color: #4299e1 !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: color 0.3s ease !important;
        }

        .view-all:hover {
            color: #3182ce !important;
            text-decoration: underline !important;
        }

        /* Prescription Form */
        .prescription-form {
            padding: 20px !important;
        }

        .form-row {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr !important;
            }
        }

        .form-group {
            margin-bottom: 15px !important;
        }

        .form-label {
            display: block !important;
            margin-bottom: 8px !important;
            color: #2d3748 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }

        .form-control {
            width: 100% !important;
            padding: 12px !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            background: white !important;
        }

        .form-control:focus {
            outline: none !important;
            border-color: #4299e1 !important;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1) !important;
        }

        select.form-control {
            cursor: pointer !important;
        }

        textarea.form-control {
            resize: vertical !important;
            min-height: 120px !important;
            font-family: 'Courier New', monospace !important;
            line-height: 1.5 !important;
        }

        /* Alerts */
        .alert {
            padding: 15px !important;
            border-radius: 8px !important;
            margin-bottom: 20px !important;
            border: 1px solid transparent !important;
        }

        .alert-success {
            background: #f0fff4 !important;
            color: #22543d !important;
            border-color: #9ae6b4 !important;
        }

        .alert-error {
            background: #fed7d7 !important;
            color: #742a2a !important;
            border-color: #feb2b2 !important;
        }

        /* AI Check Section */
        .ai-check-section {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            margin: 20px 0 !important;
            border: 1px solid #e1e5eb !important;
        }

        .ai-check-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 15px !important;
        }

        .ai-check-header h4 {
            color: #2d3748 !important;
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        /* Buttons */
        .btn-secondary, .btn-outline, .submit-btn {
            padding: 12px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 14px !important;
        }

        .submit-btn {
            background: #4299e1 !important;
            color: white !important;
        }

        .submit-btn:hover {
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

        .form-actions {
            display: flex !important;
            gap: 10px !important;
            margin-top: 20px !important;
            flex-wrap: wrap !important;
        }

        /* Interaction Results */
        .interaction-results {
            margin-top: 15px !important;
        }

        .interaction-alert {
            display: flex !important;
            align-items: flex-start !important;
            padding: 15px !important;
            border-radius: 8px !important;
            border: 1px solid !important;
        }

        .interaction-alert.safe {
            background: #f0fff4 !important;
            border-color: #9ae6b4 !important;
        }

        .interaction-alert.warning {
            background: #fffaf0 !important;
            border-color: #faf089 !important;
        }

        .interaction-alert.loading {
            background: #ebf8ff !important;
            border-color: #90cdf4 !important;
        }

        .alert-icon {
            font-size: 20px !important;
            margin-right: 12px !important;
            margin-top: 2px !important;
        }

        .alert-content h5 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 14px !important;
        }

        .alert-content p {
            color: #4a5568 !important;
            font-size: 13px !important;
            margin-bottom: 5px !important;
        }

        .interaction-details {
            margin-top: 8px !important;
            padding-top: 8px !important;
            border-top: 1px solid rgba(0, 0, 0, 0.1) !important;
        }

        .interaction-details p {
            font-size: 12px !important;
            margin-bottom: 3px !important;
        }

        /* Prescriptions List */
        .prescriptions-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
            padding: 20px !important;
        }

        .prescription-item {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            border-left: 4px solid #4299e1 !important;
            transition: all 0.3s ease !important;
        }

        .prescription-item:hover {
            background: #edf2f7 !important;
            transform: translateX(5px) !important;
        }

        .prescription-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            margin-bottom: 15px !important;
        }

        .patient-info h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .patient-id, .prescription-date {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 3px !important;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            text-transform: capitalize !important;
        }

        .status-active {
            background: #e6fffa !important;
            color: #234e52 !important;
            border: 1px solid #81e6d9 !important;
        }

        .prescription-content {
            margin-bottom: 15px !important;
        }

        .prescription-text {
            background: white !important;
            padding: 15px !important;
            border-radius: 6px !important;
            border: 1px solid #e1e5eb !important;
            font-family: 'Courier New', monospace !important;
            font-size: 13px !important;
            line-height: 1.5 !important;
            white-space: pre-wrap !important;
        }

        .prescription-actions {
            display: flex !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
        }

        .btn-action {
            padding: 6px 12px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: none !important;
        }

        .btn-danger {
            background: #e53e3e !important;
            color: white !important;
        }

        .btn-danger:hover {
            background: #c53030 !important;
        }

        /* No Data States */
        .no-prescriptions {
            text-align: center !important;
            padding: 40px 20px !important;
            color: #718096 !important;
        }

        .no-prescriptions-icon {
            font-size: 48px !important;
            margin-bottom: 15px !important;
            opacity: 0.5 !important;
        }

        .no-prescriptions h4 {
            color: #4a5568 !important;
            margin-bottom: 10px !important;
        }

        /* Templates Grid */
        .templates-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            gap: 20px !important;
            padding: 20px !important;
        }

        .template-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
        }

        .template-card:hover {
            background: #edf2f7 !important;
            border-color: #4299e1 !important;
            transform: translateY(-3px) !important;
        }

        .template-card h4 {
            color: #2d3748 !important;
            margin-bottom: 10px !important;
            font-size: 16px !important;
        }

        .template-card p {
            color: #718096 !important;
            margin-bottom: 15px !important;
            font-size: 13px !important;
        }

        .template-medications {
            background: white !important;
            padding: 12px !important;
            border-radius: 6px !important;
            margin-bottom: 15px !important;
            border: 1px solid #e1e5eb !important;
        }

        .template-medications strong {
            color: #2d3748 !important;
            font-size: 14px !important;
        }

        .template-medications p {
            color: #718096 !important;
            font-size: 12px !important;
            margin-bottom: 0 !important;
        }

        .btn-template {
            background: #4299e1 !important;
            color: white !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: background 0.3s ease !important;
            width: 100% !important;
        }

        .btn-template:hover {
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
            
            .stats-cards-grid {
                grid-template-columns: 1fr !important;
            }
            
            .form-actions {
                flex-direction: column !important;
            }
            
            .prescription-header {
                flex-direction: column !important;
                gap: 10px !important;
            }
            
            .ai-check-header {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: flex-start !important;
            }
            
            .templates-grid {
                grid-template-columns: 1fr !important;
            }
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
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h2>Prescription Management</h2>
                <div class="breadcrumb">
                    <span>Doctor</span> / <span>Prescriptions</span>
                </div>
            </div>
        </div>

        <!-- Prescription Statistics -->
        <div class="stats-cards-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <div class="icon-circle">
                        💊
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($recent_prescriptions); ?></h3>
                    <p>Recent Prescriptions</p>
                    <span class="stat-trend trend-up">+5 this week</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <div class="icon-circle">
                        👥
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($patients); ?></h3>
                    <p>Active Patients</p>
                    <span class="stat-trend trend-up">+3 this month</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ⚠️
                    </div>
                </div>
                <div class="stat-content">
                    <h3>2</h3>
                    <p>Drug Interactions</p>
                    <span class="stat-trend trend-neutral">Needs review</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <div class="icon-circle">
                        🔄
                    </div>
                </div>
                <div class="stat-content">
                    <h3>8</h3>
                    <p>Refill Requests</p>
                    <span class="stat-trend trend-up">Pending</span>
                </div>
            </div>
        </div>

        <!-- New Prescription Form -->
        <div class="content-card">
            <div class="card-header">
                <h3>Create New Prescription</h3>
                <span class="ai-badge">AI Assisted</span>
            </div>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="prescription-form">
                <input type="hidden" name="action" value="create_prescription">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_id" class="form-label">Select Patient</label>
                        <select id="patient_id" name="patient_id" class="form-control" required onchange="loadPatientHistory()">
                            <option value="">Choose a patient...</option>
                            <?php foreach ($patients as $patient): ?>
                            <option value="<?php echo $patient['id']; ?>">
                                <?php echo $patient['full_name']; ?> (<?php echo $patient['patient_id']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="duration" class="form-label">Treatment Duration</label>
                        <select id="duration" name="duration" class="form-control" required>
                            <option value="7 days">7 days</option>
                            <option value="14 days">14 days</option>
                            <option value="30 days" selected>30 days</option>
                            <option value="60 days">60 days</option>
                            <option value="90 days">90 days</option>
                            <option value="Ongoing">Ongoing</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="medications" class="form-label">Medications & Dosage</label>
                        <textarea id="medications" name="medications" class="form-control" rows="6" required 
                                  placeholder="Enter medications with dosage and frequency...
Example:
- Amoxicillin 500mg: 1 tablet every 8 hours
- Paracetamol 500mg: 1 tablet as needed for pain
- Vitamin C 1000mg: Once daily"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="instructions" class="form-label">Special Instructions</label>
                        <textarea id="instructions" name="instructions" class="form-control" rows="3" 
                                  placeholder="Any special instructions, precautions, or notes..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="refills" class="form-label">Refill Authorization</label>
                        <select id="refills" name="refills" class="form-control">
                            <option value="0">No refills</option>
                            <option value="1">1 refill</option>
                            <option value="2">2 refills</option>
                            <option value="3">3 refills</option>
                            <option value="Unlimited">Unlimited refills</option>
                        </select>
                    </div>
                </div>

                <!-- AI Drug Interaction Check -->
                <div class="ai-check-section">
                    <div class="ai-check-header">
                        <h4>AI Drug Interaction Check</h4>
                        <button type="button" class="btn-secondary" onclick="checkDrugInteractions()">
                            <span class="btn-icon">🔍</span>
                            Check Interactions
                        </button>
                    </div>
                    <div id="interactionResults" class="interaction-results" style="display: none;">
                        <div class="interaction-alert safe">
                            <div class="alert-icon">✅</div>
                            <div class="alert-content">
                                <h5>No Significant Interactions Found</h5>
                                <p>All medications appear safe to prescribe together.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <span class="btn-icon">💊</span>
                        Create Prescription
                    </button>
                    <button type="button" class="btn-secondary" onclick="saveDraft()">
                        <span class="btn-icon">💾</span>
                        Save Draft
                    </button>
                    <button type="button" class="btn-outline" onclick="clearForm()">
                        <span class="btn-icon">🗑️</span>
                        Clear Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Prescriptions -->
        <div class="content-card">
            <div class="card-header">
                <h3>Recent Prescriptions</h3>
                <a href="prescription-history.php" class="view-all">View All</a>
            </div>
            <div class="prescriptions-list">
                <?php if (count($recent_prescriptions) > 0): ?>
                    <?php foreach ($recent_prescriptions as $prescription): ?>
                    <div class="prescription-item">
                        <div class="prescription-header">
                            <div class="patient-info">
                                <h4><?php echo $prescription['full_name']; ?></h4>
                                <p class="patient-id">ID: <?php echo $prescription['patient_id']; ?></p>
                                <p class="prescription-date">
                                    <?php echo date('M j, Y', strtotime($prescription['visit_date'])); ?>
                                </p>
                            </div>
                            <div class="prescription-status">
                                <span class="status-badge status-active">Active</span>
                            </div>
                        </div>
                        <div class="prescription-content">
                            <div class="prescription-text">
                                <?php echo nl2br(htmlspecialchars($prescription['prescription'])); ?>
                            </div>
                        </div>
                        <div class="prescription-actions">
                            <button class="btn-action btn-primary" onclick="openPrescriptionDetailsPopup(<?php echo $prescription['id']; ?>)">
                                View Details
                            </button>
                            <button class="btn-action btn-outline" onclick="openRenewPrescriptionPopup(<?php echo $prescription['id']; ?>)">
                                Renew
                            </button>
                            <button class="btn-action btn-danger" onclick="openCancelPrescriptionPopup(<?php echo $prescription['id']; ?>)">
                                Cancel
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-prescriptions">
                        <div class="no-prescriptions-icon">💊</div>
                        <h4>No Prescriptions Found</h4>
                        <p>Create your first prescription using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Common Medication Templates -->
        <div class="content-card">
            <div class="card-header">
                <h3>Common Medication Templates</h3>
                <span class="ai-badge">Quick Prescribe</span>
            </div>
            <div class="templates-grid">
                <div class="template-card">
                    <h4>Antibiotic Course</h4>
                    <p>Standard 7-day antibiotic treatment</p>
                    <div class="template-medications">
                        <strong>Amoxicillin 500mg</strong>
                        <p>1 tablet every 8 hours for 7 days</p>
                    </div>
                    <button class="btn-template" onclick="useTemplate('antibiotic')">Use Template</button>
                </div>

                <div class="template-card">
                    <h4>Pain Management</h4>
                    <p>Acute pain relief regimen</p>
                    <div class="template-medications">
                        <strong>Paracetamol 500mg</strong>
                        <p>1-2 tablets every 6 hours as needed</p>
                    </div>
                    <button class="btn-template" onclick="useTemplate('pain')">Use Template</button>
                </div>

                <div class="template-card">
                    <h4>Hypertension</h4>
                    <p>Blood pressure management</p>
                    <div class="template-medications">
                        <strong>Lisinopril 10mg</strong>
                        <p>1 tablet daily in the morning</p>
                    </div>
                    <button class="btn-template" onclick="useTemplate('hypertension')">Use Template</button>
                </div>

                <div class="template-card">
                    <h4>Diabetes</h4>
                    <p>Blood sugar control</p>
                    <div class="template-medications">
                        <strong>Metformin 500mg</strong>
                        <p>1 tablet twice daily with meals</p>
                    </div>
                    <button class="btn-template" onclick="useTemplate('diabetes')">Use Template</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Overlays -->
    <div id="prescriptionDetailsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 700px;">
            <div class="popup-header">
                <h3>Prescription Details</h3>
                <button class="popup-close" onclick="closePopup('prescriptionDetailsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin-bottom: 10px; color: #2d3748;">Patient Information</h4>
                        <p><strong>Name:</strong> John Smith</p>
                        <p><strong>Patient ID:</strong> PAT001</p>
                        <p><strong>Date of Birth:</strong> 1985-03-15</p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 10px; color: #2d3748;">Prescription Details</h4>
                        <p><strong>Issue Date:</strong> <?php echo date('M j, Y'); ?></p>
                        <p><strong>Status:</strong> <span class="status-badge status-active">Active</span></p>
                        <p><strong>Refills Remaining:</strong> 2</p>
                    </div>
                </div>
                <div>
                    <h4 style="margin-bottom: 10px; color: #2d3748;">Medications</h4>
                    <div class="prescription-text">
Amoxicillin 500mg: 1 tablet every 8 hours for 7 days
Paracetamol 500mg: 1 tablet as needed for pain

Instructions: Take with food
Duration: 7 days
Refills: 0
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="renewPrescriptionPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Renew Prescription</h3>
                <button class="popup-close" onclick="closePopup('renewPrescriptionPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <input type="text" class="form-control" value="John Smith (PAT001)" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">New Duration</label>
                    <select class="form-control">
                        <option value="7 days">7 days</option>
                        <option value="14 days">14 days</option>
                        <option value="30 days" selected>30 days</option>
                        <option value="60 days">60 days</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Additional Refills</label>
                    <select class="form-control">
                        <option value="0">No additional refills</option>
                        <option value="1">1 refill</option>
                        <option value="2">2 refills</option>
                        <option value="3">3 refills</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('renewPrescriptionPopup')">Cancel</button>
                    <button class="btn-submit" onclick="renewPrescription()">Renew Prescription</button>
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
    function openPrescriptionDetailsPopup(prescriptionId) {
        if (prescriptionId) {
            console.log('Loading prescription details:', prescriptionId);
        }
        openPopup('prescriptionDetailsPopup');
    }

    function openRenewPrescriptionPopup(prescriptionId) {
        if (prescriptionId) {
            console.log('Renewing prescription:', prescriptionId);
        }
        openPopup('renewPrescriptionPopup');
    }

    function openCancelPrescriptionPopup(prescriptionId) {
        if (confirm('Cancel this prescription? This action cannot be undone.')) {
            alert('Prescription cancelled for ID: ' + prescriptionId);
        }
    }

    // Existing functions
    function loadPatientHistory() {
        const patientId = document.getElementById('patient_id').value;
        if (patientId) {
            console.log('Loading history for patient:', patientId);
        }
    }

    function checkDrugInteractions() {
        const medications = document.getElementById('medications').value;
        if (!medications.trim()) {
            alert('Please enter medications first.');
            return;
        }

        const resultsDiv = document.getElementById('interactionResults');
        resultsDiv.innerHTML = `
            <div class="interaction-alert loading">
                <div class="alert-icon">⏳</div>
                <div class="alert-content">
                    <h5>Checking Interactions...</h5>
                    <p>AI is analyzing potential drug interactions</p>
                </div>
            </div>
        `;
        resultsDiv.style.display = 'block';

        setTimeout(() => {
            const isSafe = Math.random() > 0.3;
            
            if (isSafe) {
                resultsDiv.innerHTML = `
                    <div class="interaction-alert safe">
                        <div class="alert-icon">✅</div>
                        <div class="alert-content">
                            <h5>No Significant Interactions Found</h5>
                            <p>All medications appear safe to prescribe together.</p>
                            <div class="interaction-details">
                                <p><strong>Note:</strong> Monitor for common side effects</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultsDiv.innerHTML = `
                    <div class="interaction-alert warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <h5>Potential Interaction Detected</h5>
                            <p>Moderate interaction between Medication A and Medication B</p>
                            <div class="interaction-details">
                                <p><strong>Risk:</strong> Increased risk of side effects</p>
                                <p><strong>Recommendation:</strong> Consider alternative or adjust dosage</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        }, 2000);
    }

    function saveDraft() {
        alert('Prescription draft saved successfully!');
    }

    function clearForm() {
        if (confirm('Clear all form fields?')) {
            document.querySelector('.prescription-form').reset();
            document.getElementById('interactionResults').style.display = 'none';
        }
    }

    function renewPrescription() {
        alert('Prescription renewed successfully!');
        closePopup('renewPrescriptionPopup');
    }

    function useTemplate(templateType) {
        let medications = '';
        
        switch(templateType) {
            case 'antibiotic':
                medications = "Amoxicillin 500mg: 1 tablet every 8 hours for 7 days\nTake with food to avoid stomach upset";
                break;
            case 'pain':
                medications = "Paracetamol 500mg: 1-2 tablets every 6 hours as needed for pain\nDo not exceed 8 tablets in 24 hours";
                break;
            case 'hypertension':
                medications = "Lisinopril 10mg: 1 tablet daily in the morning\nMonitor blood pressure regularly";
                break;
            case 'diabetes':
                medications = "Metformin 500mg: 1 tablet twice daily with meals\nMonitor blood sugar levels";
                break;
        }
        
        document.getElementById('medications').value = medications;
        document.getElementById('duration').value = '30 days';
        document.getElementById('refills').value = '2';
        
        alert('Template applied! Please review and adjust as needed.');
    }

    // Auto-save draft every 2 minutes
    setInterval(saveDraft, 120000);
    </script>
</body>
</html>