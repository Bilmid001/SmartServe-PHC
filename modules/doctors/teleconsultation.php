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

// Get upcoming teleconsultations
$query = "SELECT a.*, p.full_name, p.patient_id, p.phone, p.email 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          WHERE a.doctor_id = :doctor_id 
          AND a.appointment_date >= NOW() 
          AND a.status = 'scheduled'
          ORDER BY a.appointment_date ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id);
$stmt->execute();
$upcoming_consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teleconsultation - PHCHMS</title>
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

        /* Content Grid */
        .content-grid {
            display: grid !important;
            grid-template-columns: 2fr 1fr !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr !important;
            }
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

        /* Call Controls */
        .call-controls {
            display: flex !important;
            gap: 10px !important;
        }

        .btn-control {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 10px 16px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: none !important;
            font-size: 14px !important;
        }

        .btn-success {
            background: #48bb78 !important;
            color: white !important;
        }

        .btn-success:hover {
            background: #38a169 !important;
            transform: translateY(-2px) !important;
        }

        .btn-danger {
            background: #e53e3e !important;
            color: white !important;
        }

        .btn-danger:hover {
            background: #c53030 !important;
            transform: translateY(-2px) !important;
        }

        /* Video Container */
        .video-container {
            padding: 20px !important;
        }

        .video-preview {
            background: #1a202c !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            position: relative !important;
            height: 400px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .video-placeholder {
            text-align: center !important;
            color: white !important;
        }

        .placeholder-icon {
            font-size: 64px !important;
            margin-bottom: 15px !important;
            opacity: 0.7 !important;
        }

        .placeholder-content h4 {
            color: white !important;
            margin-bottom: 10px !important;
            font-size: 18px !important;
        }

        .placeholder-content p {
            color: #a0aec0 !important;
            font-size: 14px !important;
        }

        .call-active {
            text-align: center !important;
            color: white !important;
        }

        .call-timer {
            font-size: 48px !important;
            font-weight: 700 !important;
            margin-bottom: 10px !important;
            color: #48bb78 !important;
        }

        .call-status {
            color: #a0aec0 !important;
            font-size: 16px !important;
        }

        .video-controls {
            position: absolute !important;
            bottom: 20px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            display: flex !important;
            gap: 10px !important;
            background: rgba(0, 0, 0, 0.7) !important;
            padding: 15px !important;
            border-radius: 25px !important;
            backdrop-filter: blur(10px) !important;
        }

        .video-btn {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border: none !important;
            padding: 10px 15px !important;
            border-radius: 20px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
            font-size: 12px !important;
        }

        .video-btn:hover, .video-btn.active {
            background: #4299e1 !important;
            transform: scale(1.05) !important;
        }

        /* Patient Information Sidebar */
        .patient-info-sidebar {
            padding: 20px !important;
        }

        .patient-card {
            display: flex !important;
            align-items: center !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 20px !important;
            border-left: 4px solid #4299e1 !important;
        }

        .patient-avatar {
            margin-right: 15px !important;
        }

        .avatar-icon {
            width: 50px !important;
            height: 50px !important;
            border-radius: 50% !important;
            background: #e1e5eb !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
        }

        .patient-details h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .patient-details p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 3px !important;
        }

        .medical-summary {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 20px !important;
        }

        .medical-summary h5 {
            color: #2d3748 !important;
            margin-bottom: 15px !important;
            font-size: 16px !important;
            border-bottom: 1px solid #e1e5eb !important;
            padding-bottom: 8px !important;
        }

        .summary-item {
            display: flex !important;
            justify-content: space-between !important;
            margin-bottom: 10px !important;
            padding-bottom: 8px !important;
            border-bottom: 1px solid #edf2f7 !important;
        }

        .summary-label {
            color: #4a5568 !important;
            font-weight: 600 !important;
            font-size: 13px !important;
        }

        .summary-value {
            color: #718096 !important;
            font-size: 13px !important;
            text-align: right !important;
            max-width: 60% !important;
        }

        .consultation-notes {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
        }

        .consultation-notes h5 {
            color: #2d3748 !important;
            margin-bottom: 10px !important;
            font-size: 16px !important;
        }

        .consultation-notes textarea {
            width: 100% !important;
            min-height: 120px !important;
            padding: 12px !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            resize: vertical !important;
            margin-bottom: 10px !important;
            transition: border-color 0.3s ease !important;
        }

        .consultation-notes textarea:focus {
            outline: none !important;
            border-color: #4299e1 !important;
        }

        /* Buttons */
        .btn-primary {
            background: #4299e1 !important;
            color: white !important;
            border: none !important;
            padding: 10px 16px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 14px !important;
        }

        .btn-primary:hover {
            background: #3182ce !important;
            transform: translateY(-2px) !important;
        }

        /* Consultations List */
        .consultations-list {
            padding: 20px !important;
        }

        .consultation-item {
            display: flex !important;
            align-items: center !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 10px !important;
            transition: all 0.3s ease !important;
            border-left: 4px solid #4299e1 !important;
        }

        .consultation-item:hover {
            background: #edf2f7 !important;
            transform: translateX(5px) !important;
        }

        .consultation-time {
            min-width: 100px !important;
            text-align: center !important;
            padding-right: 15px !important;
            border-right: 1px solid #e1e5eb !important;
        }

        .consultation-time strong {
            color: #2d3748 !important;
            font-size: 16px !important;
            display: block !important;
        }

        .consultation-time span {
            color: #718096 !important;
            font-size: 12px !important;
        }

        .consultation-details {
            flex: 1 !important;
            padding: 0 15px !important;
        }

        .consultation-details h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .consultation-details p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 3px !important;
        }

        .consultation-reason {
            color: #4a5568 !important;
            font-style: italic !important;
        }

        .consultation-actions {
            display: flex !important;
            gap: 8px !important;
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

        .btn-outline {
            background: transparent !important;
            color: #718096 !important;
            border: 1px solid #e1e5eb !important;
        }

        .btn-outline:hover {
            background: #f7fafc !important;
            color: #4299e1 !important;
            border-color: #4299e1 !important;
        }

        /* No Data States */
        .no-consultations {
            text-align: center !important;
            padding: 40px 20px !important;
            color: #718096 !important;
        }

        /* AI Tools Grid */
        .ai-tools-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            gap: 20px !important;
            padding: 20px !important;
        }

        .tool-card {
            display: flex !important;
            align-items: flex-start !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
        }

        .tool-card:hover {
            background: #edf2f7 !important;
            border-color: #4299e1 !important;
            transform: translateY(-3px) !important;
        }

        .tool-icon {
            font-size: 24px !important;
            margin-right: 15px !important;
            margin-top: 5px !important;
        }

        .tool-content {
            flex: 1 !important;
        }

        .tool-content h4 {
            color: #2d3748 !important;
            margin-bottom: 8px !important;
            font-size: 16px !important;
        }

        .tool-content p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 12px !important;
            line-height: 1.4 !important;
        }

        .btn-tool {
            background: #4299e1 !important;
            color: white !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: background 0.3s ease !important;
        }

        .btn-tool:hover {
            background: #3182ce !important;
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
            
            .stats-cards-grid {
                grid-template-columns: 1fr !important;
            }
            
            .consultation-item {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            .consultation-time {
                border-right: none !important;
                border-bottom: 1px solid #e1e5eb !important;
                padding-right: 0 !important;
                padding-bottom: 10px !important;
                margin-bottom: 10px !important;
                width: 100% !important;
                text-align: left !important;
            }
            
            .consultation-actions {
                width: 100% !important;
                justify-content: flex-end !important;
                margin-top: 10px !important;
            }
            
            .video-controls {
                flex-wrap: wrap !important;
                justify-content: center !important;
            }
            
            .ai-tools-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h2>Teleconsultation Portal</h2>
                <div class="breadcrumb">
                    <span>Doctor</span> / <span>Teleconsultation</span>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-cards-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <div class="icon-circle">
                        📅
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($upcoming_consultations); ?></h3>
                    <p>Upcoming Consultations</p>
                    <span class="stat-trend trend-up">+3 today</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ✅
                    </div>
                </div>
                <div class="stat-content">
                    <h3>12</h3>
                    <p>Completed This Week</p>
                    <span class="stat-trend trend-up">+15% from last week</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ⭐
                    </div>
                </div>
                <div class="stat-content">
                    <h3>4.8/5</h3>
                    <p>Patient Satisfaction</p>
                    <span class="stat-trend trend-up">Excellent</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ⏱️
                    </div>
                </div>
                <div class="stat-content">
                    <h3>18min</h3>
                    <p>Average Duration</p>
                    <span class="stat-trend trend-down">-2min from last month</span>
                </div>
            </div>
        </div>

        <!-- Video Consultation Interface -->
        <div class="content-grid">
            <!-- Video Call Interface -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Video Consultation</h3>
                    <div class="call-controls">
                        <button class="btn-control btn-success" id="startCall">
                            <span class="control-icon">📞</span>
                            Start Call
                        </button>
                        <button class="btn-control btn-danger" id="endCall" disabled>
                            <span class="control-icon">📵</span>
                            End Call
                        </button>
                    </div>
                </div>
                <div class="video-container">
                    <div class="video-preview">
                        <div class="video-placeholder">
                            <div class="placeholder-content">
                                <span class="placeholder-icon">📹</span>
                                <h4>Video Consultation Ready</h4>
                                <p>Start a call to begin teleconsultation</p>
                            </div>
                        </div>
                        <div class="video-controls">
                            <button class="video-btn" id="toggleVideo">
                                <span class="btn-icon">📹</span>
                                Video
                            </button>
                            <button class="video-btn" id="toggleAudio">
                                <span class="btn-icon">🎤</span>
                                Audio
                            </button>
                            <button class="video-btn" id="screenShare">
                                <span class="btn-icon">🖥️</span>
                                Share
                            </button>
                            <button class="video-btn" id="recordCall">
                                <span class="btn-icon">⏺️</span>
                                Record
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Patient Information</h3>
                </div>
                <div class="patient-info-sidebar">
                    <div class="patient-card">
                        <div class="patient-avatar">
                            <span class="avatar-icon">👤</span>
                        </div>
                        <div class="patient-details">
                            <h4 id="patientName">Select a patient to begin</h4>
                            <p id="patientId">Patient ID: --</p>
                            <p id="patientContact">Contact: --</p>
                        </div>
                    </div>
                    
                    <div class="medical-summary">
                        <h5>Medical Summary</h5>
                        <div class="summary-item">
                            <span class="summary-label">Last Visit:</span>
                            <span class="summary-value">--</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Current Medications:</span>
                            <span class="summary-value">--</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Allergies:</span>
                            <span class="summary-value">--</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Chronic Conditions:</span>
                            <span class="summary-value">--</span>
                        </div>
                    </div>

                    <div class="consultation-notes">
                        <h5>Consultation Notes</h5>
                        <textarea id="consultationNotes" placeholder="Add notes during consultation..."></textarea>
                        <button class="btn-primary" onclick="saveNotes()">Save Notes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Consultations -->
        <div class="content-card">
            <div class="card-header">
                <h3>Upcoming Teleconsultations</h3>
                <button class="btn-primary" onclick="openScheduleConsultationPopup()">+ Schedule New</button>
            </div>
            <div class="consultations-list">
                <?php if (count($upcoming_consultations) > 0): ?>
                    <?php foreach ($upcoming_consultations as $consultation): ?>
                    <div class="consultation-item" data-patient-id="<?php echo $consultation['patient_id']; ?>" 
                         data-patient-name="<?php echo $consultation['full_name']; ?>"
                         data-patient-contact="<?php echo $consultation['phone']; ?>">
                        <div class="consultation-time">
                            <strong><?php echo date('g:i A', strtotime($consultation['appointment_date'])); ?></strong>
                            <span><?php echo date('M j, Y', strtotime($consultation['appointment_date'])); ?></span>
                        </div>
                        <div class="consultation-details">
                            <h4><?php echo $consultation['full_name']; ?></h4>
                            <p>Patient ID: <?php echo $consultation['patient_id']; ?></p>
                            <p class="consultation-reason"><?php echo $consultation['reason']; ?></p>
                        </div>
                        <div class="consultation-actions">
                            <button class="btn-action btn-primary" onclick="startConsultation('<?php echo $consultation['patient_id']; ?>', '<?php echo $consultation['full_name']; ?>')">
                                Start Now
                            </button>
                            <button class="btn-action btn-outline" onclick="openReschedulePopup(<?php echo $consultation['id']; ?>)">
                                Reschedule
                            </button>
                            <button class="btn-action btn-danger" onclick="openCancelPopup(<?php echo $consultation['id']; ?>)">
                                Cancel
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-consultations">
                        <p>No upcoming teleconsultations scheduled.</p>
                        <button class="btn-primary" onclick="openScheduleConsultationPopup()">Schedule Your First Consultation</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AI Assistance Tools -->
        <div class="content-card">
            <div class="card-header">
                <h3>AI Assistance Tools</h3>
                <span class="ai-badge">Real-time Support</span>
            </div>
            <div class="ai-tools-grid">
                <div class="tool-card">
                    <div class="tool-icon">🔍</div>
                    <div class="tool-content">
                        <h4>Symptom Analysis</h4>
                        <p>AI-powered symptom assessment and differential diagnosis</p>
                        <button class="btn-tool" onclick="openSymptomAnalyzerPopup()">Open Tool</button>
                    </div>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">💊</div>
                    <div class="tool-content">
                        <h4>Drug Interactions</h4>
                        <p>Check medication interactions and contraindications</p>
                        <button class="btn-tool" onclick="openDrugCheckerPopup()">Check Now</button>
                    </div>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">📋</div>
                    <div class="tool-content">
                        <h4>Clinical Guidelines</h4>
                        <p>Access evidence-based treatment protocols</p>
                        <button class="btn-tool" onclick="openGuidelinesPopup()">View Guidelines</button>
                    </div>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">🔄</div>
                    <div class="tool-content">
                        <h4>Prescription Writer</h4>
                        <p>AI-assisted prescription generation and management</p>
                        <button class="btn-tool" onclick="openPrescriptionWriterPopup()">Write Prescription</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Overlays -->
    <div id="scheduleConsultationPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Schedule New Consultation</h3>
                <button class="popup-close" onclick="closePopup('scheduleConsultationPopup')">&times;</button>
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
                    <textarea class="form-control" placeholder="Reason for consultation..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('scheduleConsultationPopup')">Cancel</button>
                    <button class="btn-submit">Schedule Consultation</button>
                </div>
            </div>
        </div>
    </div>

    <div id="reschedulePopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Reschedule Consultation</h3>
                <button class="popup-close" onclick="closePopup('reschedulePopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">New Date</label>
                    <input type="date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">New Time</label>
                    <input type="time" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Reason for Rescheduling</label>
                    <textarea class="form-control" placeholder="Optional reason..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('reschedulePopup')">Cancel</button>
                    <button class="btn-submit">Reschedule</button>
                </div>
            </div>
        </div>
    </div>

    <div id="symptomAnalyzerPopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>AI Symptom Analyzer</h3>
                <button class="popup-close" onclick="closePopup('symptomAnalyzerPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Patient Symptoms</label>
                    <textarea class="form-control" placeholder="Describe the patient's symptoms in detail..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <input type="text" class="form-control" placeholder="How long have symptoms been present?">
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
    function openScheduleConsultationPopup() {
        openPopup('scheduleConsultationPopup');
    }

    function openReschedulePopup(consultationId) {
        if (consultationId) {
            console.log('Rescheduling consultation:', consultationId);
        }
        openPopup('reschedulePopup');
    }

    function openCancelPopup(consultationId) {
        if (confirm('Are you sure you want to cancel this consultation?')) {
            alert('Cancelling consultation ' + consultationId);
        }
    }

    function openSymptomAnalyzerPopup() {
        openPopup('symptomAnalyzerPopup');
    }

    function openDrugCheckerPopup() {
        alert('Opening Drug Interaction Checker...');
    }

    function openGuidelinesPopup() {
        alert('Opening Clinical Guidelines...');
    }

    function openPrescriptionWriterPopup() {
        alert('Opening Prescription Writer...');
    }

    // Consultation Management
    function startConsultation(patientId, patientName) {
        // Update patient information
        document.getElementById('patientName').textContent = patientName;
        document.getElementById('patientId').textContent = 'Patient ID: ' + patientId;
        document.getElementById('patientContact').textContent = 'Contact: +1 (555) 123-4567';
        
        // Enable call controls
        document.getElementById('startCall').disabled = false;
        
        // Load patient medical history (simulated)
        loadPatientHistory(patientId);
        
        alert('Ready to start consultation with ' + patientName);
    }

    function loadPatientHistory(patientId) {
        // Simulate loading patient history
        const medicalSummary = document.querySelector('.medical-summary');
        medicalSummary.innerHTML = `
            <h5>Medical Summary</h5>
            <div class="summary-item">
                <span class="summary-label">Last Visit:</span>
                <span class="summary-value">Nov 15, 2023</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Current Medications:</span>
                <span class="summary-value">Lisinopril 10mg, Metformin 500mg</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Allergies:</span>
                <span class="summary-value">Penicillin, Shellfish</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Chronic Conditions:</span>
                <span class="summary-value">Hypertension, Type 2 Diabetes</span>
            </div>
        `;
    }

    function saveNotes() {
        const notes = document.getElementById('consultationNotes').value;
        if (notes.trim()) {
            alert('Consultation notes saved successfully!');
            // In real implementation, save to database
        } else {
            alert('Please enter some notes before saving.');
        }
    }

    // AI Tools
    function analyzeSymptoms() {
        document.getElementById('symptomResults').style.display = 'block';
    }

    // Video Call Controls
    document.getElementById('startCall').addEventListener('click', function() {
        this.disabled = true;
        document.getElementById('endCall').disabled = false;
        
        // Simulate call start
        const videoPlaceholder = document.querySelector('.video-placeholder');
        videoPlaceholder.innerHTML = `
            <div class="call-active">
                <div class="call-timer">00:00</div>
                <div class="call-status">Consultation in progress...</div>
            </div>
        `;
        
        alert('Video call started!');
    });

    document.getElementById('endCall').addEventListener('click', function() {
        this.disabled = true;
        document.getElementById('startCall').disabled = false;
        
        // Reset video placeholder
        const videoPlaceholder = document.querySelector('.video-placeholder');
        videoPlaceholder.innerHTML = `
            <div class="placeholder-content">
                <span class="placeholder-icon">📹</span>
                <h4>Video Consultation Ready</h4>
                <p>Start a call to begin teleconsultation</p>
            </div>
        `;
        
        alert('Video call ended!');
    });

    // Video control buttons
    document.querySelectorAll('.video-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });
    </script>
</body>
</html>