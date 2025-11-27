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

// Handle new visit creation
if ($_POST['action'] ?? '' === 'create_visit') {
    $visit_type = $_POST['visit_type'];
    $location = $_POST['location'];
    $visit_date = $_POST['visit_date'];
    $purpose = $_POST['purpose'];
    $priority = $_POST['priority'];
    
    $query = "INSERT INTO eha_visits (inspector_id, visit_type, location, visit_date, purpose, priority, status) 
              VALUES (:inspector_id, :visit_type, :location, :visit_date, :purpose, :priority, 'scheduled')";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':inspector_id' => $inspector_id,
        ':visit_type' => $visit_type,
        ':location' => $location,
        ':visit_date' => $visit_date,
        ':purpose' => $purpose,
        ':priority' => $priority
    ])) {
        $success = "Home visit scheduled successfully!";
    } else {
        $error = "Error scheduling visit!";
    }
}

// Get scheduled visits
$query = "SELECT * FROM eha_visits 
          WHERE inspector_id = :inspector_id 
          AND visit_date >= CURDATE() 
          ORDER BY visit_date ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
// $stmt->execute();
$scheduled_visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get completed visits
$query = "SELECT COUNT(*) as total FROM eha_visits 
          WHERE inspector_id = :inspector_id 
          AND status = 'completed' 
          AND DATE(visit_date) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(':inspector_id', $inspector_id);
