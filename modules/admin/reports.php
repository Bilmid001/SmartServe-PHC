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

// Handle report generation
if ($_POST['action'] ?? '' === 'generate_report') {
    $report_type = $_POST['report_type'];
    $date_range = $_POST['date_range'];
    $format = $_POST['format'];
    
    // Simulate report generation
    $success = "Report generated successfully!";
}
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - PHCHMS</title>
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

        /* Report Form */
        .report-form {
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
            min-height: 80px !important;
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

        /* Buttons */
        .submit-btn, .btn-secondary, .btn-primary {
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

        .btn-primary {
            background: #4299e1 !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: #3182ce !important;
            transform: translateY(-2px) !important;
        }

        .form-actions {
            display: flex !important;
            gap: 10px !important;
            margin-top: 20px !important;
            flex-wrap: wrap !important;
        }

        /* Templates Grid */
        .templates-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 20px !important;
            padding: 20px !important;
        }

        .template-card {
            display: flex !important;
            flex-direction: column !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
            height: 100% !important;
        }

        .template-card:hover {
            background: #edf2f7 !important;
            border-color: #4299e1 !important;
            transform: translateY(-3px) !important;
        }

        .template-icon {
            font-size: 32px !important;
            margin-bottom: 15px !important;
            text-align: center !important;
        }

        .template-content {
            flex: 1 !important;
            margin-bottom: 15px !important;
        }

        .template-content h4 {
            color: #2d3748 !important;
            margin-bottom: 8px !important;
            font-size: 16px !important;
        }

        .template-content p {
            color: #718096 !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
            margin-bottom: 10px !important;
        }

        .template-meta {
            display: flex !important;
            gap: 10px !important;
            margin-top: 10px !important;
        }

        .meta-item {
            color: #718096 !important;
            font-size: 11px !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
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
            margin-top: auto !important;
        }

        .btn-template:hover {
            background: #3182ce !important;
        }

        /* Reports List */
        .reports-list {
            padding: 20px !important;
        }

        .report-item {
            display: flex !important;
            align-items: center !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 10px !important;
            transition: all 0.3s ease !important;
            border-left: 4px solid #4299e1 !important;
        }

        .report-item:hover {
            background: #edf2f7 !important;
            transform: translateX(5px) !important;
        }

        .report-info {
            display: flex !important;
            align-items: center !important;
            flex: 1 !important;
        }

        .report-icon {
            font-size: 24px !important;
            margin-right: 15px !important;
        }

        .report-details h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .report-details p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 3px !important;
        }

        .report-size {
            color: #a0aec0 !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .report-actions {
            display: flex !important;
            gap: 8px !important;
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

        .btn-danger {
            background: #e53e3e !important;
            color: white !important;
        }

        .btn-danger:hover {
            background: #c53030 !important;
        }

        /* Scheduled Reports */
        .scheduled-reports {
            padding: 20px !important;
        }

        .schedule-item {
            display: flex !important;
            align-items: center !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            margin-bottom: 10px !important;
            transition: all 0.3s ease !important;
            border-left: 4px solid #48bb78 !important;
        }

        .schedule-item:hover {
            background: #edf2f7 !important;
            transform: translateX(5px) !important;
        }

        .schedule-info {
            flex: 1 !important;
        }

        .schedule-info h4 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .schedule-info p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 8px !important;
        }

        .schedule-details {
            display: flex !important;
            gap: 15px !important;
        }

        .schedule-frequency, .schedule-recipients {
            color: #718096 !important;
            font-size: 12px !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }

        .schedule-actions {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
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
            
            .templates-grid {
                grid-template-columns: 1fr !important;
            }
            
            .report-item, .schedule-item {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            .report-info, .schedule-info {
                margin-bottom: 10px !important;
                width: 100% !important;
            }
            
            .report-actions, .schedule-actions {
                width: 100% !important;
                justify-content: flex-end !important;
            }
            
            .schedule-details {
                flex-direction: column !important;
                gap: 5px !important;
            }
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h2>Report Generation</h2>
                <div class="breadcrumb">
                    <span>Admin</span> / <span>Reports</span>
                </div>
            </div>
        </div>

        <!-- Report Generation Form -->
        <div class="content-card">
            <div class="card-header">
                <h3>Generate New Report</h3>
            </div>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="report-form">
                <input type="hidden" name="action" value="generate_report">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select id="report_type" name="report_type" class="form-control" required>
                            <option value="">Select Report Type</option>
                            <option value="patient_activity">Patient Activity Report</option>
                            <option value="financial">Financial Summary</option>
                            <option value="inventory">Inventory Status</option>
                            <option value="staff_performance">Staff Performance</option>
                            <option value="appointment_analysis">Appointment Analysis</option>
                            <option value="lab_results">Laboratory Results Summary</option>
                            <option value="pharmacy_sales">Pharmacy Sales Report</option>
                            <option value="system_usage">System Usage Statistics</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select id="date_range" name="date_range" class="form-control" required>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week" selected>This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_quarter">This Quarter</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                </div>

                <div id="customDateRange" class="form-row" style="display: none;">
                    <div class="form-group">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="format" class="form-label">Export Format</label>
                        <select id="format" name="format" class="form-control" required>
                            <option value="pdf">PDF Document</option>
                            <option value="excel">Excel Spreadsheet</option>
                            <option value="csv">CSV File</option>
                            <option value="html">Web Page</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="include_charts" class="form-label">Include Charts</label>
                        <select id="include_charts" name="include_charts" class="form-control">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="report_notes" class="form-label">Additional Notes (Optional)</label>
                    <textarea id="report_notes" name="report_notes" class="form-control" rows="3" placeholder="Any specific requirements or filters..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <span class="btn-icon">📊</span>
                        Generate Report
                    </button>
                    <button type="button" class="btn-secondary" onclick="openPreviewPopup()">
                        <span class="btn-icon">👁️</span>
                        Preview
                    </button>
                </div>
            </form>
        </div>

        <!-- Pre-built Report Templates -->
        <div class="content-card">
            <div class="card-header">
                <h3>Quick Report Templates</h3>
                <span class="ai-badge">AI Optimized</span>
            </div>
            <div class="templates-grid">
                <div class="template-card">
                    <div class="template-icon">👥</div>
                    <div class="template-content">
                        <h4>Daily Patient Summary</h4>
                        <p>Overview of patient registrations, appointments, and visits for today.</p>
                        <div class="template-meta">
                            <span class="meta-item">📅 Today</span>
                            <span class="meta-item">⏱️ 2 min generation</span>
                        </div>
                    </div>
                    <button class="btn-template" onclick="useTemplate('daily_patient')">Use Template</button>
                </div>

                <div class="template-card">
                    <div class="template-icon">💊</div>
                    <div class="template-content">
                        <h4>Pharmacy Inventory Alert</h4>
                        <p>List of low stock items and expiring medications.</p>
                        <div class="template-meta">
                            <span class="meta-item">📅 Real-time</span>
                            <span class="meta-item">⏱️ 1 min generation</span>
                        </div>
                    </div>
                    <button class="btn-template" onclick="useTemplate('pharmacy_alert')">Use Template</button>
                </div>

                <div class="template-card">
                    <div class="template-icon">🔬</div>
                    <div class="template-content">
                        <h4>Lab Performance Report</h4>
                        <p>Test volumes, turnaround times, and result accuracy metrics.</p>
                        <div class="template-meta">
                            <span class="meta-item">📅 This Month</span>
                            <span class="meta-item">⏱️ 3 min generation</span>
                        </div>
                    </div>
                    <button class="btn-template" onclick="useTemplate('lab_performance')">Use Template</button>
                </div>

                <div class="template-card">
                    <div class="template-icon">💰</div>
                    <div class="template-content">
                        <h4>Financial Dashboard</h4>
                        <p>Revenue, expenses, and profitability analysis with trends.</p>
                        <div class="template-meta">
                            <span class="meta-item">📅 This Quarter</span>
                            <span class="meta-item">⏱️ 5 min generation</span>
                        </div>
                    </div>
                    <button class="btn-template" onclick="useTemplate('financial_dashboard')">Use Template</button>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3>Recently Generated Reports</h3>
                <a href="#" class="view-all">View All</a>
            </div>
            <div class="reports-list">
                <div class="report-item">
                    <div class="report-info">
                        <div class="report-icon">📊</div>
                        <div class="report-details">
                            <h4>Monthly Patient Activity Report</h4>
                            <p>Generated on <?php echo date('M j, Y'); ?> | PDF Format</p>
                            <span class="report-size">2.4 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-action btn-primary" onclick="openDownloadPopup(1)">Download</button>
                        <button class="btn-action btn-outline" onclick="openSharePopup(1)">Share</button>
                        <button class="btn-action btn-danger" onclick="openDeletePopup(1)">Delete</button>
                    </div>
                </div>

                <div class="report-item">
                    <div class="report-info">
                        <div class="report-icon">💊</div>
                        <div class="report-details">
                            <h4>Pharmacy Inventory Status</h4>
                            <p>Generated on <?php echo date('M j, Y', strtotime('-1 day')); ?> | Excel Format</p>
                            <span class="report-size">1.8 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-action btn-primary" onclick="openDownloadPopup(2)">Download</button>
                        <button class="btn-action btn-outline" onclick="openSharePopup(2)">Share</button>
                        <button class="btn-action btn-danger" onclick="openDeletePopup(2)">Delete</button>
                    </div>
                </div>

                <div class="report-item">
                    <div class="report-info">
                        <div class="report-icon">🔬</div>
                        <div class="report-details">
                            <h4>Laboratory Performance Analysis</h4>
                            <p>Generated on <?php echo date('M j, Y', strtotime('-2 days')); ?> | PDF Format</p>
                            <span class="report-size">3.1 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-action btn-primary" onclick="openDownloadPopup(3)">Download</button>
                        <button class="btn-action btn-outline" onclick="openSharePopup(3)">Share</button>
                        <button class="btn-action btn-danger" onclick="openDeletePopup(3)">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduled Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3>Scheduled Reports</h3>
                <button class="btn-primary" onclick="openSchedulePopup()">+ Schedule New</button>
            </div>
            <div class="scheduled-reports">
                <div class="schedule-item">
                    <div class="schedule-info">
                        <h4>Daily Patient Summary</h4>
                        <p>Automated email report sent to department heads</p>
                        <div class="schedule-details">
                            <span class="schedule-frequency">🕘 Daily at 8:00 AM</span>
                            <span class="schedule-recipients">👥 5 recipients</span>
                        </div>
                    </div>
                    <div class="schedule-actions">
                        <span class="status-badge status-active">Active</span>
                        <button class="btn-action btn-outline" onclick="openEditSchedulePopup(1)">Edit</button>
                        <button class="btn-action btn-danger" onclick="openDeleteSchedulePopup(1)">Delete</button>
                    </div>
                </div>

                <div class="schedule-item">
                    <div class="schedule-info">
                        <h4>Weekly Financial Report</h4>
                        <p>Comprehensive financial analysis for management</p>
                        <div class="schedule-details">
                            <span class="schedule-frequency">📅 Every Monday at 9:00 AM</span>
                            <span class="schedule-recipients">👥 3 recipients</span>
                        </div>
                    </div>
                    <div class="schedule-actions">
                        <span class="status-badge status-active">Active</span>
                        <button class="btn-action btn-outline" onclick="openEditSchedulePopup(2)">Edit</button>
                        <button class="btn-action btn-danger" onclick="openDeleteSchedulePopup(2)">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Overlays -->
    <div id="previewPopup" class="popup-overlay">
        <div class="popup-content" style="width: 800px;">
            <div class="popup-header">
                <h3>Report Preview</h3>
                <button class="popup-close" onclick="closePopup('previewPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div style="border: 1px solid #e1e5eb; border-radius: 8px; padding: 20px; background: white;">
                    <h4 style="margin-bottom: 15px; color: #2d3748;">Sample Report Preview</h4>
                    <p style="color: #718096; margin-bottom: 15px;">This is a preview of how your report will look when generated.</p>
                    <div style="background: #f7fafc; padding: 15px; border-radius: 6px;">
                        <p style="color: #4a5568; font-size: 14px;">Report Type: <strong id="previewReportType">Patient Activity Report</strong></p>
                        <p style="color: #4a5568; font-size: 14px;">Date Range: <strong id="previewDateRange">This Week</strong></p>
                        <p style="color: #4a5568; font-size: 14px;">Format: <strong id="previewFormat">PDF Document</strong></p>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('previewPopup')">Close</button>
                    <button class="btn-submit" onclick="generateReport()">Generate Report</button>
                </div>
            </div>
        </div>
    </div>

    <div id="downloadPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Download Report</h3>
                <button class="popup-close" onclick="closePopup('downloadPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Download Format</label>
                    <select class="form-control">
                        <option value="pdf">PDF Document</option>
                        <option value="excel">Excel Spreadsheet</option>
                        <option value="csv">CSV File</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('downloadPopup')">Cancel</button>
                    <button class="btn-submit" onclick="downloadReport()">Download</button>
                </div>
            </div>
        </div>
    </div>

    <div id="schedulePopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>Schedule New Report</h3>
                <button class="popup-close" onclick="closePopup('schedulePopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="form-group">
                    <label class="form-label">Report Type</label>
                    <select class="form-control">
                        <option value="patient_activity">Patient Activity Report</option>
                        <option value="financial">Financial Summary</option>
                        <option value="inventory">Inventory Status</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Frequency</label>
                    <select class="form-control">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Recipients</label>
                    <input type="text" class="form-control" placeholder="Enter email addresses separated by commas">
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('schedulePopup')">Cancel</button>
                    <button class="btn-submit" onclick="saveSchedule()">Save Schedule</button>
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
    function openPreviewPopup() {
        // Update preview content based on form values
        const reportType = document.getElementById('report_type').value;
        const dateRange = document.getElementById('date_range').value;
        const format = document.getElementById('format').value;
        
        document.getElementById('previewReportType').textContent = document.getElementById('report_type').options[document.getElementById('report_type').selectedIndex].text;
        document.getElementById('previewDateRange').textContent = document.getElementById('date_range').options[document.getElementById('date_range').selectedIndex].text;
        document.getElementById('previewFormat').textContent = document.getElementById('format').options[document.getElementById('format').selectedIndex].text;
        
        openPopup('previewPopup');
    }

    function openDownloadPopup(reportId) {
        openPopup('downloadPopup');
    }

    function openSharePopup(reportId) {
        alert('Sharing report ' + reportId);
    }

    function openDeletePopup(reportId) {
        if (confirm('Are you sure you want to delete this report?')) {
            alert('Deleting report ' + reportId);
        }
    }

    function openSchedulePopup() {
        openPopup('schedulePopup');
    }

    function openEditSchedulePopup(scheduleId) {
        alert('Editing schedule ' + scheduleId);
    }

    function openDeleteSchedulePopup(scheduleId) {
        if (confirm('Are you sure you want to delete this scheduled report?')) {
            alert('Deleting schedule ' + scheduleId);
        }
    }

    // Show/hide custom date range
    document.getElementById('date_range').addEventListener('change', function() {
        const customRange = document.getElementById('customDateRange');
        customRange.style.display = this.value === 'custom' ? 'block' : 'none';
    });

    function useTemplate(templateType) {
        // Set form values based on template
        const form = document.querySelector('.report-form');
        switch(templateType) {
            case 'daily_patient':
                form.report_type.value = 'patient_activity';
                form.date_range.value = 'today';
                break;
            case 'pharmacy_alert':
                form.report_type.value = 'inventory';
                form.date_range.value = 'this_week';
                break;
            case 'lab_performance':
                form.report_type.value = 'lab_results';
                form.date_range.value = 'this_month';
                break;
            case 'financial_dashboard':
                form.report_type.value = 'financial';
                form.date_range.value = 'this_quarter';
                break;
        }
        alert('Template applied! Adjust settings as needed and generate report.');
    }

    function generateReport() {
        alert('Report generation started...');
        closePopup('previewPopup');
    }

    function downloadReport() {
        alert('Downloading report...');
        closePopup('downloadPopup');
    }

    function saveSchedule() {
        alert('Report schedule saved!');
        closePopup('schedulePopup');
    }
    </script>
</body>
</html>