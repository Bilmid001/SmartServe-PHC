<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('records') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle authorization actions
if ($_POST['action'] ?? '' === 'update_authorization') {
    $auth_id = $_POST['auth_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    
    $query = "UPDATE treatment_authorizations SET status = :status, notes = :notes, reviewed_by = :user_id, reviewed_at = NOW() WHERE id = :auth_id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':status' => $status,
        ':notes' => $notes,
        ':user_id' => $_SESSION['user_id'],
        ':auth_id' => $auth_id
    ])) {
        $success = "Authorization updated successfully!";
    } else {
        $error = "Error updating authorization!";
    }
}

// Get pending authorizations
$query = "SELECT ta.*, p.full_name as patient_name, p.patient_id, u.full_name as doctor_name, 
                 mr.diagnosis, mr.treatment
          FROM treatment_authorizations ta 
          JOIN patients p ON ta.patient_id = p.id 
          JOIN users u ON ta.doctor_id = u.id 
          JOIN medical_records mr ON ta.record_id = mr.id 
          WHERE ta.status = 'pending'
          ORDER BY ta.created_at DESC";
$stmt = $db->prepare($query);
// $stmt->execute();
$pending_auths = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get authorization statistics
$query = "SELECT status, COUNT(*) as count FROM treatment_authorizations GROUP BY status";
$stmt = $db->prepare($query);
// $stmt->execute();
$auth_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

