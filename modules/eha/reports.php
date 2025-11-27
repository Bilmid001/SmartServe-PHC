<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('eha') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$inspector_id = $_SESSION['user_id'];

// Handle report generation
if ($_POST['action'] ?? '' === 'generate_report') {
    $report_type = $_POST['report_type'];
    $date_range = $_POST['date_range'];
    $location = $_POST['location'];
    $format = $_POST['format'];
    
    // Simulate report generation
    $success = "Environmental Health Report generated successfully!";
}

// Get EHA statistics
$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$total_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id AND DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$today_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id AND risk_level = 'high'";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$high_risk = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id AND status = 'open'";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$open_cases = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EHA Reports - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h2>Environmental Health Reports</h2>
                <div class="breadcrumb">
                    <span>EHA</span> / <span>Reports</span>
                </div>
            </div>

            <!-- EHA Report Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📋
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_reports; ?></h3>
                        <p>Total Reports</p>
                        <span class="stat-trend trend-up">All time</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $today_reports; ?></h3>
                        <p>Today's Reports</p>
                        <span class="stat-trend trend-up">+2 from yesterday</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $high_risk; ?></h3>
                        <p>High Risk Cases</p>
                        <span class="stat-trend trend-up">Needs attention</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🔄
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $open_cases; ?></h3>
                        <p>Open Cases</p>
                        <span class="stat-trend trend-neutral">In progress</span>
                    </div>
                </div>
            </div>

            <!-- Report Generation -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Generate Environmental Health Report</h3>
                    <span class="ai-badge">AI Enhanced</span>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="report-form">
                    <input type="hidden" name="action" value="generate_report">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" required>
                                <option value="">Select Report Type</option>
                                <option value="sanitation">Sanitation Inspection</option>
                                <option value="water_quality">Water Quality Analysis</option>
                                <option value="food_safety">Food Safety Audit</option>
                                <option value="waste_management">Waste Management</option>
                                <option value="vector_control">Vector Control</option>
                                <option value="air_quality">Air Quality Monitoring</option>
                                <option value="comprehensive">Comprehensive EHA Report</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_range">Date Range</label>
                            <select id="date_range" name="date_range" required>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="this_week" selected>This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="location">Geographic Location</label>
                            <select id="location" name="location" required>
                                <option value="">Select Location</option>
                                <option value="all">All Areas</option>
                                <option value="urban">Urban Areas</option>
                                <option value="rural">Rural Areas</option>
                                <option value="coastal">Coastal Regions</option>
                                <option value="industrial">Industrial Zones</option>
                                <option value="residential">Residential Areas</option>
                                <option value="commercial">Commercial Districts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="format">Report Format</label>
                            <select id="format" name="format" required>
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="html">Web Page</option>
                                <option value="presentation">Presentation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="additional_parameters">Additional Parameters</label>
                        <textarea id="additional_parameters" name="additional_parameters" rows="3" 
                                  placeholder="Any specific parameters, filters, or special requirements..."></textarea>
                    </div>

                    <!-- AI Analysis Preview -->
                    <div class="ai-preview-section">
                        <div class="preview-header">
                            <h4>AI Analysis Preview</h4>
                            <button type="button" class="btn-secondary" onclick="generatePreview()">
                                <span class="btn-icon">🔍</span>
                                Generate Preview
                            </button>
                        </div>
                        <div id="previewResults" class="preview-results" style="display: none;">
                            <div class="preview-card">
                                <h5>Report Insights Preview</h5>
                                <div class="preview-content">
                                    <div class="insight-item">
                                        <span class="insight-icon">📈</span>
                                        <div class="insight-text">
                                            <strong>Trend Analysis:</strong> 15% increase in sanitation compliance
                                        </div>
                                    </div>
                                    <div class="insight-item">
                                        <span class="insight-icon">⚠️</span>
                                        <div class="insight-text">
                                            <strong>Risk Areas:</strong> Industrial zone shows elevated pollution levels
                                        </div>
                                    </div>
                                    <div class="insight-item">
                                        <span class="insight-icon">💡</span>
                                        <div class="insight-text">
                                            <strong>Recommendations:</strong> Enhanced monitoring in high-risk areas
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-icon">📊</span>
                            Generate Report
                        </button>
                        <button type="button" class="btn-secondary" onclick="scheduleReport()">
                            <span class="btn-icon">⏰</span>
                            Schedule Report
                        </button>
                        <button type="button" class="btn-outline" onclick="saveTemplate()">
                            <span class="btn-icon">💾</span>
                            Save as Template
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Report Templates -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Report Templates</h3>
                    <span class="ai-badge">Pre-configured</span>
                </div>
                <div class="templates-grid">
                    <div class="template-card">
                        <div class="template-icon">🚰</div>
                        <div class="template-content">
                            <h4>Water Quality Summary</h4>
                            <p>Comprehensive water quality analysis and compliance report</p>
                            <div class="template-meta">
                                <span class="meta-item">📅 Weekly</span>
                                <span class="meta-item">🌍 All regions</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('water_quality')">Use Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-icon">🗑️</div>
                        <div class="template-content">
                            <h4>Waste Management</h4>
                            <p>Waste disposal and recycling compliance report</p>
                            <div class="template-meta">
                                <span class="meta-item">📅 Monthly</span>
                                <span class="meta-item">🏭 Industrial</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('waste_management')">Use Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-icon">🏠</div>
                        <div class="template-content">
                            <h4>Sanitation Audit</h4>
                            <p>Residential and commercial sanitation inspection report</p>
                            <div class="template-meta">
                                <span class="meta-item">📅 Quarterly</span>
                                <span class="meta-item">🏘️ Residential</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('sanitation')">Use Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-icon">🌡️</div>
                        <div class="template-content">
                            <h4>Air Quality Index</h4>
                            <p>Air pollution levels and environmental impact assessment</p>
                            <div class="template-meta">
                                <span class="meta-item">📅 Daily</span>
                                <span class="meta-item">🏙️ Urban</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('air_quality')">Use Template</button>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recently Generated Reports</h3>
                    <a href="report-archive.php" class="view-all">View Archive</a>
                </div>
                <div class="reports-list">
                    <div class="report-item">
                        <div class="report-info">
                            <div class="report-icon">🚰</div>
                            <div class="report-details">
                                <h4>Water Quality Analysis - Urban Areas</h4>
                                <p>Generated on <?php echo date('M j, Y'); ?> | PDF Format</p>
                                <div class="report-meta">
                                    <span class="meta-tag risk-low">Low Risk</span>
                                    <span class="meta-tag location-urban">Urban</span>
                                </div>
                            </div>
                        </div>
                        <div class="report-actions">
                            <button class="btn-action btn-primary" onclick="downloadReport(1)">Download</button>
                            <button class="btn-action btn-outline" onclick="shareReport(1)">Share</button>
                            <button class="btn-action btn-danger" onclick="deleteReport(1)">Delete</button>
                        </div>
                    </div>

                    <div class="report-item">
                        <div class="report-info">
                            <div class="report-icon">🗑️</div>
                            <div class="report-details">
                                <h4>Waste Management Compliance</h4>
                                <p>Generated on <?php echo date('M j, Y', strtotime('-1 day')); ?> | Excel Format</p>
                                <div class="report-meta">
                                    <span class="meta-tag risk-medium">Medium Risk</span>
                                    <span class="meta-tag location-industrial">Industrial</span>
                                </div>
                            </div>
                        </div>
                        <div class="report-actions">
                            <button class="btn-action btn-primary" onclick="downloadReport(2)">Download</button>
                            <button class="btn-action btn-outline" onclick="shareReport(2)">Share</button>
                            <button class="btn-action btn-danger" onclick="deleteReport(2)">Delete</button>
                        </div>
                    </div>

                    <div class="report-item">
                        <div class="report-info">
                            <div class="report-icon">🏠</div>
                            <div class="report-details">
                                <h4>Sanitation Inspection Report</h4>
                                <p>Generated on <?php echo date('M j, Y', strtotime('-2 days')); ?> | PDF Format</p>
                                <div class="report-meta">
                                    <span class="meta-tag risk-high">High Risk</span>
                                    <span class="meta-tag location-rural">Rural</span>
                                </div>
                            </div>
                        </div>
                        <div class="report-actions">
                            <button class="btn-action btn-primary" onclick="downloadReport(3)">Download</button>
                            <button class="btn-action btn-outline" onclick="shareReport(3)">Share</button>
                            <button class="btn-action btn-danger" onclick="deleteReport(3)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Environmental Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Environmental Insights</h3>
                    <span class="ai-badge">Real-time Analysis</span>
                </div>
                <div class="environmental-insights">
                    <div class="insight-card critical">
                        <div class="insight-header">
                            <h4>🚨 Pollution Alert</h4>
                            <span class="priority-badge">Critical</span>
                        </div>
                        <p>Industrial zone shows 25% increase in air pollution levels. Immediate action recommended.</p>
                        <div class="insight-metrics">
                            <div class="metric">
                                <span class="metric-value">185 AQI</span>
                                <span class="metric-label">Air Quality</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value">+25%</span>
                                <span class="metric-label">Increase</span>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card warning">
                        <div class="insight-header">
                            <h4>⚠️ Water Quality</h4>
                            <span class="priority-badge">Warning</span>
                        </div>
                        <p>Bacterial contamination detected in rural water sources. Testing recommended.</p>
                        <div class="insight-metrics">
                            <div class="metric">
                                <span class="metric-value">3 areas</span>
                                <span class="metric-label">Affected</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value">Medium</span>
                                <span class="metric-label">Risk Level</span>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card info">
                        <div class="insight-header">
                            <h4>💡 Improvement Trend</h4>
                            <span class="priority-badge">Positive</span>
                        </div>
                        <p>Sanitation compliance improved by 18% in urban residential areas.</p>
                        <div class="insight-metrics">
                            <div class="metric">
                                <span class="metric-value">92%</span>
                                <span class="metric-label">Compliance</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value">+18%</span>
                                <span class="metric-label">Improvement</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function generatePreview() {
        const reportType = document.getElementById('report_type').value;
        const location = document.getElementById('location').value;
        
        if (!reportType || !location) {
            alert('Please select report type and location first.');
            return;
        }

        // Show loading state
        const previewDiv = document.getElementById('previewResults');
        previewDiv.innerHTML = `
            <div class="preview-card loading">
                <h5>Generating AI Preview...</h5>
                <p>Analyzing environmental data and trends</p>
            </div>
        `;
        previewDiv.style.display = 'block';

        // Simulate AI analysis
        setTimeout(() => {
            previewDiv.innerHTML = `
                <div class="preview-card">
                    <h5>Report Insights Preview</h5>
                    <div class="preview-content">
                        <div class="insight-item">
                            <span class="insight-icon">📈</span>
                            <div class="insight-text">
                                <strong>Trend Analysis:</strong> 15% increase in sanitation compliance
                            </div>
                        </div>
                        <div class="insight-item">
                            <span class="insight-icon">⚠️</span>
                            <div class="insight-text">
                                <strong>Risk Areas:</strong> ${location} shows elevated pollution levels
                            </div>
                        </div>
                        <div class="insight-item">
                            <span class="insight-icon">💡</span>
                            <div class="insight-text">
                                <strong>Recommendations:</strong> Enhanced monitoring in high-risk areas
                            </div>
                        </div>
                        <div class="insight-item">
                            <span class="insight-icon">📊</span>
                            <div class="insight-text">
                                <strong>Data Points:</strong> 245 inspections analyzed across selected parameters
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }, 2000);
    }

    function scheduleReport() {
        alert('Opening report scheduling interface...');
    }

    function saveTemplate() {
        const reportType = document.getElementById('report_type').value;
        if (!reportType) {
            alert('Please configure the report first.');
            return;
        }
        alert('Report template saved successfully!');
    }

    function useTemplate(templateType) {
        // Set form values based on template
        const form = document.querySelector('.report-form');
        switch(templateType) {
            case 'water_quality':
                form.report_type.value = 'water_quality';
                form.date_range.value = 'this_week';
                form.location.value = 'all';
                break;
            case 'waste_management':
                form.report_type.value = 'waste_management';
                form.date_range.value = 'this_month';
                form.location.value = 'industrial';
                break;
            case 'sanitation':
                form.report_type.value = 'sanitation';
                form.date_range.value = 'this_month';
                form.location.value = 'residential';
                break;
            case 'air_quality':
                form.report_type.value = 'air_quality';
                form.date_range.value = 'today';
                form.location.value = 'urban';
                break;
        }
        alert('Template applied! Adjust parameters as needed and generate report.');
    }

    function downloadReport(reportId) {
        alert('Downloading report ' + reportId);
    }

    function shareReport(reportId) {
        alert('Sharing report ' + reportId);
    }

    function deleteReport(reportId) {
        if (confirm('Are you sure you want to delete this report?')) {
            alert('Deleting report ' + reportId);
        }
    }

    // Auto-generate preview when form changes
    document.getElementById('report_type').addEventListener('change', function() {
        if (this.value && document.getElementById('location').value) {
            generatePreview();
        }
    });

    document.getElementById('location').addEventListener('change', function() {
        if (this.value && document.getElementById('report_type').value) {
            generatePreview();
        }
    });
    </script>

    <style>
    .report-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .ai-preview-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #e67e22;
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .preview-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .preview-results {
        margin-top: 1rem;
    }

    .preview-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .preview-card h5 {
        margin: 0 0 1rem 0;
        color: #2c3e50;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    .preview-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .insight-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .insight-icon {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .insight-text {
        color: #495057;
        line-height: 1.5;
    }

    .templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .template-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .template-card:hover {
        border-color: #e67e22;
        background: white;
        transform: translateY(-2px);
    }

    .template-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .template-content h4 {
        margin: 0 0 0.8rem 0;
        color: #2c3e50;
        text-align: center;
    }

    .template-content p {
        margin: 0 0 1rem 0;
        color: #6c757d;
        text-align: center;
        line-height: 1.5;
    }

    .template-meta {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        font-size: 0.8rem;
        color: #6c757d;
        background: white;
        padding: 0.3rem 0.6rem;
        border-radius: 12px;
    }

    .reports-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .report-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #e67e22;
    }

    .report-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .report-icon {
        font-size: 2.5rem;
    }

    .report-details h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .report-details p {
        margin: 0 0 0.8rem 0;
        color: #6c757d;
    }

    .report-meta {
        display: flex;
        gap: 0.5rem;
    }

    .meta-tag {
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .risk-low {
        background: #d4edda;
        color: #155724;
    }

    .risk-medium {
        background: #fff3cd;
        color: #856404;
    }

    .risk-high {
        background: #f8d7da;
        color: #721c24;
    }

    .location-urban {
        background: #d1ecf1;
        color: #0c5460;
    }

    .location-industrial {
        background: #e2e3e5;
        color: #383d41;
    }

    .location-rural {
        background: #d4edda;
        color: #155724;
    }

    .environmental-insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid;
    }

    .insight-card.critical {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), transparent);
        border-left-color: #e74c3c;
    }

    .insight-card.warning {
        background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), transparent);
        border-left-color: #f39c12;
    }

    .insight-card.info {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), transparent);
        border-left-color: #3498db;
    }

    .insight-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .insight-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .priority-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .insight-card.critical .priority-badge {
        background: #e74c3c;
        color: white;
    }

    .insight-card.warning .priority-badge {
        background: #f39c12;
        color: white;
    }

    .insight-card.info .priority-badge {
        background: #3498db;
        color: white;
    }

    .insight-card p {
        margin: 0 0 1.5rem 0;
        color: #495057;
        line-height: 1.5;
    }

    .insight-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .insight-metrics .metric {
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 8px;
    }

    .metric-value {
        display: block;
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.3rem;
    }

    .metric-label {
        font-size: 0.8rem;
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .templates-grid {
            grid-template-columns: 1fr;
        }

        .environmental-insights {
            grid-template-columns: 1fr;
        }

        .report-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .report-actions {
            align-self: stretch;
            display: flex;
            gap: 0.5rem;
        }

        .report-actions .btn-action {
            flex: 1;
        }

        .preview-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }
    </style>
</body>
</html>