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

// Get EHA statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id AND DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$stats['today_inspections'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id AND status = 'open'";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$stats['open_cases'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id AND risk_level = 'high'";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$stats['high_risk_cases'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM eha_reports WHERE inspector_id = :inspector_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$stats['total_inspections'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent inspections
$query = "SELECT * FROM eha_reports WHERE inspector_id = :inspector_id ORDER BY inspection_date DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
$stmt->execute();
$recent_inspections = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EHA Dashboard - PHCHMS</title>
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
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header eha-content-header">
                <div class="header-left">
                    <h2>Environmental Health Dashboard</h2>
                    <p class="welcome-message">Community health monitoring and environmental safety management</p>
                </div>
                <div class="header-right">
                    <div class="eha-controls">
                        <button class="control-btn btn-primary" onclick="newInspection()">
                            <span class="btn-icon">🔍</span>
                            New Inspection
                        </button>
                        <button class="control-btn btn-success" onclick="scheduleVisit()">
                            <span class="btn-icon">🏠</span>
                            Schedule Visit
                        </button>
                        <div class="eha-status">
                            <span class="status-indicator status-healthy"></span>
                            <span class="status-text">All Areas Monitored</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EHA Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🔍
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['today_inspections']; ?></h3>
                        <p>Today's Inspections</p>
                        <span class="stat-trend trend-up">+3 scheduled</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewTodayInspections()">View</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['open_cases']; ?></h3>
                        <p>Open Cases</p>
                        <span class="stat-trend trend-up">Needs follow-up</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewOpenCases()">Review</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🚨
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['high_risk_cases']; ?></h3>
                        <p>High Risk Cases</p>
                        <span class="stat-trend trend-up">Immediate action</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewHighRisk()">Address</button>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📋
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_inspections']; ?></h3>
                        <p>Total Inspections</p>
                        <span class="stat-trend trend-up">+15% this month</span>
                    </div>
                    <div class="stat-actions">
                        <button class="stat-action-btn" onclick="viewAllInspections()">Browse</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Inspections -->
            <div class="content-grid">
                <!-- Quick Actions -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Quick EHA Actions</h3>
                        <span class="ai-badge">AI Assisted</span>
                    </div>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="newInspection()">
                            <span class="action-icon">🔍</span>
                            <span class="action-text">New Inspection</span>
                        </button>
                        <button class="quick-action-btn" onclick="scheduleVisit()">
                            <span class="action-icon">🏠</span>
                            <span class="action-text">Home Visit</span>
                        </button>
                        <button class="quick-action-btn" onclick="reportOutbreak()">
                            <span class="action-icon">🦠</span>
                            <span class="action-text">Report Outbreak</span>
                        </button>
                        <button class="quick-action-btn" onclick="vectorControl()">
                            <span class="action-icon">🦟</span>
                            <span class="action-text">Vector Control</span>
                        </button>
                        <button class="quick-action-btn" onclick="waterTesting()">
                            <span class="action-icon">💧</span>
                            <span class="action-text">Water Testing</span>
                        </button>
                        <button class="quick-action-btn" onclick="communityOutreach()">
                            <span class="action-icon">👥</span>
                            <span class="action-text">Community Outreach</span>
                        </button>
                    </div>
                </div>

                <!-- Recent Inspections -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Inspections</h3>
                        <a href="inspections.php" class="view-all">View All</a>
                    </div>
                    <div class="recent-inspections">
                        <?php if (count($recent_inspections) > 0): ?>
                            <?php foreach ($recent_inspections as $inspection): ?>
                            <div class="inspection-item">
                                <div class="inspection-type">
                                    <span class="type-icon">
                                        <?php 
                                        switch($inspection['report_type']) {
                                            case 'Sanitation': echo '🚰'; break;
                                            case 'Water Quality': echo '💧'; break;
                                            case 'Food Safety': echo '🍽️'; break;
                                            case 'Vector Control': echo '🦟'; break;
                                            default: echo '🔍';
                                        }
                                        ?>
                                    </span>
                                    <span class="type-name"><?php echo $inspection['report_type']; ?></span>
                                </div>
                                <div class="inspection-info">
                                    <h4><?php echo $inspection['location']; ?></h4>
                                    <p class="inspection-date">
                                        <?php echo date('M j, Y', strtotime($inspection['inspection_date'])); ?>
                                    </p>
                                    <p class="inspection-findings">
                                        <?php echo substr($inspection['findings'], 0, 100) . '...'; ?>
                                    </p>
                                </div>
                                <div class="inspection-status">
                                    <span class="risk-badge risk-<?php echo $inspection['risk_level']; ?>">
                                        <?php echo ucfirst($inspection['risk_level']); ?> Risk
                                    </span>
                                    <span class="status-badge status-<?php echo $inspection['status']; ?>">
                                        <?php echo ucfirst($inspection['status']); ?>
                                    </span>
                                </div>
                                <div class="inspection-actions">
                                    <button class="btn-action btn-primary" onclick="viewInspection(<?php echo $inspection['id']; ?>)">
                                        View
                                    </button>
                                    <button class="btn-action btn-outline" onclick="editInspection(<?php echo $inspection['id']; ?>)">
                                        Edit
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-inspections">
                                <p>No recent inspections found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Outbreak Alerts -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Outbreak & Health Alerts</h3>
                    <span class="alert-badge">Active Monitoring</span>
                </div>
                <div class="outbreak-alerts">
                    <div class="alert-item high-alert">
                        <div class="alert-icon">🦠</div>
                        <div class="alert-content">
                            <h4>Influenza Outbreak - Zone 4</h4>
                            <p>15 confirmed cases reported in the past 48 hours. Increased monitoring required.</p>
                            <div class="alert-meta">
                                <span class="alert-location">Location: Downtown Area</span>
                                <span class="alert-cases">Cases: 15 confirmed</span>
                                <span class="alert-severity">Severity: High</span>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-action btn-primary" onclick="respondToOutbreak(1)">Respond</button>
                            <button class="btn-action btn-outline" onclick="viewOutbreakDetails(1)">Details</button>
                        </div>
                    </div>

                    <div class="alert-item medium-alert">
                        <div class="alert-icon">🦟</div>
                        <div class="alert-content">
                            <h4>Mosquito Breeding Sites - Zone 2</h4>
                            <p>Multiple stagnant water sources identified. Increased risk of vector-borne diseases.</p>
                            <div class="alert-meta">
                                <span class="alert-location">Location: Residential Area</span>
                                <span class="alert-cases">Sites: 8 identified</span>
                                <span class="alert-severity">Severity: Medium</span>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-action btn-primary" onclick="initiateVectorControl()">Control</button>
                            <button class="btn-action btn-outline" onclick="viewVectorData()">Data</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Community Health Metrics -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Community Health Metrics</h3>
                    <span class="ai-badge">Real-time Data</span>
                </div>
                <div class="health-metrics">
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value">98.2%</div>
                            <div class="metric-label">Water Safety</div>
                            <div class="metric-trend trend-up">+0.5%</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">95.7%</div>
                            <div class="metric-label">Sanitation Coverage</div>
                            <div class="metric-trend trend-up">+2.1%</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">12</div>
                            <div class="metric-label">Active Outbreaks</div>
                            <div class="metric-trend trend-down">-3</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">89.3%</div>
                            <div class="metric-label">Vaccination Rate</div>
                            <div class="metric-trend trend-up">+5.2%</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">24</div>
                            <div class="metric-label">Vector Sites Treated</div>
                            <div class="metric-trend trend-up">+8</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">156</div>
                            <div class="metric-label">Health Education Sessions</div>
                            <div class="metric-trend trend-up">+25</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Environmental Insights -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Environmental Insights</h3>
                    <span class="ai-badge">Predictive Analytics</span>
                </div>
                <div class="environmental-insights">
                    <div class="insight-card">
                        <div class="insight-icon">🌡️</div>
                        <div class="insight-content">
                            <h4>Climate Impact Alert</h4>
                            <p>Rising temperatures may increase mosquito breeding rates by 35% in the coming weeks.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="enhanceVectorControl()">Enhance Control</button>
                                <button class="btn-action btn-outline" onclick="viewClimateData()">View Data</button>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Disease Pattern Detected</h4>
                            <p>Unusual pattern in gastrointestinal cases detected in Zone 3. Recommend water quality review.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="investigatePattern()">Investigate</button>
                                <button class="btn-action btn-outline" onclick="viewDiseaseData()">Data Analysis</button>
                            </div>
                        </div>
                    </div>

                    <div class="insight-card">
                        <div class="insight-icon">💡</div>
                        <div class="insight-content">
                            <h4>Preventive Opportunity</h4>
                            <p>Targeted sanitation education in high-risk areas could reduce disease incidence by 40%.</p>
                            <div class="insight-actions">
                                <button class="btn-action btn-primary" onclick="planEducationCampaign()">Plan Campaign</button>
                                <button class="btn-action btn-outline" onclick="viewRiskAreas()">Risk Areas</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showEHAAlerts() {
        alert('Showing environmental health alerts...');
    }

    function quickInspection() {
        const location = prompt('Enter inspection location:');
        if (location) {
            window.location.href = 'inspections.php?location=' + encodeURIComponent(location);
        }
    }

    function newInspection() {
        window.location.href = 'inspections.php?action=new';
    }

    function scheduleVisit() {
        window.location.href = 'visits.php?action=new';
    }

    function viewTodayInspections() {
        window.location.href = 'inspections.php?filter=today';
    }

    function viewOpenCases() {
        window.location.href = 'inspections.php?status=open';
    }

    function viewHighRisk() {
        window.location.href = 'inspections.php?risk=high';
    }

    function viewAllInspections() {
        window.location.href = 'inspections.php';
    }

    function reportOutbreak() {
        window.location.href = 'outbreak-tracking.php?action=new';
    }

    function vectorControl() {
        window.location.href = 'vector-control.php';
    }

    function waterTesting() {
        window.location.href = 'water-quality.php';
    }

    function communityOutreach() {
        window.location.href = 'community-outreach.php';
    }

    function viewInspection(inspectionId) {
        window.location.href = 'inspection-details.php?id=' + inspectionId;
    }

    function editInspection(inspectionId) {
        window.location.href = 'edit-inspection.php?id=' + inspectionId;
    }

    function respondToOutbreak(outbreakId) {
        alert('Initiating outbreak response for ' + outbreakId);
    }

    function viewOutbreakDetails(outbreakId) {
        window.location.href = 'outbreak-details.php?id=' + outbreakId;
    }

    function initiateVectorControl() {
        alert('Initiating vector control measures...');
    }

    function viewVectorData() {
        alert('Showing vector control data...');
    }

    function enhanceVectorControl() {
        alert('Enhancing vector control measures...');
    }

    function viewClimateData() {
        alert('Showing climate impact data...');
    }

    function investigatePattern() {
        alert('Investigating disease pattern...');
    }

    function viewDiseaseData() {
        alert('Showing disease incidence data...');
    }

    function planEducationCampaign() {
        window.location.href = 'health-education.php?action=plan';
    }

    function viewRiskAreas() {
        alert('Showing high-risk areas...');
    }
    </script>

    <style>
    .eha-header {
        background: linear-gradient(135deg, #221004ff, #ba4a00) !important;
    }

    .eha-sidebar {
        background: linear-gradient(180deg, #d35400 0%, #ba4a00 100%) !important;
    }

    .eha-sidebar .nav-link {
        color: #fdebd0 !important;
        border-left-color: transparent;
    }

    .eha-sidebar .nav-link:hover,
    .eha-sidebar .nav-link.active {
        background: rgba(211, 84, 0, 0.2);
        color: white !important;
        border-left-color: white;
    }

    .eha-sidebar .nav-section-title {
        color: #f0b27a !important;
    }

    .eha-content-header {
        background: linear-gradient(135deg, #d35400, #ba4a00) !important;
        color: white !important;
    }

    .eha-content-header h2,
    .eha-content-header .welcome-message {
        color: white !important;
    }

    .eha-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .eha-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.2rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: white;
    }

    .recent-inspections {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .inspection-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.2rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #d35400;
    }

    .inspection-type {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 80px;
    }

    .type-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .type-name {
        font-size: 0.8rem;
        color: #7f8c8d;
        text-align: center;
    }

    .inspection-info {
        flex: 1;
    }

    .inspection-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .inspection-date, .inspection-findings {
        margin: 0.2rem 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .inspection-status {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        min-width: 120px;
    }

    .inspection-actions {
        display: flex;
        gap: 0.5rem;
    }

    .outbreak-alerts {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .alert-item {
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid;
    }

    .alert-item.high-alert {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), transparent);
        border-left-color: #e74c3c;
    }

    .alert-item.medium-alert {
        background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), transparent);
        border-left-color: #f39c12;
    }

    .alert-icon {
        font-size: 2.5rem;
        flex-shrink: 0;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content h4 {
        margin: 0 0 0.8rem 0;
        color: #2c3e50;
    }

    .alert-content p {
        margin: 0 0 1rem 0;
        color: #7f8c8d;
        line-height: 1.5;
    }

    .alert-meta {
        display: flex;
        gap: 1.5rem;
    }

    .alert-location, .alert-cases, .alert-severity {
        font-size: 0.8rem;
        color: #7f8c8d;
        background: white;
        padding: 0.3rem 0.6rem;
        border-radius: 12px;
    }

    .alert-actions {
        display: flex;
        gap: 0.5rem;
    }

    .health-metrics {
        padding: 1rem 0;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .metric-card {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #d35400;
    }

    .metric-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: #d35400;
        margin-bottom: 0.5rem;
    }

    .metric-label {
        font-size: 0.9rem;
        color: #7f8c8d;
        margin-bottom: 0.5rem;
    }

    .metric-trend {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 12px;
    }

    .trend-up {
        background: rgba(46, 204, 113, 0.1);
        color: #27ae60;
    }

    .trend-down {
        background: rgba(231, 76, 60, 0.1);
        color: #c0392b;
    }

    .environmental-insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #d35400;
    }

    .insight-icon {
        font-size: 2.5rem;
        flex-shrink: 0;
    }

    .insight-content {
        flex: 1;
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

    .insight-actions {
        display: flex;
        gap: 0.5rem;
    }

    .no-inspections {
        text-align: center;
        padding: 2rem;
        color: #7f8c8d;
    }

    @media (max-width: 768px) {
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .environmental-insights {
            grid-template-columns: 1fr;
        }

        .inspection-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .inspection-actions {
            align-self: stretch;
            justify-content: stretch;
        }

        .inspection-actions .btn-action {
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

        .alert-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    </style>
</body>
</html>