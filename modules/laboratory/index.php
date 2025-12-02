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

// Get laboratory statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM lab_tests WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_tests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lab_tests WHERE status = 'in-progress'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['in_progress'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lab_tests WHERE DATE(created_at) = CURDATE() AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['completed_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lab_tests WHERE flag = 'abnormal' AND DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['abnormal_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get pending tests
$query = "SELECT lt.*, p.full_name, p.patient_id 
          FROM lab_tests lt 
          JOIN patients p ON lt.patient_id = p.id 
          WHERE lt.status IN ('pending', 'in-progress') 
          ORDER BY lt.created_at ASC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Dashboard - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
   
        <!-- Main Content -->
        <!-- <div class="main-content"> -->
            <div class="content-header lab-content-header">
                <div class="header-left">
                    <h2>Laboratory Dashboard</h2>
                    <p class="welcome-message">Diagnostic testing and analysis management center</p>
                </div>
                <div class="header-right">
                    <div class="lab-controls">
                        <button class="control-btn btn-primary" onclick="processTest()">
                            <span class="btn-icon">🧪</span>
                            Process Test
                        </button>
                        <button class="control-btn btn-success" onclick="uploadResults()">
                            <span class="btn-icon">📤</span>
                            Upload Results
                        </button>
                        <div class="lab-status">
                            <span class="status-indicator status-healthy"></span>
                            <span class="status-text">Lab Operational</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laboratory Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏳
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending_tests']; ?></h3>
                        <p>Pending Tests</p>
                        <span class="stat-trend trend-up">+5 today</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewPendingTests()">Process</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🔄
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['in_progress']; ?></h3>
                        <p>In Progress</p>
                        <span class="stat-trend trend-neutral">Being processed</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewInProgress()">Monitor</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['completed_today']; ?></h3>
                        <p>Completed Today</p>
                        <span class="stat-trend trend-up">+12% efficiency</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewCompleted()">Review</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['abnormal_today']; ?></h3>
                        <p>Abnormal Results</p>
                        <span class="stat-trend trend-up">Needs attention</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewAbnormal()">Verify</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Pending Tests -->
            <div class="content-grid">
                <!-- Quick Actions -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Quick Laboratory Actions</h3>
                        <span class="ai-badge">AI Enhanced</span>
                    </div>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="registerTest()">
                            <span class="action-icon">➕</span>
                            <span class="action-text">Register Test</span>
                        </button>
                        <button class="quick-action-btn" onclick="processTest()">
                            <span class="action-icon">🧪</span>
                            <span class="action-text">Process Test</span>
                        </button>
                        <button class="quick-action-btn" onclick="uploadResults()">
                            <span class="action-icon">📤</span>
                            <span class="action-text">Upload Results</span>
                        </button>
                        <button class="quick-action-btn" onclick="qualityControl()">
                            <span class="action-icon">✅</span>
                            <span class="action-text">Quality Control</span>
                        </button>
                        <button class="quick-action-btn" onclick="equipmentCheck()">
                            <span class="action-icon">⚙️</span>
                            <span class="action-text">Equipment Check</span>
                        </button>
                        <button class="quick-action-btn" onclick="generateReport()">
                            <span class="action-icon">📊</span>
                            <span class="action-text">Lab Report</span>
                        </button>
                    </div>
                </div>

                <!-- Pending Tests -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Pending Tests</h3>
                        <a href="tests.php" class="view-all">View All</a>
                    </div>
                    <div class="pending-tests">
                        <?php if (count($pending_tests) > 0): ?>
                            <?php foreach ($pending_tests as $test): ?>
                            <div class="test-item">
                                <div class="test-header">
                                    <h4><?php echo $test['test_name']; ?></h4>
                                    <span class="test-status status-<?php echo $test['status']; ?>">
                                        <?php echo ucfirst($test['status']); ?>
                                    </span>
                                </div>
                                <div class="test-info">
                                    <p class="patient-name"><?php echo $test['full_name']; ?> (<?php echo $test['patient_id']; ?>)</p>
                                    <p class="test-type"><?php echo $test['test_type']; ?></p>
                                    <p class="test-time">
                                        Ordered: <?php echo date('M j, g:i A', strtotime($test['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="test-actions">
                                    <?php if ($test['status'] === 'pending'): ?>
                                        <button class="btn-action btn-primary" onclick="startTest(<?php echo $test['id']; ?>)">
                                            Start
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-action btn-success" onclick="completeTest(<?php echo $test['id']; ?>)">
                                            Complete
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-action btn-outline" onclick="viewTestDetails(<?php echo $test['id']; ?>)">
                                        Details
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-tests">
                                <p>No pending tests at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Equipment Status -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Equipment Status</h3>
                    <span class="ai-badge">Real-time Monitoring</span>
                </div>
                <div class="equipment-status">
                    <div class="equipment-item operational">
                        <div class="equipment-icon">🔬</div>
                        <div class="equipment-info">
                            <h4>Hematology Analyzer</h4>
                            <p>Model: ABC-2000 | Serial: HEM12345</p>
                            <div class="equipment-metrics">
                                <span class="metric">Uptime: 99.8%</span>
                                <span class="metric">Calibration: Current</span>
                                <span class="metric">Maintenance: Due in 15 days</span>
                            </div>
                        </div>
                        <div class="equipment-status-indicator">
                            <span class="status-dot operational"></span>
                            <span class="status-text">Operational</span>
                        </div>
                    </div>

                    <div class="equipment-item maintenance">
                        <div class="equipment-icon">🧪</div>
                        <div class="equipment-info">
                            <h4>Chemistry Analyzer</h4>
                            <p>Model: CHEM-5000 | Serial: CHEM67890</p>
                            <div class="equipment-metrics">
                                <span class="metric">Uptime: 95.2%</span>
                                <span class="metric">Calibration: Required</span>
                                <span class="metric">Maintenance: Overdue</span>
                            </div>
                        </div>
                        <div class="equipment-status-indicator">
                            <span class="status-dot maintenance"></span>
                            <span class="status-text">Maintenance</span>
                        </div>
                    </div>

                    <div class="equipment-item operational">
                        <div class="equipment-icon">💉</div>
                        <div class="equipment-info">
                            <h4>Blood Gas Analyzer</h4>
                            <p>Model: BGA-300 | Serial: BGA11223</p>
                            <div class="equipment-metrics">
                                <span class="metric">Uptime: 98.5%</span>
                                <span class="metric">Calibration: Current</span>
                                <span class="metric">Maintenance: Due in 30 days</span>
                            </div>
                        </div>
                        <div class="equipment-status-indicator">
                            <span class="status-dot operational"></span>
                            <span class="status-text">Operational</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Lab Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Laboratory Insights</h3>
                    <span class="ai-badge">Predictive Analytics</span>
                </div>
                <div class="lab-insights">
                    <div class="insight-card">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Test Volume Forecast</h4>
                            <p>Expected 18% increase in CBC tests next week. Prepare inventory accordingly.</p>
                            <div class="insight-metrics">
                                <div class="metric">
                                    <span class="metric-value">CBC Tests</span>
                                    <span class="metric-label">+18% forecast</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">Lipid Profile</span>
                                    <span class="metric-label">+12% forecast</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">🔍</div>
                        <div class="insight-content">
                            <h4>Quality Anomaly</h4>
                            <p>Unusual variance detected in recent glucose test results. Recommend recalibration.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="recalibrateEquipment()">Recalibrate</button>
                                <button class="btn-action btn-outline" onclick="viewQualityData()">View Data</button>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">💡</div>
                        <div class="insight-content">
                            <h4>Efficiency Opportunity</h4>
                            <p>Batch processing of routine tests could reduce processing time by 22%.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="optimizeProcess()">Optimize</button>
                                <button class="btn-action btn-outline" onclick="viewEfficiency()">Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Test Statistics -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Today's Test Statistics</h3>
                    <button class="btn-primary" onclick="exportLabReport()">
                        <span class="btn-icon">📄</span>
                        Export Report
                    </button>
                </div>
                <div class="test-statistics">
                    <div class="statistics-grid">
                        <div class="stat-item">
                            <div class="stat-value">42</div>
                            <div class="stat-label">Blood Tests</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">28</div>
                            <div class="stat-label">Urine Tests</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">15</div>
                            <div class="stat-label">Microbiology</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">8</div>
                            <div class="stat-label">Pathology</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">2.1h</div>
                            <div class="stat-label">Avg. Turnaround</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">99.3%</div>
                            <div class="stat-label">Accuracy Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showLabAlerts() {
        alert('Showing laboratory alerts and notifications...');
    }

    function quickTestEntry() {
        const patientId = prompt('Enter patient ID for test:');
        if (patientId) {
            window.location.href = 'tests.php?patient=' + patientId;
        }
    }

    function processTest() {
        window.location.href = 'tests.php';
    }

    function uploadResults() {
        window.location.href = 'results.php';
    }

    function viewPendingTests() {
        window.location.href = 'tests.php?status=pending';
    }

    function viewInProgress() {
        window.location.href = 'tests.php?status=in-progress';
    }

    function viewCompleted() {
        window.location.href = 'results.php?filter=today';
    }

    function viewAbnormal() {
        window.location.href = 'results.php?filter=abnormal';
    }

    function registerTest() {
        window.location.href = 'tests.php?action=new';
    }

    function qualityControl() {
        window.location.href = 'quality-control.php';
    }

    function equipmentCheck() {
        window.location.href = 'equipment.php';
    }

    function generateReport() {
        window.location.href = 'reports.php';
    }

    function startTest(testId) {
        if (confirm('Start processing this test?')) {
            // In real implementation, update test status
            alert('Test ' + testId + ' started.');
            window.location.reload();
        }
    }

    function completeTest(testId) {
        window.location.href = 'complete-test.php?id=' + testId;
    }

    function viewTestDetails(testId) {
        window.location.href = 'test-details.php?id=' + testId;
    }

    function recalibrateEquipment() {
        alert('Initiating equipment recalibration...');
    }

    function viewQualityData() {
        alert('Showing quality control data...');
    }

    function optimizeProcess() {
        alert('Optimizing laboratory processes...');
    }

    function viewEfficiency() {
        alert('Showing efficiency analysis...');
    }

    function exportLabReport() {
        alert('Exporting laboratory report...');
    }
    </script>

    <style>
/* ================================================
   Lab Theme
================================================ */
.lab-header {
    background: linear-gradient(135deg, #16a085, #138d75) !important;
}

.lab-sidebar {
    background: linear-gradient(180deg, #16a085 0%, #138d75 100%) !important;
}

.lab-sidebar .nav-link {
    color: #d1f2eb !important;
    border-left-color: transparent;
}

.lab-sidebar .nav-link:hover,
.lab-sidebar .nav-link.active {
    background: rgba(22, 160, 133, 0.2) !important;
    color: white !important;
    border-left-color: white !important;
}

.lab-sidebar .nav-section-title {
    color: #a2d9ce !important;
}

.lab-content-header {
    background: linear-gradient(135deg, #16a085, #138d75) !important;
    color: white !important;
}

.lab-content-header h2,
.lab-content-header .welcome-message {
    color: white !important;
}

/* ================================================
   Controls & Status
================================================ */
.lab-controls {
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
}

.lab-status {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.8rem 1.2rem !important;
    background: rgba(255, 255, 255, 0.2) !important;
    border-radius: 8px !important;
    color: white !important;
}

/* ================================================
   Pending Tests
================================================ */
.pending-tests {
    display: flex !important;
    flex-direction: column !important;
    gap: 1rem !important;
}

.test-item {
    display: flex !important;
    align-items: center !important;
    gap: 1.5rem !important;
    padding: 1.2rem !important;
    background: #f8f9fa !important;
    border-radius: 8px !important;
    border-left: 4px solid #16a085 !important;
}

.test-header {
    flex: 1 !important;
}

.test-header h4 {
    margin-bottom: 0.5rem !important;
    color: #2c3e50 !important;
}

.test-info {
    min-width: 200px !important;
}

.patient-name,
.test-type,
.test-time {
    margin: 0.2rem 0 !important;
    color: #7f8c8d !important;
    font-size: 0.9rem !important;
}

.test-actions {
    display: flex !important;
    gap: 0.5rem !important;
}

/* ================================================
   Equipment Status
================================================ */
.equipment-status {
    display: flex !important;
    flex-direction: column !important;
    gap: 1rem !important;
}

.equipment-item {
    display: flex !important;
    align-items: center !important;
    gap: 1.5rem !important;
    padding: 1.5rem !important;
    background: #f8f9fa !important;
    border-radius: 8px !important;
    border-left: 4px solid !important;
}

.equipment-item.operational { border-left-color: #2ecc71 !important; }
.equipment-item.maintenance { border-left-color: #f39c12 !important; }
.equipment-item.offline { border-left-color: #e74c3c !important; }

.equipment-icon {
    font-size: 2.5rem !important;
    flex-shrink: 0 !important;
}

.equipment-info {
    flex: 1 !important;
}

.equipment-info h4 { margin-bottom: 0.5rem !important; color: #2c3e50 !important; }
.equipment-info p { margin-bottom: 0.8rem !important; color: #7f8c8d !important; }

.equipment-metrics {
    display: flex !important;
    gap: 1.5rem !important;
}

.equipment-metrics .metric {
    font-size: 0.8rem !important;
    color: #7f8c8d !important;
    background: white !important;
    padding: 0.3rem 0.6rem !important;
    border-radius: 12px !important;
}

.equipment-status-indicator {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

.status-dot {
    width: 12px !important;
    height: 12px !important;
    border-radius: 50% !important;
}

.status-dot.operational { background: #2ecc71; box-shadow: 0 0 10px #2ecc71; }
.status-dot.maintenance { background: #f39c12; box-shadow: 0 0 10px #f39c12; }
.status-dot.offline { background: #e74c3c; box-shadow: 0 0 10px #e74c3c; }

.status-text {
    font-size: 0.8rem !important;
    font-weight: 500 !important;
}

/* ================================================
   Lab Insights
================================================ */
.lab-insights {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
    gap: 1.5rem !important;
}

.insight-card {
    background: #f8f9fa !important;
    padding: 1.5rem !important;
    border-radius: 10px !important;
    border-left: 4px solid #16a085 !important;
}

.insight-icon {
    font-size: 2.5rem !important;
    margin-bottom: 1rem !important;
}

.insight-content h4 {
    margin-bottom: 0.8rem !important;
    color: #2c3e50 !important;
}

.insight-content p {
    margin-bottom: 1.5rem !important;
    color: #7f8c8d !important;
    line-height: 1.5 !important;
}

.insight-metrics {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 1rem !important;
    margin-bottom: 1.5rem !important;
}

.insight-metrics .metric {
    text-align: center !important;
    padding: 0.8rem !important;
    background: white !important;
    border-radius: 6px !important;
}

.metric-value {
    display: block !important;
    font-weight: 700 !important;
    color: #16a085 !important;
    margin-bottom: 0.3rem !important;
}

.metric-label {
    font-size: 0.8rem !important;
    color: #7f8c8d !important;
}

/* ================================================
   Test Statistics
================================================ */
.test-statistics { padding: 1rem 0 !important; }

.statistics-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 1.5rem !important;
}

.stat-item {
    text-align: center !important;
    padding: 1.5rem !important;
    background: #f8f9fa !important;
    border-radius: 8px !important;
}

.stat-value {
    display: block !important;
    font-size: 2rem !important;
    font-weight: 700 !important;
    color: #16a085 !important;
    margin-bottom: 0.5rem !important;
}

.stat-label {
    font-size: 0.9rem !important;
    color: #7f8c8d !important;
    font-weight: 500 !important;
}

.no-tests {
    text-align: center !important;
    padding: 2rem !important;
    color: #7f8c8d !important;
}

/* ================================================
   Responsive Adjustments
================================================ */
@media (max-width: 768px) {
    .lab-insights { grid-template-columns: 1fr !important; }
    .statistics-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .test-item { flex-direction: column !important; align-items: flex-start !important; gap: 1rem !important; }
    .test-actions { align-self: stretch !important; justify-content: stretch !important; }
    .test-actions .btn-action { flex: 1 !important; }
    .equipment-item { flex-direction: column !important; text-align: center !important; }
    .equipment-metrics { flex-direction: column !important; gap: 0.5rem !important; }
    .insight-metrics { grid-template-columns: 1fr !important; }
}

@media (max-width: 480px) {
    .statistics-grid { grid-template-columns: 1fr !important; }
    .quick-actions-grid { grid-template-columns: 1fr 1fr !important; }
}

    </style>
</body>
</html>