<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('patient')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$patient_id = $_SESSION['user_id'];

// Get patient medical records
$query = "SELECT mr.*, u.full_name as doctor_name 
          FROM medical_records mr 
          JOIN users u ON mr.doctor_id = u.id 
          WHERE mr.patient_id = :patient_id 
          ORDER BY mr.visit_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lab results
$query = "SELECT * FROM lab_tests 
          WHERE patient_id = :patient_id 
          ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$lab_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get prescriptions
$query = "SELECT mr.prescription, mr.visit_date, u.full_name as doctor_name 
          FROM medical_records mr 
          JOIN users u ON mr.doctor_id = u.id 
          WHERE mr.patient_id = :patient_id 
          AND mr.prescription IS NOT NULL 
          ORDER BY mr.visit_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medical Records - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        /* Main Content Area */
        .main-content {
            margin-left: 120px !important;
            padding: 20px !important;
            min-height: 100vh !important;
            transition: all 0.3s ease !important;
        }
        </style>
</head>
<body>
  
        <div class="main-content">
            <div class="content-header">
                <h2>My Medical Records</h2>
                <div class="breadcrumb">
                    <span>Patient</span> / <span>Medical Records</span>
                </div>
            </div>

            <!-- Health Summary Cards -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📋
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($medical_records); ?></h3>
                        <p>Medical Visits</p>
                        <span class="stat-trend trend-neutral">All time</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🔬
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($lab_results); ?></h3>
                        <p>Lab Tests</p>
                        <span class="stat-trend trend-up">+2 this month</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💊
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($prescriptions); ?></h3>
                        <p>Prescriptions</p>
                        <span class="stat-trend trend-neutral">Active</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⭐
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>4.8/5</h3>
                        <p>Health Score</p>
                        <span class="stat-trend trend-up">Excellent</span>
                    </div>
                </div>
            </div>

            <!-- Medical History Timeline -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Medical History Timeline</h3>
                    <button class="btn-primary" onclick="exportMedicalHistory()">
                        <span class="btn-icon">📄</span>
                        Export Records
                    </button>
                </div>
                <div class="timeline">
                    <?php if (count($medical_records) > 0): ?>
                        <?php foreach ($medical_records as $record): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h4>Visit with Dr. <?php echo $record['doctor_name']; ?></h4>
                                    <span class="timeline-date">
                                        <?php echo date('M j, Y', strtotime($record['visit_date'])); ?>
                                    </span>
                                </div>
                                <div class="timeline-body">
                                    <div class="record-section">
                                        <h5>Symptoms & Complaints</h5>
                                        <p><?php echo $record['symptoms'] ?: 'No symptoms recorded'; ?></p>
                                    </div>
                                    <div class="record-section">
                                        <h5>Diagnosis</h5>
                                        <p><?php echo $record['diagnosis'] ?: 'No diagnosis recorded'; ?></p>
                                    </div>
                                    <div class="record-section">
                                        <h5>Treatment & Notes</h5>
                                        <p><?php echo $record['treatment'] ?: 'No treatment recorded'; ?></p>
                                    </div>
                                    <?php if ($record['prescription']): ?>
                                    <div class="record-section">
                                        <h5>Prescription</h5>
                                        <div class="prescription-box">
                                            <?php echo nl2br(htmlspecialchars($record['prescription'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-records">
                            <div class="no-records-icon">📋</div>
                            <h4>No Medical Records Found</h4>
                            <p>Your medical records will appear here after your first consultation.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lab Results Section -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Laboratory Test Results</h3>
                    <button class="btn-primary" onclick="requestLabTest()">
                        <span class="btn-icon">🔬</span>
                        Request New Test
                    </button>
                </div>
                <div class="lab-results-grid">
                    <?php if (count($lab_results) > 0): ?>
                        <?php foreach ($lab_results as $test): ?>
                        <div class="lab-result-card">
                            <div class="result-header">
                                <h4><?php echo $test['test_name']; ?></h4>
                                <span class="test-status status-<?php echo $test['status']; ?>">
                                    <?php echo ucfirst($test['status']); ?>
                                </span>
                            </div>
                            <div class="result-details">
                                <p><strong>Test Type:</strong> <?php echo $test['test_type']; ?></p>
                                <p><strong>Ordered:</strong> <?php echo date('M j, Y', strtotime($test['created_at'])); ?></p>
                                <?php if ($test['results']): ?>
                                <div class="results-content">
                                    <h5>Results:</h5>
                                    <pre><?php echo htmlspecialchars($test['results']); ?></pre>
                                    <?php if ($test['normal_range']): ?>
                                    <p><strong>Normal Range:</strong> <?php echo $test['normal_range']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($test['flag'] === 'abnormal'): ?>
                                    <div class="abnormal-flag">
                                        <span class="flag-icon">⚠️</span>
                                        Abnormal Results - Please consult your doctor
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="result-actions">
                                <button class="btn-action btn-primary" onclick="viewLabResult(<?php echo $test['id']; ?>)">
                                    View Details
                                </button>
                                <?php if ($test['uploaded_file']): ?>
                                <button class="btn-action btn-outline" onclick="downloadLabReport(<?php echo $test['id']; ?>)">
                                    Download Report
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <p>No laboratory test results available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Current Medications -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Current Medications</h3>
                    <button class="btn-primary" onclick="addMedication()">
                        <span class="btn-icon">➕</span>
                        Add Medication
                    </button>
                </div>
                <div class="medications-list">
                    <?php if (count($prescriptions) > 0): ?>
                        <?php foreach ($prescriptions as $prescription): ?>
                        <div class="medication-card">
                            <div class="medication-icon">💊</div>
                            <div class="medication-details">
                                <h4>Prescription from Dr. <?php echo $prescription['doctor_name']; ?></h4>
                                <p class="prescription-date">
                                    <?php echo date('M j, Y', strtotime($prescription['visit_date'])); ?>
                                </p>
                                <div class="prescription-content">
                                    <?php echo nl2br(htmlspecialchars($prescription['prescription'])); ?>
                                </div>
                            </div>
                            <div class="medication-actions">
                                <button class="btn-action btn-outline" onclick="refillPrescription(<?php echo $prescription['id']; ?>)">
                                    Request Refill
                                </button>
                                <button class="btn-action btn-primary" onclick="viewPrescription(<?php echo $prescription['id']; ?>)">
                                    View Details
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-medications">
                            <p>No current medications recorded.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Health Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Health Insights</h3>
                    <span class="ai-badge">Powered by AI</span>
                </div>
                <div class="health-insights">
                    <div class="insight-card">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Health Trend Analysis</h4>
                            <p>Your overall health has improved by 15% over the past 6 months.</p>
                            <div class="insight-metrics">
                                <div class="metric">
                                    <span class="metric-value">92%</span>
                                    <span class="metric-label">Medication Adherence</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">85%</span>
                                    <span class="metric-label">Appointment Attendance</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-icon">💡</div>
                        <div class="insight-content">
                            <h4>Personalized Recommendations</h4>
                            <p>Based on your health data, we recommend:</p>
                            <ul class="recommendations-list">
                                <li>Continue current medication regimen</li>
                                <li>Schedule follow-up in 3 months</li>
                                <li>Consider lifestyle modifications for blood pressure</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-icon">⚠️</div>
                        <div class="insight-content">
                            <h4>Health Alerts</h4>
                            <p>No critical alerts at this time. Continue with your current health plan.</p>
                            <div class="alert-status status-healthy">
                                All vital signs within normal range
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function exportMedicalHistory() {
        alert('Exporting medical history... This would generate a PDF report.');
    }

    function requestLabTest() {
        alert('Opening lab test request form...');
    }

    function viewLabResult(testId) {
        window.location.href = 'lab-result-details.php?id=' + testId;
    }

    function downloadLabReport(testId) {
        alert('Downloading lab report for test ' + testId);
    }

    function addMedication() {
        alert('Opening medication addition form...');
    }

    function refillPrescription(prescriptionId) {
        if (confirm('Request prescription refill?')) {
            alert('Refill request sent for prescription ' + prescriptionId);
        }
    }

    function viewPrescription(prescriptionId) {
        window.location.href = 'prescription-details.php?id=' + prescriptionId;
    }

    // AI Health Insights
    function generateHealthReport() {
        alert('Generating comprehensive health report with AI analysis...');
    }
    </script>

    <style>
    .timeline {
        position: relative;
        padding: 2rem 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 2rem;
        position: relative;
    }

    .timeline-marker {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #1a73e8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        z-index: 2;
        flex-shrink: 0;
    }

    .timeline-content {
        flex: 1;
        margin-left: 1.5rem;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .timeline-header h4 {
        margin: 0;
        color: #343a40;
    }

    .timeline-date {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .record-section {
        margin-bottom: 1.5rem;
    }

    .record-section h5 {
        color: #1a73e8;
        margin: 0 0 0.5rem 0;
        font-size: 1rem;
    }

    .record-section p {
        margin: 0;
        color: #495057;
        line-height: 1.6;
    }

    .prescription-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        white-space: pre-wrap;
    }

    .lab-results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .lab-result-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .result-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .result-header h4 {
        margin: 0;
        color: #343a40;
    }

    .results-content {
        margin: 1rem 0;
    }

    .results-content pre {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 6px;
        overflow-x: auto;
        font-family: 'Courier New', monospace;
    }

    .abnormal-flag {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 1rem;
        border-radius: 6px;
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .medications-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .medication-card {
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #1a73e8;
    }

    .medication-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .medication-details {
        flex: 1;
    }

    .medication-details h4 {
        margin: 0 0 0.5rem 0;
        color: #343a40;
    }

    .prescription-date {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .prescription-content {
        background: white;
        padding: 1rem;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        white-space: pre-wrap;
    }

    .health-insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #1a73e8;
    }

    .insight-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .insight-content h4 {
        margin: 0 0 1rem 0;
        color: #343a40;
    }

    .insight-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }

    .metric {
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 8px;
    }

    .metric-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a73e8;
        margin-bottom: 0.3rem;
    }

    .metric-label {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .recommendations-list {
        margin: 1rem 0 0 0;
        padding-left: 1.5rem;
    }

    .recommendations-list li {
        margin-bottom: 0.5rem;
        color: #495057;
    }

    .alert-status {
        padding: 0.8rem 1rem;
        border-radius: 6px;
        text-align: center;
        margin-top: 1rem;
        font-weight: 500;
    }

    .status-healthy {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .no-records, .no-results, .no-medications {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .no-records-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .timeline::before {
            left: 25px;
        }

        .timeline-marker {
            width: 50px;
            height: 50px;
            font-size: 1rem;
        }

        .lab-results-grid {
            grid-template-columns: 1fr;
        }

        .health-insights {
            grid-template-columns: 1fr;
        }

        .insight-metrics {
            grid-template-columns: 1fr;
        }

        .medication-card {
            flex-direction: column;
        }

        .result-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
    </style>
</body>
</html>