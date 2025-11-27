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

// Get billing statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM billing WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_bills'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT SUM(total_amount) as total FROM billing WHERE DATE(created_at) = CURDATE() AND status = 'paid'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$query = "SELECT COUNT(*) as total FROM billing WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_bills'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT SUM(total_amount) as total FROM billing WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Get recent bills
$query = "SELECT b.*, p.full_name, p.patient_id 
          FROM billing b 
          JOIN patients p ON b.patient_id = p.id 
          ORDER BY b.created_at DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Billing - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h2>Pharmacy Billing</h2>
                <div class="breadcrumb">
                    <span>Pharmacy</span> / <span>Billing</span>
                </div>
            </div>

            <!-- Billing Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💰
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($stats['today_revenue'], 2); ?></h3>
                        <p>Today's Revenue</p>
                        <span class="stat-trend trend-up">+15% from yesterday</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewTodayBills()">View</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📄
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['today_bills']; ?></h3>
                        <p>Today's Bills</p>
                        <span class="stat-trend trend-up">+8 bills</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="createNewBill()">New Bill</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏳
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending_bills']; ?></h3>
                        <p>Pending Bills</p>
                        <span class="stat-trend trend-up">Needs attention</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewPendingBills()">Process</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💳
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($stats['pending_amount'], 2); ?></h3>
                        <p>Pending Amount</p>
                        <span class="stat-trend trend-up">Uncollected</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="collectPayments()">Collect</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Quick Billing Actions</h3>
                        <span class="ai-badge">Smart Tools</span>
                    </div>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="createNewBill()">
                            <span class="action-icon">➕</span>
                            <span class="action-text">Create New Bill</span>
                        </button>
                        <button class="quick-action-btn" onclick="processPayments()">
                            <span class="action-icon">💳</span>
                            <span class="action-text">Process Payments</span>
                        </button>
                        <button class="quick-action-btn" onclick="generateReport()">
                            <span class="action-icon">📊</span>
                            <span class="action-text">Generate Report</span>
                        </button>
                        <button class="quick-action-btn" onclick="viewOutstanding()">
                            <span class="action-icon">📋</span>
                            <span class="action-text">Outstanding Bills</span>
                        </button>
                        <button class="quick-action-btn" onclick="manageTax()">
                            <span class="action-icon">🏛️</span>
                            <span class="action-text">Tax Settings</span>
                        </button>
                        <button class="quick-action-btn" onclick="exportData()">
                            <span class="action-icon">📤</span>
                            <span class="action-text">Export Data</span>
                        </button>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3>Create New Bill</h3>
                        <button class="btn-primary" onclick="createNewBill()">
                            <span class="btn-icon">➕</span>
                            New Bill
                        </button>
                    </div>
                    <div class="bill-creation">
                        <form class="quick-bill-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="patient_search">Patient Search</label>
                                    <input type="text" id="patient_search" placeholder="Enter patient name or ID">
                                </div>
                                <div class="form-group">
                                    <label for="bill_date">Bill Date</label>
                                    <input type="date" id="bill_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="items">Items/Services</label>
                                <div class="bill-items">
                                    <div class="bill-item">
                                        <input type="text" placeholder="Item description" class="item-desc">
                                        <input type="number" placeholder="Qty" class="item-qty" min="1" value="1">
                                        <input type="number" placeholder="Price" class="item-price" step="0.01" min="0">
                                        <span class="item-total">$0.00</span>
                                        <button type="button" class="btn-remove" onclick="removeItem(this)">❌</button>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-item" onclick="addItem()">
                                    <span class="btn-icon">➕</span>
                                    Add Item
                                </button>
                            </div>
                            <div class="bill-summary">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">$0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span>Tax (8%):</span>
                                    <span id="tax">$0.00</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total:</span>
                                    <span id="total">$0.00</span>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="submit-btn" onclick="saveBill()">
                                    <span class="btn-icon">💾</span>
                                    Save Bill
                                </button>
                                <button type="button" class="btn-secondary" onclick="clearForm()">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Bills -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Bills</h3>
                    <div class="filter-options">
                        <select id="billFilter" onchange="filterBills()">
                            <option value="all">All Bills</option>
                            <option value="today">Today</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="bills-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Bill ID</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bills as $bill): ?>
                            <tr>
                                <td><strong>#<?php echo $bill['id']; ?></strong></td>
                                <td>
                                    <div class="patient-info">
                                        <strong><?php echo htmlspecialchars($bill['full_name']); ?></strong>
                                        <span class="patient-id"><?php echo htmlspecialchars($bill['patient_id']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($bill['created_at'])); ?></td>
                                <td><?php echo $bill['item_count'] ?? 'N/A'; ?> items</td>
                                <td><strong>$<?php echo number_format($bill['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $bill['status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($bill['status'] ?? 'pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-primary" onclick="viewBill(<?php echo $bill['id']; ?>)">
                                            View
                                        </button>
                                        <button class="btn-action btn-outline" onclick="printBill(<?php echo $bill['id']; ?>)">
                                            Print
                                        </button>
                                        <?php if (($bill['status'] ?? 'pending') === 'pending'): ?>
                                        <button class="btn-action btn-success" onclick="markPaid(<?php echo $bill['id']; ?>)">
                                            Paid
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Processing -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Payment Processing</h3>
                    <span class="ai-badge">Real-time</span>
                </div>
                <div class="payment-processing">
                    <div class="payment-methods">
                        <div class="method-card" onclick="processCashPayment()">
                            <div class="method-icon">💵</div>
                            <div class="method-info">
                                <h4>Cash Payment</h4>
                                <p>Process cash transactions</p>
                            </div>
                        </div>
                        <div class="method-card" onclick="processCardPayment()">
                            <div class="method-icon">💳</div>
                            <div class="method-info">
                                <h4>Card Payment</h4>
                                <p>Credit/Debit card processing</p>
                            </div>
                        </div>
                        <div class="method-card" onclick="processInsurance()">
                            <div class="method-icon">🏥</div>
                            <div class="method-info">
                                <h4>Insurance Claim</h4>
                                <p>Process insurance payments</p>
                            </div>
                        </div>
                        <div class="method-card" onclick="processOnline()">
                            <div class="method-icon">🌐</div>
                            <div class="method-info">
                                <h4>Online Payment</h4>
                                <p>Digital payment processing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Financial Summary</h3>
                    <button class="btn-primary" onclick="generateFinancialReport()">
                        <span class="btn-icon">📈</span>
                        Generate Report
                    </button>
                </div>
                <div class="financial-summary">
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h4>Daily Revenue</h4>
                                <span class="trend-up">+12%</span>
                            </div>
                            <div class="summary-amount">$2,847.50</div>
                            <div class="summary-period">Today</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-header">
                                <h4>Weekly Revenue</h4>
                                <span class="trend-up">+8%</span>
                            </div>
                            <div class="summary-amount">$15,238.75</div>
                            <div class="summary-period">This Week</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-header">
                                <h4>Monthly Revenue</h4>
                                <span class="trend-up">+15%</span>
                            </div>
                            <div class="summary-amount">$58,942.30</div>
                            <div class="summary-period">This Month</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function createNewBill() {
        alert('Opening new bill creation form...');
    }

    function viewTodayBills() {
        alert('Showing today\'s bills...');
    }

    function viewPendingBills() {
        alert('Showing pending bills...');
    }

    function collectPayments() {
        alert('Opening payment collection interface...');
    }

    function processPayments() {
        alert('Opening payment processing...');
    }

    function generateReport() {
        alert('Generating billing report...');
    }

    function viewOutstanding() {
        alert('Showing outstanding bills...');
    }

    function manageTax() {
        alert('Opening tax settings...');
    }

    function exportData() {
        alert('Exporting billing data...');
    }

    function viewBill(billId) {
        alert('Viewing bill ' + billId);
    }

    function printBill(billId) {
        alert('Printing bill ' + billId);
    }

    function markPaid(billId) {
        if (confirm('Mark this bill as paid?')) {
            alert('Marking bill ' + billId + ' as paid');
        }
    }

    function processCashPayment() {
        alert('Processing cash payment...');
    }

    function processCardPayment() {
        alert('Processing card payment...');
    }

    function processInsurance() {
        alert('Processing insurance claim...');
    }

    function processOnline() {
        alert('Processing online payment...');
    }

    function generateFinancialReport() {
        alert('Generating financial report...');
    }

    function filterBills() {
        const filter = document.getElementById('billFilter').value;
        alert('Filtering bills by: ' + filter);
    }

    // Bill calculation functions
    function addItem() {
        const itemsContainer = document.querySelector('.bill-items');
        const newItem = document.createElement('div');
        newItem.className = 'bill-item';
        newItem.innerHTML = `
            <input type="text" placeholder="Item description" class="item-desc">
            <input type="number" placeholder="Qty" class="item-qty" min="1" value="1" onchange="calculateTotal()">
            <input type="number" placeholder="Price" class="item-price" step="0.01" min="0" onchange="calculateTotal()">
            <span class="item-total">$0.00</span>
            <button type="button" class="btn-remove" onclick="removeItem(this)">❌</button>
        `;
        itemsContainer.appendChild(newItem);
    }

    function removeItem(button) {
        button.parentElement.remove();
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        const items = document.querySelectorAll('.bill-item');
        
        items.forEach(item => {
            const qty = parseFloat(item.querySelector('.item-qty').value) || 0;
            const price = parseFloat(item.querySelector('.item-price').value) || 0;
            const total = qty * price;
            subtotal += total;
            
            item.querySelector('.item-total').textContent = '$' + total.toFixed(2);
        });
        
        const tax = subtotal * 0.08;
        const total = subtotal + tax;
        
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('tax').textContent = '$' + tax.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
    }

    function saveBill() {
        alert('Saving bill...');
    }

    function clearForm() {
        document.querySelector('.quick-bill-form').reset();
        document.querySelector('.bill-items').innerHTML = '';
        calculateTotal();
    }

    // Initialize calculations
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotal();
    });
    </script>

    <style>
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 1rem;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .quick-action-btn:hover {
        background: #8e44ad;
        color: white;
        border-color: #8e44ad;
        transform: translateY(-3px);
    }

    .bill-creation {
        padding: 1rem 0;
    }

    .quick-bill-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-weight: 600;
        color: #2c3e50;
    }

    .form-group input {
        padding: 0.8rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
    }

    .bill-items {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .bill-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
        gap: 0.5rem;
        align-items: center;
        padding: 0.8rem;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .bill-item input {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .item-total {
        font-weight: 600;
        color: #2c3e50;
        text-align: center;
    }

    .btn-remove {
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0.3rem 0.6rem;
        cursor: pointer;
    }

    .btn-add-item {
        background: #27ae60;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        align-self: flex-start;
    }

    .bill-summary {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #8e44ad;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid #dee2e6;
    }

    .summary-row.total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .bills-table-container {
        overflow-x: auto;
    }

    .patient-info {
        display: flex;
        flex-direction: column;
    }

    .patient-id {
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .action-buttons {
        display: flex;
        gap: 0.3rem;
    }

    .payment-processing {
        margin-top: 1rem;
    }

    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .method-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #8e44ad;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .method-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .method-icon {
        font-size: 2.5rem;
    }

    .method-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .method-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .financial-summary {
        margin-top: 1rem;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .summary-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #8e44ad;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .summary-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .trend-up {
        color: #27ae60;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .summary-amount {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .summary-period {
        color: #7f8c8d;
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
        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .bill-item {
            grid-template-columns: 1fr;
            gap: 0.8rem;
        }

        .payment-methods {
            grid-template-columns: 1fr;
        }

        .summary-cards {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>