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

// Handle new inspection
if ($_POST['action'] ?? '' === 'create_inspection') {
    $report_type = $_POST['report_type'];
    $location = $_POST['location'];
    $inspection_date = $_POST['inspection_date'];
    $findings = $_POST['findings'];
    $recommendations = $_POST['recommendations'];
    $risk_level = $_POST['risk_level'];
    $status = $_POST['status'];
    
    $query = "INSERT INTO eha_reports (report_type, location, inspector_id, inspection_date, findings, recommendations, risk_level, status) 
              VALUES (:report_type, :location, :inspector_id, :inspection_date, :findings, :recommendations, :risk_level, :status)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':report_type' => $report_type,
        ':location' => $location,
        ':inspector_id' => $inspector_id,
        ':inspection_date' => $inspection_date,
        ':findings' => $findings,
        ':recommendations' => $recommendations,
        ':risk_level' => $risk_level,
        ':status' => $status
    ])) {
        $success = "Inspection report created successfully!";
    } else {
        $error = "Error creating inspection report!";
    }
}

// Get inspections
$query = "SELECT * FROM eha_reports WHERE inspector_id = :inspector_id ORDER BY inspection_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$inspections = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environmental Inspections - PHCHMS</title>
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
</style>

</head>
<body>
   
        
        <div class="main-content">
            <div class="content-header">
                <h2>Environmental Health Inspections</h2>
                <div class="breadcrumb">
                    <span>EHA</span> / <span>Inspections</span>
                </div>
            </div>

            <!-- Inspection Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>24</h3>
                        <p>Completed Inspections</p>
                        <span class="stat-trend trend-up">+5 this month</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>8</h3>
                        <p>High Risk Areas</p>
                        <span class="stat-trend trend-up">Needs attention</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🏠
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>15</h3>
                        <p>Home Visits</p>
                        <span class="stat-trend trend-neutral">Scheduled</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🌡️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>92%</h3>
                        <p>Compliance Rate</p>
                        <span class="stat-trend trend-up">+3% from last month</span>
                    </div>
                </div>
            </div>

            <!-- New Inspection Form -->
            <div class="content-card">
                <div class="card-header">
                    <h3>New Inspection Report</h3>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="inspection-form">
                    <input type="hidden" name="action" value="create_inspection">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" required>
                                <option value="Sanitation">Sanitation Inspection</option>
                                <option value="Water Quality">Water Quality</option>
                                <option value="Food Safety">Food Safety</option>
                                <option value="Waste Management">Waste Management</option>
                                <option value="Vector Control">Vector Control</option>
                                <option value="Air Quality">Air Quality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" required placeholder="Enter location address">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="inspection_date">Inspection Date</label>
                            <input type="date" id="inspection_date" name="inspection_date" required>
                        </div>
                        <div class="form-group">
                            <label for="risk_level">Risk Level</label>
                            <select id="risk_level" name="risk_level" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="findings">Findings</label>
                        <textarea id="findings" name="findings" rows="4" required placeholder="Describe inspection findings..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="3" placeholder="Enter recommendations..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="open">Open</option>
                            <option value="in-progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Create Inspection Report</button>
                </form>
            </div>

            <!-- AI Outbreak Detection -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Outbreak Detection</h3>
                    <span class="ai-badge">Real-time Monitoring</span>
                </div>
                <div class="ai-alerts">
                    <div class="alert-item alert-low">
                        <div class="alert-icon">🔍</div>
                        <div class="alert-content">
                            <h4>Normal Activity</h4>
                            <p>No unusual disease patterns detected in your area.</p>
                        </div>
                    </div>
                    <div class="alert-item alert-medium">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <h4>Seasonal Alert</h4>
                            <p>Increased mosquito activity detected. Consider vector control measures.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Inspections -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Inspections</h3>
                    <a href="#" class="view-all">View All</a>
                </div>
                <div class="inspections-list">
                    <?php foreach ($inspections as $inspection): ?>
                    <div class="inspection-item">
                        <div class="inspection-header">
                            <h4><?php echo $inspection['report_type']; ?></h4>
                            <span class="risk-badge risk-<?php echo $inspection['risk_level']; ?>">
                                <?php echo ucfirst($inspection['risk_level']); ?> Risk
                            </span>
                        </div>
                        <div class="inspection-details">
                            <p><strong>Location:</strong> <?php echo $inspection['location']; ?></p>
                            <p><strong>Date:</strong> <?php echo date('M j, Y', strtotime($inspection['inspection_date'])); ?></p>
                            <p><strong>Status:</strong> <span class="status-badge status-<?php echo $inspection['status']; ?>"><?php echo ucfirst($inspection['status']); ?></span></p>
                        </div>
                        <div class="inspection-actions">
                            <button class="btn-sm btn-primary" onclick="viewInspection(<?php echo $inspection['id']; ?>)">View</button>
                            <button class="btn-sm btn-secondary" onclick="editInspection(<?php echo $inspection['id']; ?>)">Edit</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewInspection(inspectionId) {
        window.location.href = 'inspection-details.php?id=' + inspectionId;
    }
    
    function editInspection(inspectionId) {
        window.location.href = 'edit-inspection.php?id=' + inspectionId;
    }
    </script>
</body>
</html>