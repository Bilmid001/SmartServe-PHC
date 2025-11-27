<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get analytics data
$stats = [];
$query = "SELECT COUNT(*) as total FROM patients WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['new_patients_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM appointments WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['appointments_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lab_tests WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_tests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM pharmacy_inventory WHERE quantity <= reorder_level";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['low_stock_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Analytics - PHCHMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .date-range select {
            padding: 8px 12px !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 6px !important;
            background: white !important;
            font-size: 14px !important;
            cursor: pointer !important;
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

        .stat-card-warning {
            border-left-color: #ed8936 !important;
        }

        .stat-card-info {
            border-left-color: #0bc5ea !important;
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

        .stat-card-warning .icon-circle {
            background: rgba(237, 137, 54, 0.1) !important;
        }

        .stat-card-info .icon-circle {
            background: rgba(11, 197, 234, 0.1) !important;
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

        /* Content Grid */
        .content-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
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

        .alert-badge {
            background: #fed7d7 !important;
            color: #742a2a !important;
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        /* Chart Containers */
        .chart-container {
            padding: 20px !important;
            height: 300px !important;
            position: relative !important;
        }

        /* Predictions Grid */
        .predictions-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 20px !important;
            padding: 20px !important;
        }

        .prediction-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            border-left: 4px solid !important;
            transition: all 0.3s ease !important;
        }

        .prediction-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .prediction-high {
            border-left-color: #48bb78 !important;
        }

        .prediction-medium {
            border-left-color: #ed8936 !important;
        }

        .prediction-low {
            border-left-color: #4299e1 !important;
        }

        .prediction-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            margin-bottom: 10px !important;
        }

        .prediction-header h4 {
            color: #2d3748 !important;
            font-size: 16px !important;
            margin-bottom: 5px !important;
        }

        .confidence-score {
            background: white !important;
            padding: 4px 8px !important;
            border-radius: 12px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            border: 1px solid #e1e5eb !important;
        }

        .prediction-card p {
            color: #718096 !important;
            font-size: 14px !important;
            margin-bottom: 15px !important;
            line-height: 1.5 !important;
        }

        .prediction-details {
            background: white !important;
            padding: 12px !important;
            border-radius: 6px !important;
            border: 1px solid #e1e5eb !important;
        }

        .detail-item {
            display: flex !important;
            justify-content: space-between !important;
            margin-bottom: 8px !important;
        }

        .detail-item:last-child {
            margin-bottom: 0 !important;
        }

        .detail-label {
            color: #4a5568 !important;
            font-weight: 600 !important;
            font-size: 12px !important;
        }

        .detail-value {
            color: #718096 !important;
            font-size: 12px !important;
            text-align: right !important;
        }

        /* Alerts Container */
        .alerts-container {
            padding: 20px !important;
        }

        .alert-item {
            display: flex !important;
            align-items: flex-start !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 15px !important;
            border-left: 4px solid !important;
            transition: all 0.3s ease !important;
        }

        .alert-item:hover {
            background: #edf2f7 !important;
            transform: translateX(5px) !important;
        }

        .alert-critical {
            border-left-color: #e53e3e !important;
        }

        .alert-warning {
            border-left-color: #ed8936 !important;
        }

        .alert-info {
            border-left-color: #4299e1 !important;
        }

        .alert-icon {
            font-size: 20px !important;
            margin-right: 15px !important;
            margin-top: 2px !important;
        }

        .alert-content {
            flex: 1 !important;
        }

        .alert-content h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .alert-content p {
            color: #718096 !important;
            font-size: 14px !important;
            margin-bottom: 5px !important;
        }

        .alert-time {
            color: #a0aec0 !important;
            font-size: 12px !important;
        }

        .alert-actions {
            display: flex !important;
            gap: 8px !important;
            margin-left: 15px !important;
        }

        /* Buttons */
        .btn-action {
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
            transform: translateY(-1px) !important;
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
                gap: 15px !important;
            }
            
            .stats-cards-grid {
                grid-template-columns: 1fr !important;
            }
            
            .content-grid {
                grid-template-columns: 1fr !important;
            }
            
            .predictions-grid {
                grid-template-columns: 1fr !important;
            }
            
            .alert-item {
                flex-direction: column !important;
            }
            
            .alert-actions {
                margin-left: 0 !important;
                margin-top: 10px !important;
                width: 100% !important;
                justify-content: flex-end !important;
            }
            
            .chart-container {
                height: 250px !important;
            }
        }

        /* Custom Chart Styles */
        .chart-tooltip {
            background: rgba(0, 0, 0, 0.8) !important;
            border-radius: 6px !important;
            padding: 8px 12px !important;
            color: white !important;
            font-size: 12px !important;
        }

        /* Loading States */
        .loading {
            opacity: 0.7 !important;
            pointer-events: none !important;
        }

        .loading::after {
            content: '' !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            width: 20px !important;
            height: 20px !important;
            margin: -10px 0 0 -10px !important;
            border: 2px solid #e1e5eb !important;
            border-top: 2px solid #4299e1 !important;
            border-radius: 50% !important;
            animation: spin 1s linear infinite !important;
        }

        @keyframes spin {
            0% { transform: rotate(0deg) !important; }
            100% { transform: rotate(360deg) !important; }
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div class="header-left">
                <h2>AI Analytics Dashboard</h2>
                <p class="welcome-message">Real-time insights and predictive analytics for better decision making</p>
            </div>
            <div class="header-right">
                <div class="date-range">
                    <select id="timeRange" onchange="updateAnalytics()">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="365">Last Year</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="stats-cards-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <div class="icon-circle">
                        📈
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['new_patients_today']; ?></h3>
                    <p>New Patients Today</p>
                    <span class="stat-trend trend-up">+12% from yesterday</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <div class="icon-circle">
                        📊
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['appointments_today']; ?></h3>
                    <p>Today's Appointments</p>
                    <span class="stat-trend trend-up">+8% from last week</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ⚠️
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_tests']; ?></h3>
                    <p>Pending Lab Tests</p>
                    <span class="stat-trend trend-down">-5% from yesterday</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <div class="icon-circle">
                        📦
                    </div>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['low_stock_items']; ?></h3>
                    <p>Low Stock Items</p>
                    <span class="stat-trend trend-up">Attention needed</span>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="content-grid">
            <!-- Patient Trends -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Patient Registration Trends</h3>
                    <span class="ai-badge">AI Forecast</span>
                </div>
                <div class="chart-container">
                    <canvas id="patientTrendsChart"></canvas>
                </div>
            </div>

            <!-- Department Distribution -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Department Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <!-- Revenue Analytics -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Revenue Analytics</h3>
                    <span class="ai-badge">Predictive</span>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Resource Utilization -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Resource Utilization</h3>
                </div>
                <div class="chart-container">
                    <canvas id="utilizationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- AI Predictions -->
        <div class="content-card">
            <div class="card-header">
                <h3>AI-Powered Predictions</h3>
                <span class="ai-badge">Machine Learning</span>
            </div>
            <div class="predictions-grid">
                <div class="prediction-card prediction-high">
                    <div class="prediction-header">
                        <h4>📈 Patient Admission Forecast</h4>
                        <span class="confidence-score">94%</span>
                    </div>
                    <p>Expected 15% increase in patient admissions next week due to seasonal factors.</p>
                    <div class="prediction-details">
                        <div class="detail-item">
                            <span class="detail-label">Peak Day:</span>
                            <span class="detail-value">Wednesday</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Recommended Staff:</span>
                            <span class="detail-value">+2 nurses</span>
                        </div>
                    </div>
                </div>

                <div class="prediction-card prediction-medium">
                    <div class="prediction-header">
                        <h4>💊 Medication Demand</h4>
                        <span class="confidence-score">87%</span>
                    </div>
                    <p>Anticipated 20% increase in demand for antibiotics and pain relievers.</p>
                    <div class="prediction-details">
                        <div class="detail-item">
                            <span class="detail-label">Critical Items:</span>
                            <span class="detail-value">Amoxicillin, Paracetamol</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Reorder Quantity:</span>
                            <span class="detail-value">500 units each</span>
                        </div>
                    </div>
                </div>

                <div class="prediction-card prediction-low">
                    <div class="prediction-header">
                        <h4>🔬 Lab Test Volume</h4>
                        <span class="confidence-score">76%</span>
                    </div>
                    <p>Predicted 10% decrease in routine blood tests, increase in specialized tests.</p>
                    <div class="prediction-details">
                        <div class="detail-item">
                            <span class="detail-label">Affected Tests:</span>
                            <span class="detail-value">CBC, Lipid Profile</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Staff Allocation:</span>
                            <span class="detail-value">Adjust shifts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Alerts -->
        <div class="content-card">
            <div class="card-header">
                <h3>Real-time System Alerts</h3>
                <span class="alert-badge">Live</span>
            </div>
            <div class="alerts-container">
                <div class="alert-item alert-critical">
                    <div class="alert-icon">🚨</div>
                    <div class="alert-content">
                        <h4>System Performance</h4>
                        <p>Database response time exceeding thresholds. Consider optimization.</p>
                        <span class="alert-time">Just now</span>
                    </div>
                    <div class="alert-actions">
                        <button class="btn-action btn-primary" onclick="openOptimizationPopup()">Optimize</button>
                        <button class="btn-action btn-outline" onclick="dismissAlert(this)">Ignore</button>
                    </div>
                </div>

                <div class="alert-item alert-warning">
                    <div class="alert-icon">⚠️</div>
                    <div class="alert-content">
                        <h4>Security Alert</h4>
                        <p>Multiple failed login attempts detected from unusual locations.</p>
                        <span class="alert-time">5 minutes ago</span>
                    </div>
                    <div class="alert-actions">
                        <button class="btn-action btn-primary" onclick="openSecurityReviewPopup()">Review</button>
                        <button class="btn-action btn-outline" onclick="dismissAlert(this)">Dismiss</button>
                    </div>
                </div>

                <div class="alert-item alert-info">
                    <div class="alert-icon">💡</div>
                    <div class="alert-content">
                        <h4>Backup Required</h4>
                        <p>Scheduled database backup pending. Recommended to run now.</p>
                        <span class="alert-time">15 minutes ago</span>
                    </div>
                    <div class="alert-actions">
                        <button class="btn-action btn-primary" onclick="openBackupPopup()">Backup Now</button>
                        <button class="btn-action btn-outline" onclick="dismissAlert(this)">Schedule</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Overlays -->
    <div id="optimizationPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>System Optimization</h3>
                <button class="popup-close" onclick="closePopup('optimizationPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Optimization Type</label>
                    <select class="form-control">
                        <option value="database">Database Optimization</option>
                        <option value="cache">Cache Clear</option>
                        <option value="performance">Performance Tuning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Schedule</label>
                    <select class="form-control">
                        <option value="now">Run Now</option>
                        <option value="offpeak">Schedule for Off-Peak Hours</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('optimizationPopup')">Cancel</button>
                    <button class="btn-submit" onclick="runOptimization()">Run Optimization</button>
                </div>
            </div>
        </div>
    </div>

    <div id="securityReviewPopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>Security Review</h3>
                <button class="popup-close" onclick="closePopup('securityReviewPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="alert-item alert-warning">
                    <div class="alert-content">
                        <h4>Suspicious Activity Detected</h4>
                        <p>Multiple failed login attempts from IP: 192.168.1.100</p>
                        <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Action</label>
                    <select class="form-control">
                        <option value="block">Block IP Address</option>
                        <option value="monitor">Increase Monitoring</option>
                        <option value="notify">Notify Security Team</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('securityReviewPopup')">Cancel</button>
                    <button class="btn-submit" onclick="handleSecurityAlert()">Take Action</button>
                </div>
            </div>
        </div>
    </div>

    <div id="backupPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Database Backup</h3>
                <button class="popup-close" onclick="closePopup('backupPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Backup Type</label>
                    <select class="form-control">
                        <option value="full">Full Backup</option>
                        <option value="incremental">Incremental Backup</option>
                        <option value="differential">Differential Backup</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Storage Location</label>
                    <select class="form-control">
                        <option value="local">Local Server</option>
                        <option value="cloud">Cloud Storage</option>
                        <option value="both">Both Locations</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('backupPopup')">Cancel</button>
                    <button class="btn-submit" onclick="startBackup()">Start Backup</button>
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
    function openOptimizationPopup() {
        openPopup('optimizationPopup');
    }

    function openSecurityReviewPopup() {
        openPopup('securityReviewPopup');
    }

    function openBackupPopup() {
        openPopup('backupPopup');
    }

    // Alert Management
    function dismissAlert(button) {
        const alertItem = button.closest('.alert-item');
        alertItem.style.opacity = '0';
        setTimeout(() => {
            alertItem.remove();
        }, 300);
    }

    // Action Functions
    function runOptimization() {
        alert('System optimization started...');
        closePopup('optimizationPopup');
    }

    function handleSecurityAlert() {
        alert('Security action taken successfully!');
        closePopup('securityReviewPopup');
    }

    function startBackup() {
        alert('Database backup initiated...');
        closePopup('backupPopup');
    }

    // Initialize Charts
    document.addEventListener('DOMContentLoaded', function() {
        // Patient Trends Chart
        const patientCtx = document.getElementById('patientTrendsChart').getContext('2d');
        new Chart(patientCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Actual Patients',
                    data: [120, 150, 180, 160, 200, 220, 240, 230, 250, 270, 260, 280],
                    borderColor: '#1a73e8',
                    backgroundColor: 'rgba(26, 115, 232, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Predicted',
                    data: [null, null, null, null, null, null, null, null, null, null, 260, 300],
                    borderColor: '#ff6b6b',
                    borderDash: [5, 5],
                    backgroundColor: 'transparent',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Monthly Patient Registration'
                    }
                }
            }
        });

        // Department Distribution Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cardiology', 'Pediatrics', 'Orthopedics', 'Neurology', 'General Medicine', 'Dermatology'],
                datasets: [{
                    data: [25, 20, 15, 12, 18, 10],
                    backgroundColor: [
                        '#1a73e8', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [125000, 145000, 165000, 195000],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Utilization Chart
        const utilCtx = document.getElementById('utilizationChart').getContext('2d');
        new Chart(utilCtx, {
            type: 'radar',
            data: {
                labels: ['Doctors', 'Nurses', 'Lab Tech', 'Pharmacy', 'Admin', 'Support'],
                datasets: [{
                    label: 'Current Utilization',
                    data: [85, 90, 75, 80, 60, 70],
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    pointBackgroundColor: 'rgba(255, 193, 7, 1)'
                }, {
                    label: 'Optimal Range',
                    data: [80, 85, 80, 85, 75, 75],
                    backgroundColor: 'rgba(26, 115, 232, 0.2)',
                    borderColor: 'rgba(26, 115, 232, 1)',
                    pointBackgroundColor: 'rgba(26, 115, 232, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            }
        });
    });

    function updateAnalytics() {
        const timeRange = document.getElementById('timeRange').value;
        // In real implementation, this would fetch new data based on time range
        alert('Updating analytics for ' + timeRange + ' days...');
    }
    </script>
</body>
</html>