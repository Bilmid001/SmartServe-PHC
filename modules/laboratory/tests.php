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

// Handle test status updates
if ($_POST['action'] ?? '' === 'update_test_status') {
    $test_id = $_POST['test_id'];
    $status = $_POST['status'];
    $results = $_POST['results'] ?? '';
    
    $query = "UPDATE lab_tests SET status = :status, results = :results WHERE id = :test_id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':status' => $status,
        ':results' => $results,
        ':test_id' => $test_id
    ])) {
        $success = "Test status updated successfully!";
    } else {
        $error = "Error updating test status!";
    }
}

// Get pending tests
$query = "SELECT lt.*, p.full_name, p.patient_id, u.full_name as doctor_name 
          FROM lab_tests lt 
          JOIN patients p ON lt.patient_id = p.id 
          JOIN users u ON lt.ordered_by = u.id 
          WHERE lt.status IN ('pending', 'in-progress') 
          ORDER BY lt.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get completed tests (today)
$query = "SELECT COUNT(*) as count FROM lab_tests WHERE DATE(created_at) = CURDATE() AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$completed_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Tests - PHCHMS</title>
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

        /* Stats Cards */
        .stats-cards {
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
            border-left: 4px solid #4299e1 !important;
        }

        .stat-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-icon {
            font-size: 32px !important;
            margin-right: 15px !important;
            opacity: 0.8 !important;
        }

        .stat-info h3 {
            font-size: 24px !important;
            font-weight: 700 !important;
            color: #2d3748 !important;
            margin-bottom: 5px !important;
        }

        .stat-info p {
            color: #718096 !important;
            font-size: 14px !important;
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

        /* Tests Grid */
        .tests-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)) !important;
            gap: 20px !important;
        }

        .test-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            border-left: 4px solid #4299e1 !important;
            transition: all 0.3s ease !important;
        }

        .test-card:hover {
            background: #edf2f7 !important;
            transform: translateY(-3px) !important;
        }

        .test-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            margin-bottom: 15px !important;
        }

        .test-header h4 {
            color: #2d3748 !important;
            font-size: 16px !important;
            margin-bottom: 5px !important;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            text-transform: capitalize !important;
        }

        .status-pending {
            background: #fffaf0 !important;
            color: #744210 !important;
            border: 1px solid #faf089 !important;
        }

        .status-in-progress {
            background: #ebf8ff !important;
            color: #1a365d !important;
            border: 1px solid #90cdf4 !important;
        }

        .status-completed {
            background: #f0fff4 !important;
            color: #22543d !important;
            border: 1px solid #9ae6b4 !important;
        }

        .test-info p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 5px !important;
        }

        .test-info strong {
            color: #4a5568 !important;
        }

        .test-actions {
            display: flex !important;
            gap: 8px !important;
            margin-top: 15px !important;
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

        .btn-success {
            background: #48bb78 !important;
            color: white !important;
        }

        .btn-success:hover {
            background: #38a169 !important;
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

        /* No Data States */
        .no-data {
            text-align: center !important;
            padding: 40px 20px !important;
            color: #718096 !important;
            grid-column: 1 / -1 !important;
        }

        /* AI Analysis */
        .ai-analysis {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 20px !important;
        }

        .analysis-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
        }

        .analysis-card:hover {
            background: #edf2f7 !important;
            border-color: #4299e1 !important;
            transform: translateY(-3px) !important;
        }

        .analysis-card h4 {
            color: #2d3748 !important;
            margin-bottom: 10px !important;
            font-size: 16px !important;
        }

        .analysis-card p {
            color: #718096 !important;
            font-size: 13px !important;
            margin-bottom: 15px !important;
            line-height: 1.4 !important;
        }

        /* Quick Test Form */
        .quick-test-form {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
        }

        .form-row {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 20px !important;
            margin-bottom: 15px !important;
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
            min-height: 100px !important;
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

        /* Form Actions */
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
            
            .stats-cards {
                grid-template-columns: 1fr !important;
            }
            
            .tests-grid {
                grid-template-columns: 1fr !important;
            }
            
            .ai-analysis {
                grid-template-columns: 1fr !important;
            }
            
            .test-header {
                flex-direction: column !important;
                gap: 10px !important;
            }
            
            .test-actions {
                flex-wrap: wrap !important;
            }
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h2>Laboratory Test Management</h2>
                <div class="breadcrumb">
                    <span>Laboratory</span> / <span>Tests</span>
                </div>
            </div>
        </div>

        <!-- Lab Statistics -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">🔬</div>
                <div class="stat-info">
                    <h3><?php echo count($pending_tests); ?></h3>
                    <p>Pending Tests</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $completed_today; ?></h3>
                    <p>Completed Today</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3>3</h3>
                    <p>Abnormal Results</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>24</h3>
                    <p>Avg. Processing Time (hrs)</p>
                </div>
            </div>
        </div>

        <!-- Pending Tests -->
        <div class="dashboard-section">
            <h3>Pending Laboratory Tests</h3>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="tests-grid">
                <?php foreach ($pending_tests as $test): ?>
                <div class="test-card">
                    <div class="test-header">
                        <h4><?php echo $test['test_name']; ?></h4>
                        <span class="status-badge status-<?php echo $test['status']; ?>">
                            <?php echo ucfirst($test['status']); ?>
                        </span>
                    </div>
                    <div class="test-info">
                        <p><strong>Patient:</strong> <?php echo $test['full_name']; ?> (<?php echo $test['patient_id']; ?>)</p>
                        <p><strong>Ordered by:</strong> Dr. <?php echo $test['doctor_name']; ?></p>
                        <p><strong>Test Type:</strong> <?php echo $test['test_type']; ?></p>
                        <p><strong>Ordered:</strong> <?php echo date('M j, Y g:i A', strtotime($test['created_at'])); ?></p>
                    </div>
                    <div class="test-actions">
                        <?php if ($test['status'] === 'pending'): ?>
                            <button class="btn-sm btn-primary" onclick="startTest(<?php echo $test['id']; ?>)">
                                Start Test
                            </button>
                        <?php else: ?>
                            <button class="btn-sm btn-success" onclick="openCompleteTestPopup(<?php echo $test['id']; ?>)">
                                Complete Test
                            </button>
                        <?php endif; ?>
                        <button class="btn-sm btn-secondary" onclick="openTestDetailsPopup(<?php echo $test['id']; ?>)">
                            Details
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (count($pending_tests) === 0): ?>
                    <div class="no-data">
                        <p>No pending tests at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AI Result Analysis -->
        <div class="dashboard-section">
            <h3>AI-Powered Result Analysis</h3>
            <div class="ai-analysis">
                <div class="analysis-card">
                    <h4>🔍 Pattern Detection</h4>
                    <p>AI has detected unusual patterns in recent blood work results.</p>
                    <button class="btn-primary" onclick="openPatternAnalysisPopup()">View Analysis</button>
                </div>
                <div class="analysis-card">
                    <h4>📈 Trend Analysis</h4>
                    <p>Seasonal increase in specific test types detected.</p>
                    <button class="btn-primary" onclick="openTrendsPopup()">View Trends</button>
                </div>
                <div class="analysis-card">
                    <h4>⚠️ Quality Control</h4>
                    <p>Equipment calibration recommended based on result variance.</p>
                    <button class="btn-primary" onclick="openQCReportPopup()">QC Report</button>
                </div>
            </div>
        </div>

        <!-- Quick Test Registration -->
        <div class="dashboard-section">
            <h3>Quick Test Registration</h3>
            <form class="quick-test-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_search" class="form-label">Patient Search</label>
                        <input type="text" id="patient_search" class="form-control" placeholder="Enter patient name or ID">
                    </div>
                    <div class="form-group">
                        <label for="test_type" class="form-label">Test Type</label>
                        <select id="test_type" class="form-control">
                            <option value="blood">Blood Test</option>
                            <option value="urine">Urine Analysis</option>
                            <option value="imaging">Imaging</option>
                            <option value="culture">Culture</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="test_name" class="form-label">Test Name</label>
                    <input type="text" id="test_name" class="form-control" placeholder="Specific test name">
                </div>
                <button type="button" class="btn-primary" onclick="openQuickTestPopup()">Register Test</button>
            </form>
        </div>
    </div>

    <!-- Popup Overlays -->
    <div id="completeTestPopup" class="popup-overlay">
        <div class="popup-content" style="width: 600px;">
            <div class="popup-header">
                <h3>Complete Laboratory Test</h3>
                <button class="popup-close" onclick="closePopup('completeTestPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <form id="completeTestForm">
                    <input type="hidden" id="test_id" name="test_id">
                    <input type="hidden" name="action" value="update_test_status">
                    <input type="hidden" name="status" value="completed">
                    
                    <div class="form-group">
                        <label for="results" class="form-label">Test Results</label>
                        <textarea id="results" name="results" class="form-control" rows="6" placeholder="Enter test results and findings..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="normal_range" class="form-label">Normal Range</label>
                        <input type="text" id="normal_range" name="normal_range" class="form-control" placeholder="e.g., 0-100 mg/dL">
                    </div>
                    <div class="form-group">
                        <label for="flag" class="form-label">Result Flag</label>
                        <select id="flag" name="flag" class="form-control">
                            <option value="normal">Normal</option>
                            <option value="abnormal">Abnormal</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closePopup('completeTestPopup')">Cancel</button>
                        <button type="submit" class="btn-submit">Submit Results</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="testDetailsPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Test Details</h3>
                <button class="popup-close" onclick="closePopup('testDetailsPopup')">&times;</button>
            </div>
            <div class="popup-body">
                <div class="test-card">
                    <div class="test-header">
                        <h4>Complete Blood Count (CBC)</h4>
                        <span class="status-badge status-in-progress">In Progress</span>
                    </div>
                    <div class="test-info">
                        <p><strong>Patient:</strong> John Smith (PAT001)</p>
                        <p><strong>Ordered by:</strong> Dr. Sarah Wilson</p>
                        <p><strong>Test Type:</strong> Blood Test</p>
                        <p><strong>Priority:</strong> Routine</p>
                        <p><strong>Ordered:</strong> <?php echo date('M j, Y g:i A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="quickTestPopup" class="popup-overlay">
        <div class="popup-content" style="width: 500px;">
            <div class="popup-header">
                <h3>Register Quick Test</h3>
                <button class="popup-close" onclick="closePopup('quickTestPopup')">&times;</button>
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
                        <option value="blood">Blood Test</option>
                        <option value="urine">Urine Analysis</option>
                        <option value="imaging">Imaging</option>
                        <option value="culture">Culture</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Test Name</label>
                    <input type="text" class="form-control" placeholder="Enter test name">
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-control">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="stat">STAT</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-cancel" onclick="closePopup('quickTestPopup')">Cancel</button>
                    <button class="btn-submit" onclick="registerQuickTest()">Register Test</button>
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
    function openCompleteTestPopup(testId) {
        document.getElementById('test_id').value = testId;
        openPopup('completeTestPopup');
    }

    function openTestDetailsPopup(testId) {
        openPopup('testDetailsPopup');
    }

    function openQuickTestPopup() {
        openPopup('quickTestPopup');
    }

    function openPatternAnalysisPopup() {
        alert('Opening AI Pattern Analysis...');
    }

    function openTrendsPopup() {
        alert('Opening Trend Analysis...');
    }

    function openQCReportPopup() {
        alert('Opening Quality Control Report...');
    }

    // Test Management Functions
    function startTest(testId) {
        // Update test status to in-progress
        fetch('update-test-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `test_id=${testId}&status=in-progress&action=update_test_status`
        }).then(response => {
            location.reload();
        });
    }
    
    function registerQuickTest() {
        alert('Quick test registered successfully!');
        closePopup('quickTestPopup');
    }

    // Form submission
    document.getElementById('completeTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('', {
            method: 'POST',
            body: formData
        }).then(response => {
            location.reload();
        });
    });
    </script>
</body>
</html>