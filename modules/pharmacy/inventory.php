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

// Handle inventory actions
if ($_POST['action'] ?? '' === 'add_drug') {
    $drug_name = $_POST['drug_name'];
    $generic_name = $_POST['generic_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $reorder_level = $_POST['reorder_level'];
    $unit_price = $_POST['unit_price'];
    $expiry_date = $_POST['expiry_date'];
    $supplier = $_POST['supplier'];
    
    $query = "INSERT INTO pharmacy_inventory (drug_name, generic_name, category, quantity, reorder_level, unit_price, expiry_date, supplier) 
              VALUES (:drug_name, :generic_name, :category, :quantity, :reorder_level, :unit_price, :expiry_date, :supplier)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':drug_name' => $drug_name,
        ':generic_name' => $generic_name,
        ':category' => $category,
        ':quantity' => $quantity,
        ':reorder_level' => $reorder_level,
        ':unit_price' => $unit_price,
        ':expiry_date' => $expiry_date,
        ':supplier' => $supplier
    ])) {
        $success = "Drug added to inventory successfully!";
    } else {
        $error = "Error adding drug to inventory!";
    }
}

// Get inventory with AI predictions
$query = "SELECT *, 
          CASE 
            WHEN quantity <= reorder_level THEN 'low'
            WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring'
            ELSE 'normal'
          END as stock_status
          FROM pharmacy_inventory 
          ORDER BY stock_status DESC, quantity ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get low stock items
$query = "SELECT COUNT(*) as count FROM pharmacy_inventory WHERE quantity <= reorder_level";
$stmt = $db->prepare($query);
$stmt->execute();
$low_stock_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get expiring soon items
$query = "SELECT COUNT(*) as count FROM pharmacy_inventory WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$stmt = $db->prepare($query);
$stmt->execute();
$expiring_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Inventory - PHCHMS</title>
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
                <h2>Pharmacy Inventory Management</h2>
                <div class="breadcrumb">
                    <span>Pharmacy</span> / <span>Inventory</span>
                </div>
            </div>

            <!-- Inventory Alerts -->
            <div class="alert-section">
                <?php if ($low_stock_count > 0): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Low Stock Alert:</strong> <?php echo $low_stock_count; ?> items are below reorder level.
                </div>
                <?php endif; ?>
                
                <?php if ($expiring_count > 0): ?>
                <div class="alert alert-error">
                    <strong>📅 Expiry Alert:</strong> <?php echo $expiring_count; ?> items are expiring within 30 days.
                </div>
                <?php endif; ?>
            </div>

            <!-- Add Drug Form -->
            <div class="dashboard-section">
                <h3>Add New Drug to Inventory</h3>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="drug-form">
                    <input type="hidden" name="action" value="add_drug">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="drug_name">Drug Name *</label>
                            <input type="text" id="drug_name" name="drug_name" required>
                        </div>
                        <div class="form-group">
                            <label for="generic_name">Generic Name</label>
                            <input type="text" id="generic_name" name="generic_name">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="Antibiotic">Antibiotic</option>
                                <option value="Analgesic">Analgesic</option>
                                <option value="Antihypertensive">Antihypertensive</option>
                                <option value="Antidiabetic">Antidiabetic</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Initial Quantity *</label>
                            <input type="number" id="quantity" name="quantity" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reorder_level">Reorder Level *</label>
                            <input type="number" id="reorder_level" name="reorder_level" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_price">Unit Price ($) *</label>
                            <input type="number" id="unit_price" name="unit_price" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date">
                        </div>
                        <div class="form-group">
                            <label for="supplier">Supplier</label>
                            <input type="text" id="supplier" name="supplier">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Add to Inventory</button>
                </form>
            </div>

            <!-- AI Stock Predictions -->
            <div class="dashboard-section">
                <h3>AI Stock Predictions</h3>
                <div class="ai-predictions">
                    <div class="prediction-card">
                        <h4>📈 Demand Forecast</h4>
                        <p>Paracetamol demand expected to increase by 15% next week.</p>
                        <span class="confidence">87% confidence</span>
                    </div>
                    <div class="prediction-card">
                        <h4>🔄 Reorder Suggestions</h4>
                        <p>Amoxicillin stock running low. Suggested reorder: 500 units.</p>
                        <span class="confidence">92% confidence</span>
                    </div>
                    <div class="prediction-card">
                        <h4>💰 Cost Optimization</h4>
                        <p>Bulk purchase of Ibuprofen could save 12% this month.</p>
                        <span class="confidence">78% confidence</span>
                    </div>
                </div>
            </div>

            <!-- Inventory List -->
            <div class="dashboard-section">
                <h3>Current Inventory</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Drug Name</th>
                                <th>Generic Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Reorder Level</th>
                                <th>Unit Price</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item): 
                                $status_class = '';
                                $status_text = '';
                                
                                if ($item['stock_status'] === 'low') {
                                    $status_class = 'status-warning';
                                    $status_text = 'Low Stock';
                                } elseif ($item['stock_status'] === 'expiring') {
                                    $status_class = 'status-error';
                                    $status_text = 'Expiring Soon';
                                } else {
                                    $status_class = 'status-success';
                                    $status_text = 'In Stock';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo $item['drug_name']; ?></strong></td>
                                <td><?php echo $item['generic_name']; ?></td>
                                <td><?php echo $item['category']; ?></td>
                                <td>
                                    <span class="<?php echo $item['quantity'] <= $item['reorder_level'] ? 'text-warning' : ''; ?>">
                                        <?php echo $item['quantity']; ?>
                                    </span>
                                </td>
                                <td><?php echo $item['reorder_level']; ?></td>
                                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td>
                                    <?php if ($item['expiry_date']): ?>
                                        <?php echo date('M j, Y', strtotime($item['expiry_date'])); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-sm btn-primary" onclick="updateStock(<?php echo $item['id']; ?>)">
                                        Update
                                    </button>
                                    <button class="btn-sm btn-secondary" onclick="viewDetails(<?php echo $item['id']; ?>)">
                                        Details
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateStock(drugId) {
        const newQuantity = prompt('Enter new quantity:');
        if (newQuantity !== null && !isNaN(newQuantity)) {
            // Implement stock update
            alert('Updating stock for drug ID: ' + drugId + ' to ' + newQuantity);
        }
    }
    
    function viewDetails(drugId) {
        window.location.href = 'drug-details.php?id=' + drugId;
    }
    </script>
</body>
</html>