// $stmt->execute();
// $completed_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EHA Visits - PHCHMS</title>
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
                <h2>Environmental Health Visits</h2>
                <div class="breadcrumb">
                    <span>EHA</span> / <span>Home Visits</span>
                </div>
            </div>

            <!-- Visit Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            🏠
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($scheduled_visits); ?></h3>
                        <p>Scheduled Visits</p>
                        <span class="stat-trend trend-up">+3 this week</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $completed_today; ?></h3>
                        <p>Completed Today</p>
                        <span class="stat-trend trend-up">On track</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⚠️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>5</h3>
                        <p>High Priority</p>
                        <span class="stat-trend trend-up">Urgent</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📍
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>12</h3>
                        <p>Active Locations</p>
                        <span class="stat-trend trend-neutral">Monitoring</span>
                    </div>
                </div>
            </div>

            <!-- Schedule New Visit -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Schedule New Home Visit</h3>
                    <span class="ai-badge">Route Optimized</span>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="visit-form">
                    <input type="hidden" name="action" value="create_visit">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="visit_type">Visit Type</label>
                            <select id="visit_type" name="visit_type" required>
                                <option value="">Select Visit Type</option>
                                <option value="sanitation">Sanitation Inspection</option>
                                <option value="water_safety">Water Safety Check</option>
                                <option value="vector_control">Vector Control</option>
                                <option value="food_safety">Food Safety Audit</option>
                                <option value="waste_management">Waste Management</option>
                                <option value="health_education">Health Education</option>
                                <option value="follow_up">Follow-up Visit</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority Level</label>
                            <select id="priority" name="priority" required>
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="location">Location Address</label>
                            <input type="text" id="location" name="location" required 
                                   placeholder="Enter full address or location coordinates">
                        </div>
                        <div class="form-group">
                            <label for="visit_date">Scheduled Date & Time</label>
                            <input type="datetime-local" id="visit_date" name="visit_date" required 
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="purpose">Visit Purpose & Objectives</label>
                        <textarea id="purpose" name="purpose" rows="4" required 
                                  placeholder="Describe the purpose of this visit, specific objectives, and areas to inspect..."></textarea>
                    </div>

                    <!-- AI Route Optimization -->
                    <div class="ai-optimization-section">
                        <div class="optimization-header">
                            <h4>AI Route Optimization</h4>
                            <button type="button" class="btn-secondary" onclick="optimizeRoute()">
                                <span class="btn-icon">🗺️</span>
                                Optimize Route
                            </button>
                        </div>
                        <div id="routeResults" class="route-results" style="display: none;">
                            <div class="route-card">
                                <h5>Optimized Visit Route</h5>
                                <div class="route-details">
                                    <div class="route-item">
                                        <span class="route-icon">📍</span>
                                        <div class="route-text">
                                            <strong>Optimal Route Found</strong>
                                            <p>Estimated travel time: 25 minutes</p>
                                        </div>
                                    </div>
                                    <div class="route-item">
                                        <span class="route-icon">⏱️</span>
                                        <div class="route-text">
                                            <strong>Time Savings</strong>
                                            <p>15 minutes saved compared to direct route</p>
                                        </div>
                                    </div>
                                    <div class="route-item">
                                        <span class="route-icon">🚗</span>
                                        <div class="route-text">
                                            <strong>Distance</strong>
                                            <p>8.5 km total distance</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-icon">📅</span>
                            Schedule Visit
                        </button>
                        <button type="button" class="btn-secondary" onclick="saveDraft()">
                            <span class="btn-icon">💾</span>
                            Save Draft
                        </button>
                        <button type="button" class="btn-outline" onclick="clearForm()">
                            <span class="btn-icon">🗑️</span>
                            Clear Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Today's Schedule -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Today's Visit Schedule</h3>
                    <a href="visit-calendar.php" class="view-all">View Calendar</a>
                </div>
                <div class="visits-timeline">
                    <?php
                    $today_visits = array_filter($scheduled_visits, function($visit) {
                        return date('Y-m-d', strtotime($visit['visit_date'])) === date('Y-m-d');
                    });
                    ?>
                    
                    <?php if (count($today_visits) > 0): ?>
                        <?php foreach ($today_visits as $visit): ?>
                        <div class="visit-slot">
                            <div class="visit-time">
                                <strong><?php echo date('h:i A', strtotime($visit['visit_date'])); ?></strong>
                            </div>
                            <div class="visit-info">
                                <h4><?php echo ucfirst(str_replace('_', ' ', $visit['visit_type'])); ?> Visit</h4>
                                <p class="visit-location">📍 <?php echo $visit['location']; ?></p>
                                <p class="visit-purpose"><?php echo $visit['purpose']; ?></p>
                                <div class="visit-meta">
                                    <span class="priority-badge priority-<?php echo $visit['priority']; ?>">
                                        <?php echo ucfirst($visit['priority']); ?> Priority
                                    </span>
                                    <span class="status-badge status-<?php echo $visit['status']; ?>">
                                        <?php echo ucfirst($visit['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="visit-actions">
                                <button class="btn-action btn-primary" onclick="startVisit(<?php echo $visit['id']; ?>)">
                                    Start Visit
                                </button>
                                <button class="btn-action btn-outline" onclick="rescheduleVisit(<?php echo $visit['id']; ?>)">
                                    Reschedule
                                </button>
                                <button class="btn-action btn-danger" onclick="cancelVisit(<?php echo $visit['id']; ?>)">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-visits">
                            <div class="no-visits-icon">🏠</div>
                            <h4>No Visits Scheduled for Today</h4>
                            <p>Schedule new home visits using the form above.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Visits -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Upcoming Visits</h3>
                    <a href="visit-schedule.php" class="view-all">View All</a>
                </div>
                <div class="upcoming-visits">
                    <?php
                    $upcoming_visits = array_filter($scheduled_visits, function($visit) {
                        return date('Y-m-d', strtotime($visit['visit_date'])) > date('Y-m-d');
                    });
                    ?>
                    
                    <?php if (count($upcoming_visits) > 0): ?>
                        <div class="visits-grid">
                            <?php foreach (array_slice($upcoming_visits, 0, 6) as $visit): ?>
                            <div class="visit-card">
                                <div class="visit-date">
                                    <strong><?php echo date('M j', strtotime($visit['visit_date'])); ?></strong>
                                    <span><?php echo date('D', strtotime($visit['visit_date'])); ?></span>
                                </div>
                                <div class="visit-details">
                                    <h4><?php echo ucfirst(str_replace('_', ' ', $visit['visit_type'])); ?></h4>
                                    <p class="visit-time"><?php echo date('g:i A', strtotime($visit['visit_date'])); ?></p>
                                    <p class="visit-location"><?php echo $visit['location']; ?></p>
                                    <div class="visit-meta">
                                        <span class="priority-badge priority-<?php echo $visit['priority']; ?>">
                                            <?php echo ucfirst($visit['priority']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="visit-actions">
                                    <button class="btn-action btn-primary" onclick="viewVisit(<?php echo $visit['id']; ?>)">
                                        Details
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-upcoming-visits">
                            <p>No upcoming visits scheduled.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Visit Templates -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Visit Templates</h3>
                    <span class="ai-badge">Common Scenarios</span>
                </div>
                <div class="templates-grid">
                    <div class="template-card">
                        <div class="template-icon">🚰</div>
                        <div class="template-content">
                            <h4>Water Safety Check</h4>
                            <p>Standard water quality and safety inspection</p>
                            <div class="template-details">
                                <span class="detail-item">⏱️ 45 min</span>
                                <span class="detail-item">📍 Residential</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('water_safety')">Use Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-icon">🏠</div>
                        <div class="template-content">
                            <h4>Sanitation Audit</h4>
                            <p>Comprehensive sanitation and hygiene inspection</p>
                            <div class="template-details">
                                <span class="detail-item">⏱️ 60 min</span>
                                <span class="detail-item">📍 Household</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('sanitation')">Use Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-icon">🦟</div>
                        <div class="template-content">
                            <h4>Vector Control</h4>
                            <p>Mosquito and pest control assessment</p>
                            <div class="template-details">
                                <span class="detail-item">⏱️ 30 min</span>
                                <span class="detail-item">📍 Outdoor</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('vector_control')">Use Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-icon">📚</div>
                        <div class="template-content">
                            <h4>Health Education</h4>
                            <p>Community health awareness and education</p>
                            <div class="template-details">
                                <span class="detail-item">⏱️ 90 min</span>
                                <span class="detail-item">📍 Community</span>
                            </div>
                        </div>
                        <button class="btn-template" onclick="useTemplate('health_education')">Use Template</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function optimizeRoute() {
        const location = document.getElementById('location').value;
        if (!location.trim()) {
            alert('Please enter a location first.');
            return;
        }

        // Show loading state
        const routeDiv = document.getElementById('routeResults');
        routeDiv.innerHTML = `
            <div class="route-card loading">
                <h5>Optimizing Route...</h5>
                <p>Calculating optimal travel path</p>
            </div>
        `;
        routeDiv.style.display = 'block';

        // Simulate route optimization
        setTimeout(() => {
            routeDiv.innerHTML = `
                <div class="route-card">
                    <h5>Optimized Visit Route</h5>
                    <div class="route-details">
                        <div class="route-item">
                            <span class="route-icon">📍</span>
                            <div class="route-text">
                                <strong>Optimal Route Found</strong>
                                <p>Estimated travel time: 25 minutes</p>
                            </div>
                        </div>
                        <div class="route-item">
                            <span class="route-icon">⏱️</span>
                            <div class="route-text">
                                <strong>Time Savings</strong>
                                <p>15 minutes saved compared to direct route</p>
                            </div>
                        </div>
                        <div class="route-item">
                            <span class="route-icon">🚗</span>
                            <div class="route-text">
                                <strong>Distance</strong>
                                <p>8.5 km total distance</p>
                            </div>
                        </div>
                        <div class="route-item">
                            <span class="route-icon">🛣️</span>
                            <div class="route-text">
                                <strong>Route Details</strong>
                                <p>Includes 3 other nearby inspections</p>
                            </div>
                        </div>
                    </div>
                    <div class="route-actions">
                        <button class="btn-action btn-primary" onclick="viewRouteMap()">View Map</button>
                        <button class="btn-action btn-outline" onclick="downloadRoute()">Download Directions</button>
                    </div>
                </div>
            `;
        }, 2000);
    }

    function saveDraft() {
        alert('Visit draft saved successfully!');
    }

    function clearForm() {
        if (confirm('Clear all form fields?')) {
            document.querySelector('.visit-form').reset();
            document.getElementById('routeResults').style.display = 'none';
        }
    }

    function startVisit(visitId) {
        if (confirm('Start this home visit now?')) {
            window.location.href = 'visit-conduct.php?id=' + visitId;
        }
    }

    function rescheduleVisit(visitId) {
        alert('Rescheduling visit ' + visitId);
    }

    function cancelVisit(visitId) {
        if (confirm('Cancel this visit? This action cannot be undone.')) {
            alert('Visit cancelled: ' + visitId);
        }
    }

    function viewVisit(visitId) {
        window.location.href = 'visit-details.php?id=' + visitId;
    }

    function useTemplate(templateType) {
        let purpose = '';
        let visitType = templateType;
        
        switch(templateType) {
            case 'water_safety':
                purpose = "Inspect water sources for safety and quality. Check for proper storage, treatment, and potential contamination risks. Test water samples if necessary.";
                break;
            case 'sanitation':
                purpose = "Comprehensive sanitation inspection. Assess waste disposal, toilet facilities, handwashing stations, and overall hygiene practices.";
                break;
            case 'vector_control':
                purpose = "Vector control assessment. Identify breeding sites, check for pest infestations, and recommend control measures.";
                break;
            case 'health_education':
                purpose = "Health education session. Provide information on hygiene practices, disease prevention, and healthy living habits.";
                break;
        }
        
        document.getElementById('visit_type').value = visitType;
        document.getElementById('purpose').value = purpose;
        document.getElementById('priority').value = 'medium';
        
        // Set default time to next available slot
        const now = new Date();
        now.setHours(now.getHours() + 1);
        now.setMinutes(0);
        document.getElementById('visit_date').value = now.toISOString().slice(0, 16);
        
        alert('Template applied! Please review and adjust details as needed.');
    }

    function viewRouteMap() {
        alert('Opening route map...');
    }

    function downloadRoute() {
        alert('Downloading route directions...');
    }

    // Auto-optimize route when location changes
    document.getElementById('location').addEventListener('change', function() {
        if (this.value.trim()) {
            optimizeRoute();
        }
    });
    </script>

    <style>
    .visit-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .ai-optimization-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #e67e22;
    }

    .optimization-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .optimization-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .route-results {
        margin-top: 1rem;
    }

    .route-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .route-card h5 {
        margin: 0 0 1rem 0;
        color: #2c3e50;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    .route-details {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .route-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .route-icon {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .route-text strong {
        display: block;
        color: #2c3e50;
        margin-bottom: 0.3rem;
    }

    .route-text p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .route-actions {
        display: flex;
        gap: 0.5rem;
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }

    .visits-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .visit-slot {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #e67e22;
        transition: all 0.3s ease;
    }

    .visit-slot:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .visit-time {
        min-width: 100px;
        text-align: center;
    }

    .visit-time strong {
        color: #e67e22;
        font-size: 1.1rem;
    }

    .visit-info {
        flex: 1;
    }

    .visit-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .visit-location {
        color: #e67e22;
        font-weight: 600;
        margin: 0.2rem 0;
    }

    .visit-purpose {
        color: #495057;
        margin: 0.5rem 0 0 0;
        font-style: italic;
    }

    .visit-meta {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
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

    .priority-urgent {
        background: #dc3545;
        color: white;
    }

    .visit-actions {
        display: flex;
        gap: 0.5rem;
    }

    .upcoming-visits {
        margin-top: 1rem;
    }

    .visits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .visit-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid #e67e22;
        transition: all 0.3s ease;
    }

    .visit-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .visit-date {
        text-align: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .visit-date strong {
        display: block;
        color: #e67e22;
        font-size: 1.2rem;
        margin-bottom: 0.3rem;
    }

    .visit-date span {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .visit-details h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .visit-time {
        color: #e67e22;
        font-weight: 600;
        margin: 0.2rem 0;
    }

    .visit-location {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0.2rem 0 1rem 0;
    }

    .templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .template-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
        text-align: center;
        transition: all 0.3s ease;
    }

    .template-card:hover {
        border-color: #e67e22;
        background: white;
        transform: translateY(-2px);
    }

    .template-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .template-content h4 {
        margin: 0 0 0.8rem 0;
        color: #2c3e50;
    }

    .template-content p {
        margin: 0 0 1rem 0;
        color: #6c757d;
        line-height: 1.5;
    }

    .template-details {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .detail-item {
        font-size: 0.8rem;
        color: #6c757d;
        background: white;
        padding: 0.3rem 0.6rem;
        border-radius: 12px;
    }

    .no-visits, .no-upcoming-visits {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .no-visits-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .visits-grid {
            grid-template-columns: 1fr;
        }

        .templates-grid {
            grid-template-columns: 1fr;
        }

        .visit-slot {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .visit-actions {
            align-self: stretch;
            justify-content: stretch;
        }

        .visit-actions .btn-action {
            flex: 1;
        }

        .optimization-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .route-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>