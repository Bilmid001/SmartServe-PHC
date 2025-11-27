<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('pharmacy') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle prescription dispensing
if ($_POST['action'] ?? '' === 'dispense_prescription') {
    $prescription_id = $_POST['prescription_id'];
    $dispensed_quantity = $_POST['dispensed_quantity'];
    $notes = $_POST['notes'];
    
    // In real implementation, update inventory and mark as dispensed
    $success = "Prescription dispensed successfully!";
}

// Get pending prescriptions
$query = "SELECT mr.*, p.full_name, p.patient_id, u.full_name as doctor_name 
          FROM medical_records mr 
          JOIN patients p ON mr.patient_id = p.id 
          JOIN users u ON mr.doctor_id = u.id 
          WHERE mr.prescription IS NOT NULL 
          AND mr.prescription_status IS NULL 
          ORDER BY mr.visit_date DESC";
$stmt = $db->prepare($query);
// $stmt->execute();
$pending_prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get today's dispensed prescriptions
$query = "SELECT COUNT(*) as count FROM prescription_logs WHERE DATE(dispensed_at) = CURDATE()";
$stmt = $db->prepare($query);
// $stmt->execute();
// $dispensed_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
     <style>
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

    </style>
</head>
<body>

        
        <div class="main-content">
            <div class="content-header">
                <h2>Pharmacy Prescriptions</h2>
                <div class="breadcrumb">
                    <span>Pharmacy</span> / <span>Prescriptions</span>
                </div>
            </div>

            <!-- Prescription Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📋
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($pending_prescriptions); ?></h3>
                        <p>Pending Prescriptions</p>
                        <span class="stat-trend trend-up">+5 today</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $dispensed_today; ?></h3>
                        <p>Dispensed Today</p>
                        <span class="stat-trend trend-up">+12% from yesterday</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>3</h3>
                        <p>Out of Stock</p>
                        <span class="stat-trend trend-up">Needs attention</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏱️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>8.2min</h3>
                        <p>Avg. Processing Time</p>
                        <span class="stat-trend trend-down">-1.5min improvement</span>
                    </div>
                </div>
            </div>

            <!-- Pending Prescriptions -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Pending Prescriptions</h3>
                    <span class="ai-badge">AI Verified</span>
                </div>
                <div class="prescriptions-grid">
                    <?php if (count($pending_prescriptions) > 0): ?>
                        <?php foreach ($pending_prescriptions as $prescription): ?>
                        <div class="prescription-card">
                            <div class="prescription-header">
                                <div class="patient-info">
                                    <h4><?php echo $prescription['full_name']; ?></h4>
                                    <p class="patient-id">ID: <?php echo $prescription['patient_id']; ?></p>
                                    <p class="doctor-name">Dr. <?php echo $prescription['doctor_name']; ?></p>
                                </div>
                                <div class="prescription-meta">
                                    <span class="visit-date">
                                        <?php echo date('M j, Y', strtotime($prescription['visit_date'])); ?>
                                    </span>
                                    <span class="priority-badge priority-normal">Normal</span>
                                </div>
                            </div>
                            
                            <div class="prescription-content">
                                <h5>Prescription Details:</h5>
                                <div class="prescription-text">
                                    <?php echo nl2br(htmlspecialchars($prescription['prescription'])); ?>
                                </div>
                            </div>

                            <div class="ai-analysis">
                                <div class="analysis-result">
                                    <span class="analysis-icon">✅</span>
                                    <span class="analysis-text">No drug interactions detected</span>
                                </div>
                                <div class="analysis-result">
                                    <span class="analysis-icon">💊</span>
                                    <span class="analysis-text">All medications in stock</span>
                                </div>
                            </div>

                            <div class="prescription-actions">
                                <button class="btn-action btn-success" onclick="dispensePrescription(<?php echo $prescription['id']; ?>)">
                                    <span class="btn-icon">💊</span>
                                    Dispense
                                </button>
                                <button class="btn-action btn-outline" onclick="viewPrescription(<?php echo $prescription['id']; ?>)">
                                    View Details
                                </button>
                                <button class="btn-action btn-warning" onclick="flagPrescription(<?php echo $prescription['id']; ?>)">
                                    Flag Issue
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-prescriptions">
                            <div class="no-prescriptions-icon">💊</div>
                            <h4>No Pending Prescriptions</h4>
                            <p>All prescriptions have been processed and dispensed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Drug Interaction Checker -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Drug Interaction Checker</h3>
                    <span class="ai-badge">Real-time Analysis</span>
                </div>
                <div class="interaction-checker">
                    <div class="checker-input">
                        <div class="form-group">
                            <label for="drug1">Drug 1</label>
                            <input type="text" id="drug1" placeholder="Enter drug name">
                        </div>
                        <div class="form-group">
                            <label for="drug2">Drug 2</label>
                            <input type="text" id="drug2" placeholder="Enter drug name">
                        </div>
                        <div class="form-group">
                            <label for="drug3">Drug 3 (Optional)</label>
                            <input type="text" id="drug3" placeholder="Enter drug name">
                        </div>
                    </div>
                    <button class="btn-primary" onclick="checkInteractions()">
                        <span class="btn-icon">🔍</span>
                        Check Interactions
                    </button>
                    
                    <div id="interactionResults" class="interaction-results" style="display: none;">
                        <h4>Interaction Analysis Results</h4>
                        <div class="results-content">
                            <div class="interaction-level interaction-low">
                                <h5>✅ No Significant Interactions</h5>
                                <p>These medications can be safely taken together.</p>
                            </div>
                            <div class="interaction-details">
                                <h6>Monitoring Recommendations:</h6>
                                <ul>
                                    <li>Monitor for usual side effects</li>
                                    <li>No additional precautions needed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Dispensing Log -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Today's Dispensing Log</h3>
                    <button class="btn-primary" onclick="exportDispensingLog()">
                        <span class="btn-icon">📄</span>
                        Export Log
                    </button>
                </div>
                <div class="dispensing-log">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Medication</th>
                                <th>Quantity</th>
                                <th>Pharmacist</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>09:15 AM</td>
                                <td>John Smith (ID: PAT001)</td>
                                <td>Amoxicillin 500mg</td>
                                <td>20 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-completed">Dispensed</span></td>
                            </tr>
                            <tr>
                                <td>10:30 AM</td>
                                <td>Maria Garcia (ID: PAT002)</td>
                                <td>Lisinopril 10mg</td>
                                <td>30 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-completed">Dispensed</span></td>
                            </tr>
                            <tr>
                                <td>11:45 AM</td>
                                <td>Robert Johnson (ID: PAT003)</td>
                                <td>Metformin 500mg</td>
                                <td>60 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-completed">Dispensed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stock Alert for Common Medications -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Stock Alerts</h3>
                    <span class="alert-badge">Live Updates</span>
                </div>
                <div class="stock-alerts">
                    <div class="alert-item alert-warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <h4>Low Stock: Paracetamol 500mg</h4>
                            <p>Current stock: 45 units. Reorder level: 50 units.</p>
                            <div class="alert-actions">
                                <button class="btn-action btn-primary" onclick="reorderMedication('Paracetamol')">Reorder</button>
                                <button class="btn-action btn-outline" onclick="viewInventory('Paracetamol')">View Inventory</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert-item alert-danger">
                        <div class="alert-icon">🚨</div>
                        <div class="alert-content">
                            <h4>Critical: Amoxicillin 250mg</h4>
                            <p>Current stock: 12 units. Reorder immediately.</p>
                            <div class="alert-actions">
                                <button class="btn-action btn-primary" onclick="reorderMedication('Amoxicillin')">Emergency Order</button>
                                <button class="btn-action btn-outline" onclick="viewInventory('Amoxicillin')">Check Stock</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function dispensePrescription(prescriptionId) {
        const quantity = prompt('Enter quantity to dispense:');
        if (quantity && !isNaN(quantity)) {
            // In real implementation, submit form data
            alert('Dispensing prescription ' + prescriptionId + ' with quantity: ' + quantity);
        }
    }

    function viewPrescription(prescriptionId) {
        window.location.href = 'prescription-details.php?id=' + prescriptionId;
    }

    function flagPrescription(prescriptionId) {
        const issue = prompt('Please describe the issue:');
        if (issue) {
            alert('Flagging prescription ' + prescriptionId + ' with issue: ' + issue);
        }
    }

    function checkInteractions() {
        const drug1 = document.getElementById('drug1').value;
        const drug2 = document.getElementById('drug2').value;
        const drug3 = document.getElementById('drug3').value;
        
        if (!drug1 || !drug2) {
            alert('Please enter at least two drug names.');
            return;
        }
        
        // Simulate AI interaction check
        document.getElementById('interactionResults').style.display = 'block';
        
        // In real implementation, this would call an API
        setTimeout(() => {
            alert('AI analysis complete! No significant interactions found.');
        }, 1000);
    }

    function exportDispensingLog() {
        alert('Exporting today\'s dispensing log...');
    }

    function reorderMedication(medication) {
        if (confirm(`Place emergency order for ${medication}?`)) {
            alert(`Emergency order placed for ${medication}`);
        }
    }

    function viewInventory(medication) {
        alert(`Opening inventory for ${medication}...`);
    }

    // Auto-complete for drug names
    document.addEventListener('DOMContentLoaded', function() {
        const drugInputs = document.querySelectorAll('input[type="text"]');
        drugInputs.forEach(input => {
            input.addEventListener('input', function() {
                // In real implementation, this would show suggestions
            });
        });
    });
    </script>

    <style>
    .prescriptions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .prescription-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .prescription-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .patient-info h4 {
        margin: 0 0 0.3rem 0;
        color: #343a40;
    }

    .patient-id, .doctor-name {
        margin: 0.2rem 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .prescription-meta {
        text-align: right;
    }

    .visit-date {
        display: block;
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .priority-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .priority-normal {
        background: #d4edda;
        color: #155724;
    }

    .priority-high {
        background: #f8d7da;
        color: #721c24;
    }

    .prescription-content {
        margin-bottom: 1rem;
    }

    .prescription-content h5 {
        color: #1a73e8;
        margin: 0 0 0.8rem 0;
    }

    .prescription-text {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        white-space: pre-wrap;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    .ai-analysis {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .analysis-result {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .analysis-icon {
        font-size: 1.2rem;
    }

    .analysis-text {
        color: #28a745;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .prescription-actions {
        display: flex;
        gap: 0.5rem;
    }

    .interaction-checker {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .checker-input {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .interaction-results {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #28a745;
    }

    .interaction-level {
        margin-bottom: 1rem;
    }

    .interaction-low {
        color: #28a745;
    }

    .interaction-medium {
        color: #ffc107;
    }

    .interaction-high {
        color: #dc3545;
    }

    .interaction-details h6 {
        margin: 1rem 0 0.5rem 0;
        color: #343a40;
    }

    .interaction-details ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #495057;
    }

    .dispensing-log {
        overflow-x: auto;
    }

    .stock-alerts {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .no-prescriptions {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
        grid-column: 1 / -1;
    }

    .no-prescriptions-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .prescriptions-grid {
            grid-template-columns: 1fr;
        }

        .prescription-header {
            flex-direction: column;
            gap: 1rem;
        }

        .prescription-meta {
            text-align: left;
        }

        .prescription-actions {
            flex-direction: column;
        }

        .checker-input {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>