foreach ($auth_stats as $stat) {
    $stats[$stat['status']] = $stat['count'];
}
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Authorization - PHCHMS</title>
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
                <h2>Treatment Authorization</h2>
                <div class="breadcrumb">
                    <span>Records</span> / <span>Authorization</span>
                </div>
            </div>

            <!-- Authorization Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏳
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Pending Authorization</p>
                        <span class="stat-trend trend-up">Needs review</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['approved']; ?></h3>
                        <p>Approved Today</p>
                        <span class="stat-trend trend-up">+5 this week</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ❌
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['rejected']; ?></h3>
                        <p>Rejected</p>
                        <span class="stat-trend trend-down">-2 from last week</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏱️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>2.1h</h3>
                        <p>Avg. Response Time</p>
                        <span class="stat-trend trend-down">-0.5h improvement</span>
                    </div>
                </div>
            </div>

            <!-- Pending Authorizations -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Pending Treatment Authorizations</h3>
                    <span class="ai-badge">AI Risk Assessment</span>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="authorizations-list">
                    <?php if (count($pending_auths) > 0): ?>
                        <?php foreach ($pending_auths as $auth): ?>
                        <div class="authorization-card">
                            <div class="auth-header">
                                <div class="patient-info">
                                    <h4><?php echo $auth['patient_name']; ?></h4>
                                    <p class="patient-id">ID: <?php echo $auth['patient_id']; ?></p>
                                    <p class="doctor-name">Requested by: Dr. <?php echo $auth['doctor_name']; ?></p>
                                </div>
                                <div class="auth-meta">
                                    <span class="auth-date"><?php echo date('M j, Y g:i A', strtotime($auth['created_at'])); ?></span>
                                    <span class="priority-badge priority-<?php echo $auth['priority']; ?>">
                                        <?php echo ucfirst($auth['priority']); ?> Priority
                                    </span>
                                </div>
                            </div>
                            
                            <div class="auth-details">
                                <div class="detail-section">
                                    <h5>Diagnosis</h5>
                                    <p><?php echo $auth['diagnosis']; ?></p>
                                </div>
                                <div class="detail-section">
                                    <h5>Proposed Treatment</h5>
                                    <p><?php echo $auth['treatment']; ?></p>
                                </div>
                                <div class="detail-section">
                                    <h5>Authorization Request</h5>
                                    <p><?php echo $auth['treatment_type']; ?> - <?php echo $auth['procedure_name']; ?></p>
                                </div>
                            </div>

                            <!-- AI Risk Assessment -->
                            <div class="ai-assessment">
                                <div class="assessment-header">
                                    <h5>AI Risk Assessment</h5>
                                    <span class="risk-level risk-medium">Medium Risk</span>
                                </div>
                                <div class="assessment-details">
                                    <div class="risk-factor">
                                        <span class="factor-name">Medical Necessity:</span>
                                        <span class="factor-value high">High</span>
                                    </div>
                                    <div class="risk-factor">
                                        <span class="factor-name">Cost Efficiency:</span>
                                        <span class="factor-value medium">Medium</span>
                                    </div>
                                    <div class="risk-factor">
                                        <span class="factor-name">Alternative Options:</span>
                                        <span class="factor-value low">Available</span>
                                    </div>
                                </div>
                            </div>

                            <div class="auth-actions">
                                <form method="POST" class="auth-form">
                                    <input type="hidden" name="action" value="update_authorization">
                                    <input type="hidden" name="auth_id" value="<?php echo $auth['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label for="notes_<?php echo $auth['id']; ?>">Review Notes</label>
                                        <textarea id="notes_<?php echo $auth['id']; ?>" name="notes" rows="3" placeholder="Enter review notes..."></textarea>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button type="submit" name="status" value="approved" class="btn-action btn-success">
                                            ✅ Approve
                                        </button>
                                        <button type="submit" name="status" value="rejected" class="btn-action btn-danger">
                                            ❌ Reject
                                        </button>
                                        <button type="button" class="btn-action btn-outline" onclick="requestMoreInfo(<?php echo $auth['id']; ?>)">
                                            ℹ️ More Info
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-authorizations">
                            <div class="no-auth-icon">✅</div>
                            <h4>No Pending Authorizations</h4>
                            <p>All treatment authorizations have been reviewed and processed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Authorization Analytics -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Authorization Analytics</h3>
                    <span class="ai-badge">Trend Analysis</span>
                </div>
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <h4>Approval Rate</h4>
                        <div class="metric-value">85%</div>
                        <div class="metric-trend trend-up">+5% from last month</div>
                    </div>
                    
                    <div class="analytics-card">
                        <h4>Avg. Processing Time</h4>
                        <div class="metric-value">2.1h</div>
                        <div class="metric-trend trend-down">-0.3h improvement</div>
                    </div>
                    
                    <div class="analytics-card">
                        <h4>High Priority Cases</h4>
                        <div class="metric-value">12</div>
                        <div class="metric-trend trend-up">Active</div>
                    </div>
                    
                    <div class="analytics-card">
                        <h4>Cost Savings</h4>
                        <div class="metric-value">$24.5K</div>
                        <div class="metric-trend trend-up">This quarter</div>
                    </div>
                </div>
            </div>

            <!-- Quick Authorization Tools -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Authorization Tools</h3>
                </div>
                <div class="tools-grid">
                    <button class="tool-btn" onclick="bulkApprove()">
                        <span class="tool-icon">✅</span>
                        <span class="tool-text">Bulk Approve</span>
                        <span class="tool-desc">Approve multiple low-risk cases</span>
                    </button>
                    
                    <button class="tool-btn" onclick="generateReport()">
                        <span class="tool-icon">📊</span>
                        <span class="tool-text">Generate Report</span>
                        <span class="tool-desc">Authorization analytics</span>
                    </button>
                    
                    <button class="tool-btn" onclick="setAutoRules()">
                        <span class="tool-icon">⚙️</span>
                        <span class="tool-text">Auto-approval Rules</span>
                        <span class="tool-desc">Configure automation</span>
                    </button>
                    
                    <button class="tool-btn" onclick="viewGuidelines()">
                        <span class="tool-icon">📚</span>
                        <span class="tool-text">Guidelines</span>
                        <span class="tool-desc">Authorization policies</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function requestMoreInfo(authId) {
        const notes = prompt('What additional information do you need?');
        if (notes) {
            // In real implementation, this would send a request for more information
            alert('Information request sent for authorization ' + authId);
        }
    }

    function bulkApprove() {
        if (confirm('Approve all low-risk pending authorizations?')) {
            alert('Bulk approval process started...');
        }
    }

    function generateReport() {
        alert('Generating authorization report...');
    }

    function setAutoRules() {
        alert('Opening auto-approval rules configuration...');
    }

    function viewGuidelines() {
        alert('Opening authorization guidelines...');
    }

    // AI risk assessment simulation
    function assessRisk(authId) {
        // This would be connected to an AI service in real implementation
        console.log('Assessing risk for authorization:', authId);
    }
    </script>

    <style>
    .authorizations-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .authorization-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .auth-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .patient-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .patient-id, .doctor-name {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin: 0.2rem 0;
    }

    .auth-meta {
        text-align: right;
    }

    .auth-date {
        display: block;
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .priority-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .priority-low {
        background: #d4edda;
        color: #155724;
    }

    .priority-medium {
        background: #fff3cd;
        color: #856404;
    }

    .priority-high {
        background: #f8d7da;
        color: #721c24;
    }

    .auth-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .detail-section h5 {
        color: #2980b9;
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
    }

    .detail-section p {
        margin: 0;
        color: #495057;
        line-height: 1.5;
    }

    .ai-assessment {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border-left: 4px solid #f39c12;
    }

    .assessment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .assessment-header h5 {
        margin: 0;
        color: #2c3e50;
    }

    .risk-level {
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

    .assessment-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .risk-factor {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem;
        background: white;
        border-radius: 6px;
    }

    .factor-value {
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 10px;
        font-size: 0.8rem;
    }

    .factor-value.high {
        background: #d4edda;
        color: #155724;
    }

    .factor-value.medium {
        background: #fff3cd;
        color: #856404;
    }

    .factor-value.low {
        background: #e2e3e5;
        color: #383d41;
    }

    .auth-actions {
        border-top: 1px solid #e9ecef;
        padding-top: 1.5rem;
    }

    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .analytics-card {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .analytics-card h4 {
        margin: 0 0 1rem 0;
        color: #2c3e50;
        font-size: 0.9rem;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2980b9;
        margin-bottom: 0.5rem;
    }

    .tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .tool-btn {
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

    .tool-btn:hover {
        background: #2980b9;
        color: white;
        border-color: #2980b9;
        transform: translateY(-2px);
    }

    .tool-icon {
        font-size: 2rem;
        margin-bottom: 0.8rem;
    }

    .tool-text {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .tool-desc {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .no-authorizations {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
    }

    .no-auth-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .auth-header {
            flex-direction: column;
            gap: 1rem;
        }

        .auth-meta {
            text-align: left;
        }

        .auth-details {
            grid-template-columns: 1fr;
        }

        .assessment-details {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .tools-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>