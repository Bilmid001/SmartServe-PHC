<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('lab') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle result upload
if ($_POST['action'] ?? '' === 'upload_result') {
    $test_id = $_POST['test_id'];
    $results = $_POST['results'];
    $normal_range = $_POST['normal_range'];
    $flag = $_POST['flag'];
    
    $query = "UPDATE lab_tests SET results = :results, normal_range = :normal_range, flag = :flag, status = 'completed' WHERE id = :test_id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':results' => $results,
        ':normal_range' => $normal_range,
        ':flag' => $flag,
        ':test_id' => $test_id
    ])) {
        $success = "Test results uploaded successfully!";
    } else {
        $error = "Error uploading test results!";
    }
}

// Get completed tests
$query = "SELECT lt.*, p.full_name, p.patient_id, u.full_name as doctor_name 
          FROM lab_tests lt 
          JOIN patients p ON lt.patient_id = p.id 
          JOIN users u ON lt.ordered_by = u.id 
          WHERE lt.status = 'completed' 
          ORDER BY lt.created_at DESC 
          LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();
$completed_tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tests needing review
$query = "SELECT COUNT(*) as count FROM lab_tests WHERE flag = 'abnormal' AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$abnormal_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
        
        <!-- <div class="main-content"> -->
            <div class="content-header">
                <h2>Laboratory Test Results</h2>
                <div class="breadcrumb">
                    <span>Laboratory</span> / <span>Results</span>
                </div>
            </div>

            <!-- Results Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($completed_tests); ?></h3>
                        <p>Tests Completed</p>
                        <span class="stat-trend trend-up">+12 today</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $abnormal_count; ?></h3>
                        <p>Abnormal Results</p>
                        <span class="stat-trend trend-up">Needs review</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏱️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>2.3h</h3>
                        <p>Avg. Turnaround</p>
                        <span class="stat-trend trend-down">-0.5h from last week</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📊
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>98.7%</h3>
                        <p>Accuracy Rate</p>
                        <span class="stat-trend trend-up">+0.3% improvement</span>
                    </div>
                </div>
            </div>

            <!-- Quick Results Upload -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Results Upload</h3>
                    <span class="ai-badge">AI Assisted</span>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="results-form">
                    <input type="hidden" name="action" value="upload_result">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="test_id">Select Test</label>
                            <select id="test_id" name="test_id" required>
                                <option value="">Choose a test to update...</option>
                                <!-- In real implementation, populate with pending tests -->
                                <option value="1">Blood Work - Patient #1234</option>
                                <option value="2">Urinalysis - Patient #5678</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="flag">Result Flag</label>
                            <select id="flag" name="flag" required>
                                <option value="normal">Normal</option>
                                <option value="abnormal">Abnormal</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="results">Test Results</label>
                        <textarea id="results" name="results" rows="6" placeholder="Enter detailed test results..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="normal_range">Normal Range</label>
                        <input type="text" id="normal_range" name="normal_range" placeholder="e.g., 0-100 mg/dL">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-icon">📤</span>
                            Upload Results
                        </button>
                        <button type="button" class="btn-secondary" onclick="aiAnalyzeResults()">
                            <span class="btn-icon">🤖</span>
                            AI Analysis
                        </button>
                    </div>
                </form>
            </div>

            <!-- AI Result Analysis -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Pattern Detection</h3>
                    <span class="ai-badge">Real-time Monitoring</span>
                </div>
                <div class="ai-analysis-dashboard">
                    <div class="analysis-alert">
                        <div class="alert-icon">🔍</div>
                        <div class="alert-content">
                            <h4>Unusual Pattern Detected</h4>
                            <p>Elevated liver enzymes in 3 patients from same geographic area.</p>
                            <div class="alert-actions">
                                <button class="btn-action btn-primary" onclick="investigatePattern()">Investigate</button>
                                <button class="btn-action btn-outline" onclick="dismissAlert()">Dismiss</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analysis-stats">
                        <div class="stat-item">
                            <span class="stat-value">15</span>
                            <span class="stat-label">Patterns Analyzed</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">92%</span>
                            <span class="stat-label">Accuracy</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">3</span>
                            <span class="stat-label">Active Alerts</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Tests Results -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recently Completed Tests</h3>
                    <div class="filter-options">
                        <select id="resultFilter" onchange="filterResults()">
                            <option value="all">All Results</option>
                            <option value="normal">Normal Only</option>
                            <option value="abnormal">Abnormal Only</option>
                            <option value="today">Today Only</option>
                        </select>
                    </div>
                </div>
                <div class="results-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Test ID</th>
                                <th>Patient</th>
                                <th>Test Name</th>
                                <th>Ordered By</th>
                                <th>Completed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed_tests as $test): ?>
                            <tr class="<?php echo $test['flag'] === 'abnormal' ? 'row-abnormal' : ''; ?>">
                                <td><strong>#<?php echo $test['id']; ?></strong></td>
                                <td>
                                    <div class="patient-info">
                                        <strong><?php echo $test['full_name']; ?></strong>
                                        <span class="patient-id"><?php echo $test['patient_id']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $test['test_name']; ?></td>
                                <td>Dr. <?php echo $test['doctor_name']; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($test['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $test['flag']; ?>">
                                        <?php echo ucfirst($test['flag']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-primary" onclick="viewResultDetails(<?php echo $test['id']; ?>)">
                                            View
                                        </button>
                                        <button class="btn-action btn-outline" onclick="editResult(<?php echo $test['id']; ?>)">
                                            Edit
                                        </button>
                                        <button class="btn-action btn-danger" onclick="deleteResult(<?php echo $test['id']; ?>)">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quality Control -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quality Control Dashboard</h3>
                    <button class="btn-primary" onclick="runQualityCheck()">
                        <span class="btn-icon">🔧</span>
                        Run QC Check
                    </button>
                </div>
                <div class="qc-metrics">
                    <div class="qc-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="98">
                                <span class="chart-value">98%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Equipment Accuracy</h4>
                            <p>All systems operating within specifications</p>
                        </div>
                    </div>
                    
                    <div class="qc-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="95">
                                <span class="chart-value">95%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Staff Performance</h4>
                            <p>Meeting quality standards</p>
                        </div>
                    </div>
                    
                    <div class="qc-metric">
                        <div class="metric-chart">
                            <div class="chart-circle" data-percentage="99">
                                <span class="chart-value">99%</span>
                            </div>
                        </div>
                        <div class="metric-info">
                            <h4>Sample Integrity</h4>
                            <p>Proper handling and processing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function aiAnalyzeResults() {
        const results = document.getElementById('results').value;
        if (!results.trim()) {
            alert('Please enter test results first.');
            return;
        }
        
        // Simulate AI analysis
        alert('AI Analysis: Results appear to be within normal ranges. No critical abnormalities detected.');
        
        // Auto-fill normal range and flag
        document.getElementById('normal_range').value = 'Varies by test type';
        document.getElementById('flag').value = 'normal';
    }

    function filterResults() {
        const filter = document.getElementById('resultFilter').value;
        alert('Filtering results by: ' + filter);
        // In real implementation, this would filter the table
    }

    function viewResultDetails(testId) {
        window.location.href = 'result-details.php?id=' + testId;
    }

    function editResult(testId) {
        window.location.href = 'edit-result.php?id=' + testId;
    }

    function deleteResult(testId) {
        if (confirm('Are you sure you want to delete this test result?')) {
            alert('Deleting result ' + testId);
        }
    }

    function investigatePattern() {
        alert('Opening pattern investigation dashboard...');
    }

    function dismissAlert() {
        alert('Alert dismissed.');
    }

    function runQualityCheck() {
        alert('Running quality control checks...');
    }

    // Initialize QC charts
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.chart-circle').forEach(chart => {
            const percentage = chart.getAttribute('data-percentage');
            chart.style.background = `conic-gradient(#1a73e8 ${percentage}%, #e9ecef ${percentage}% 100%)`;
        });
    });
    </script>

    <style>
    .results-form textarea {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    .ai-analysis-dashboard {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .analysis-alert {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), transparent);
        border: 1px solid #ffeaa7;
        border-radius: 10px;
    }

    .alert-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content h4 {
        margin: 0 0 0.5rem 0;
        color: #856404;
    }

    .alert-content p {
        margin: 0 0 1rem 0;
        color: #856404;
    }

    .analysis-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .stat-item {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: #1a73e8;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .results-table-container {
        overflow-x: auto;
    }

    .row-abnormal {
        background: rgba(220, 53, 69, 0.05) !important;
        border-left: 4px solid #dc3545;
    }

    .patient-info {
        display: flex;
        flex-direction: column;
    }

    .patient-id {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .action-buttons {
        display: flex;
        gap: 0.3rem;
    }

    .action-buttons .btn-action {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
    }

    .qc-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .qc-metric {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .metric-chart {
        position: relative;
        width: 80px;
        height: 80px;
    }

    .chart-circle {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: conic-gradient(#1a73e8 0%, #e9ecef 0% 100%);
        transition: all 1s ease;
    }

    .chart-value {
        font-weight: 700;
        color: #1a73e8;
        font-size: 1rem;
    }

    .metric-info h4 {
        margin: 0 0 0.5rem 0;
        color: #343a40;
    }

    .metric-info p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .filter-options {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .filter-options select {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: white;
    }

    @media (max-width: 768px) {
        .analysis-stats {
            grid-template-columns: 1fr;
        }

        .qc-metrics {
            grid-template-columns: 1fr;
        }

        .qc-metric {
            flex-direction: column;
            text-align: center;
        }

        .action-buttons {
            flex-direction: column;
        }

        .analysis-alert {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>
</body>
</html>