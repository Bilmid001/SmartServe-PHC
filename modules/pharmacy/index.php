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

// Get pharmacy statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM pharmacy_inventory WHERE quantity <= reorder_level";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM pharmacy_inventory WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['expiring_soon'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM (SELECT DISTINCT patient_id FROM medical_records WHERE DATE(created_at) = CURDATE() AND prescription IS NOT NULL) as today_prescriptions";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_prescriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT SUM(quantity * unit_price) as total FROM pharmacy_inventory";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['inventory_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get pending prescriptions
$query = "SELECT mr.*, p.full_name, p.patient_id, u.full_name as doctor_name 
          FROM medical_records mr 
          JOIN patients p ON mr.patient_id = p.id 
          JOIN users u ON mr.doctor_id = u.id 
          WHERE mr.prescription IS NOT NULL 
          AND mr.prescription_status IS NULL 
          ORDER BY mr.visit_date DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
// $stmt->execute();
$pending_prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard - PHCHMS</title>
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
    
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header pharmacy-content-header">
                <div class="header-left">
                    <h2>Pharmacy Management Dashboard</h2>
                    <p class="welcome-message">Medication management and inventory control center</p>
                </div>
                <div class="header-right">
                    <div class="pharmacy-controls">
                        <button class="control-btn btn-primary" onclick="dispensePrescription()">
                            <span class="btn-icon">💊</span>
                            Dispense
                        </button>
                        <button class="control-btn btn-success" onclick="manageInventory()">
                            <span class="btn-icon">📦</span>
                            Manage Stock
                        </button>
                        <div class="pharmacy-status">
                            <span class="status-indicator status-healthy"></span>
                            <span class="status-text">Pharmacy Open</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pharmacy Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['low_stock']; ?></h3>
                        <p>Low Stock Items</p>
                        <span class="stat-trend trend-up">Attention needed</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewLowStock()">Review</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['expiring_soon']; ?></h3>
                        <p>Expiring Soon</p>
                        <span class="stat-trend trend-up">Within 30 days</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewExpiring()">Check</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💊
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['today_prescriptions']; ?></h3>
                        <p>Today's Prescriptions</p>
                        <span class="stat-trend trend-up">+15% from yesterday</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewPrescriptions()">Process</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💰
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($stats['inventory_value'], 2); ?></h3>
                        <p>Inventory Value</p>
                        <span class="stat-trend trend-up">+2.5% this week</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewInventory()">Details</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Pending Prescriptions -->
            <div class="content-grid">
                <!-- Quick Actions -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Quick Pharmacy Actions</h3>
                        <span class="ai-badge">AI Optimized</span>
                    </div>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="dispensePrescription()">
                            <span class="action-icon">💊</span>
                            <span class="action-text">Dispense Drug</span>
                        </button>
                        <button class="quick-action-btn" onclick="checkInventory()">
                            <span class="action-icon">📦</span>
                            <span class="action-text">Check Stock</span>
                        </button>
                        <button class="quick-action-btn" onclick="orderSupplies()">
                            <span class="action-icon">🛒</span>
                            <span class="action-text">Order Supplies</span>
                        </button>
                        <button class="quick-action-btn" onclick="checkInteractions()">
                            <span class="action-icon">⚠️</span>
                            <span class="action-text">Check Interactions</span>
                        </button>
                        <button class="quick-action-btn" onclick="generateReport()">
                            <span class="action-icon">📊</span>
                            <span class="action-text">Pharmacy Report</span>
                        </button>
                        <button class="quick-action-btn" onclick="patientCounseling()">
                            <span class="action-icon">💬</span>
                            <span class="action-text">Patient Counseling</span>
                        </button>
                    </div>
                </div>

                <!-- Pending Prescriptions -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Pending Prescriptions</h3>
                        <a href="prescriptions.php" class="view-all">View All</a>
                    </div>
                    <div class="pending-prescriptions">
                        <?php if (count($pending_prescriptions) > 0): ?>
                            <?php foreach ($pending_prescriptions as $prescription): ?>
                            <div class="prescription-item">
                                <div class="prescription-header">
                                    <h4><?php echo $prescription['full_name']; ?></h4>
                                    <span class="patient-id">ID: <?php echo $prescription['patient_id']; ?></span>
                                </div>
                                <div class="prescription-info">
                                    <p class="doctor-name">Dr. <?php echo $prescription['doctor_name']; ?></p>
                                    <p class="prescription-date">
                                        <?php echo date('M j, Y', strtotime($prescription['visit_date'])); ?>
                                    </p>
                                </div>
                                <div class="prescription-actions">
                                    <button class="btn-action btn-success" onclick="dispensePrescription(<?php echo $prescription['id']; ?>)">
                                        Dispense
                                    </button>
                                    <button class="btn-action btn-outline" onclick="viewPrescription(<?php echo $prescription['id']; ?>)">
                                        Details
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-prescriptions">
                                <p>No pending prescriptions at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Critical Stock Alerts -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Critical Stock Alerts</h3>
                    <span class="alert-badge">Immediate Action</span>
                </div>
                <div class="stock-alerts">
                    <div class="alert-item critical">
                        <div class="alert-icon">🚨</div>
                        <div class="alert-content">
                            <h4>Amoxicillin 500mg - Critical Low</h4>
                            <p>Current stock: 8 units. Reorder level: 50 units.</p>
                            <div class="alert-meta">
                                <span class="alert-supplier">Supplier: PharmaCorp</span>
                                <span class="alert-lead-time">Lead time: 2 days</span>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-action btn-primary" onclick="emergencyOrder('Amoxicillin')">Emergency Order</button>
                            <button class="btn-action btn-outline" onclick="viewAlternatives('Amoxicillin')">Alternatives</button>
                        </div>
                    </div>

                    <div class="alert-item warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <h4>Paracetamol 500mg - Low Stock</h4>
                            <p>Current stock: 45 units. Reorder level: 50 units.</p>
                            <div class="alert-meta">
                                <span class="alert-supplier">Supplier: MedSupply</span>
                                <span class="alert-lead-time">Lead time: 1 day</span>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-action btn-primary" onclick="reorderItem('Paracetamol')">Reorder</button>
                            <button class="btn-action btn-outline" onclick="viewStock('Paracetamol')">Stock Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Pharmacy Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Pharmacy Insights</h3>
                    <span class="ai-badge">Predictive Analytics</span>
                </div>
                <div class="pharmacy-insights">
                    <div class="insight-card">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Demand Forecast</h4>
                            <p>Expected 20% increase in antibiotic prescriptions next week due to seasonal flu.</p>
                            <div class="insight-metrics">
                                <div class="metric">
                                    <span class="metric-value">Amoxicillin</span>
                                    <span class="metric-label">+25% demand</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-value">Azithromycin</span>
                                    <span class="metric-label">+18% demand</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">💡</div>
                        <div class="insight-content">
                            <h4>Cost Optimization</h4>
                            <p>Bulk purchase of Ibuprofen could save 15% on monthly medication costs.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="viewBulkPricing()">View Pricing</button>
                                <button class="btn-action btn-outline" onclick="createPurchaseOrder()">Create PO</button>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">🔍</div>
                        <div class="insight-content">
                            <h4>Drug Interaction Alert</h4>
                            <p>New research indicates potential interaction between Medication A and B.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="viewInteractionDetails()">Details</button>
                                <button class="btn-action btn-outline" onclick="updateGuidelines()">Update Guidelines</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Dispensing Activity -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Today's Dispensing Activity</h3>
                    <button class="btn-primary" onclick="exportDispensingLog()">
                        <span class="btn-icon">📄</span>
                        Export Log
                    </button>
                </div>
                <div class="dispensing-activity">
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
                                <td>08:30 AM</td>
                                <td>John Smith (PAT001)</td>
                                <td>Amoxicillin 500mg</td>
                                <td>20 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-completed">Dispensed</span></td>
                            </tr>
                            <tr>
                                <td>09:15 AM</td>
                                <td>Maria Garcia (PAT002)</td>
                                <td>Lisinopril 10mg</td>
                                <td>30 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-completed">Dispensed</span></td>
                            </tr>
                            <tr>
                                <td>10:45 AM</td>
                                <td>Robert Johnson (PAT003)</td>
                                <td>Metformin 500mg</td>
                                <td>60 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-completed">Dispensed</span></td>
                            </tr>
                            <tr>
                                <td>11:30 AM</td>
                                <td>Sarah Wilson (PAT004)</td>
                                <td>Atorvastatin 20mg</td>
                                <td>30 tablets</td>
                                <td>You</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showPharmacyAlerts() {
        alert('Showing pharmacy alerts and notifications...');
    }

    function quickDispense() {
        const patientId = prompt('Enter patient ID:');
        if (patientId) {
            window.location.href = 'prescriptions.php?patient=' + patientId;
        }
    }

    function dispensePrescription(prescriptionId) {
        if (prescriptionId) {
            window.location.href = 'dispense.php?id=' + prescriptionId;
        } else {
            window.location.href = 'prescriptions.php';
        }
    }

    function manageInventory() {
        window.location.href = 'inventory.php';
    }

    function viewLowStock() {
        window.location.href = 'inventory.php?filter=low-stock';
    }

    function viewExpiring() {
        window.location.href = 'inventory.php?filter=expiring';
    }

    function viewPrescriptions() {
        window.location.href = 'prescriptions.php';
    }

    function viewInventory() {
        window.location.href = 'inventory.php';
    }

    function checkInventory() {
        const drugName = prompt('Enter drug name to check:');
        if (drugName) {
            window.location.href = 'inventory.php?search=' + encodeURIComponent(drugName);
        }
    }

    function orderSupplies() {
        window.location.href = 'purchase-orders.php?action=new';
    }

    function checkInteractions() {
        window.location.href = 'drug-interactions.php';
    }

    function generateReport() {
        window.location.href = 'reports.php';
    }

    function patientCounseling() {
        window.location.href = 'patient-counseling.php';
    }

    function viewPrescription(prescriptionId) {
        window.location.href = 'prescription-details.php?id=' + prescriptionId;
    }

    function emergencyOrder(drugName) {
        if (confirm(`Place emergency order for ${drugName}?`)) {
            alert(`Emergency order placed for ${drugName}`);
        }
    }

    function viewAlternatives(drugName) {
        alert(`Showing alternatives for ${drugName}`);
    }

    function reorderItem(drugName) {
        if (confirm(`Reorder ${drugName}?`)) {
            alert(`Reorder initiated for ${drugName}`);
        }
    }

    function viewStock(drugName) {
        alert(`Showing stock details for ${drugName}`);
    }

    function viewBulkPricing() {
        alert('Showing bulk pricing options...');
    }

    function createPurchaseOrder() {
        window.location.href = 'purchase-orders.php?action=new';
    }

    function viewInteractionDetails() {
        alert('Showing drug interaction details...');
    }

    function updateGuidelines() {
        alert('Updating clinical guidelines...');
    }

    function exportDispensingLog() {
        alert('Exporting today\'s dispensing log...');
    }
    </script>

    <style>
    .pharmacy-header {
        background: linear-gradient(135deg, #8e44ad, #7d3c98) !important;
    }

    .pharmacy-sidebar {
        background: linear-gradient(180deg, #8e44ad 0%, #7d3c98 100%) !important;
    }

    .pharmacy-sidebar .nav-link {
        color: #e8daef !important;
        border-left-color: transparent;
    }

    .pharmacy-sidebar .nav-link:hover,
    .pharmacy-sidebar .nav-link.active {
        background: rgba(142, 68, 173, 0.2);
        color: white !important;
        border-left-color: white;
    }

    .pharmacy-sidebar .nav-section-title {
        color: #d2b4de !important;
    }

    .pharmacy-content-header {
        background: linear-gradient(135deg, #8e44ad, #7d3c98) !important;
        color: white !important;
    }

    .pharmacy-content-header h2,
    .pharmacy-content-header .welcome-message {
        color: white !important;
    }

    .pharmacy-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pharmacy-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.2rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: white;
    }

    .pending-prescriptions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .prescription-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.2rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #8e44ad;
    }

    .prescription-header {
        flex: 1;
    }

    .prescription-header h4 {
        margin: 0 0 0.3rem 0;
        color: #2c3e50;
    }

    .patient-id {
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .prescription-info {
        min-width: 150px;
    }

    .doctor-name, .prescription-date {
        margin: 0.2rem 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .prescription-actions {
        display: flex;
        gap: 0.5rem;
    }

    .stock-alerts {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .alert-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid;
    }

    .alert-item.critical {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), transparent);
        border-left-color: #e74c3c;
    }

    .alert-item.warning {
        background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), transparent);
        border-left-color: #f39c12;
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
        color: #2c3e50;
    }

    .alert-content p {
        margin: 0 0 0.8rem 0;
        color: #7f8c8d;
    }

    .alert-meta {
        display: flex;
        gap: 1.5rem;
    }

    .alert-supplier, .alert-lead-time {
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .alert-actions {
        display: flex;
        gap: 0.5rem;
    }

    .pharmacy-insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #8e44ad;
    }

    .insight-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .insight-content h4 {
        margin: 0 0 0.8rem 0;
        color: #2c3e50;
    }

    .insight-content p {
        margin: 0 0 1.5rem 0;
        color: #7f8c8d;
        line-height: 1.5;
    }

    .insight-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .insight-metrics .metric {
        text-align: center;
        padding: 0.8rem;
        background: white;
        border-radius: 6px;
    }

    .metric-value {
        display: block;
        font-weight: 700;
        color: #8e44ad;
        margin-bottom: 0.3rem;
    }

    .metric-label {
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .dispensing-activity {
        overflow-x: auto;
    }

    .no-prescriptions {
        text-align: center;
        padding: 2rem;
        color: #7f8c8d;
    }

    @media (max-width: 768px) {
        .pharmacy-insights {
            grid-template-columns: 1fr;
        }

        .prescription-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .prescription-actions {
            align-self: stretch;
            justify-content: stretch;
        }

        .prescription-actions .btn-action {
            flex: 1;
        }

        .alert-item {
            flex-direction: column;
        }

        .alert-actions {
            align-self: stretch;
            justify-content: stretch;
        }

        .alert-actions .btn-action {
            flex: 1;
        }

        .insight-metrics {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }

        .alert-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
    </style>
</body>
</html>