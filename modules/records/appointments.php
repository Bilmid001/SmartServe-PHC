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

// Handle appointment actions
if ($_POST['action'] ?? '' === 'create_appointment') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];
    $notes = $_POST['notes'];
    
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    
    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason, notes, status) 
              VALUES (:patient_id, :doctor_id, :appointment_date, :reason, :notes, 'scheduled')";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':patient_id' => $patient_id,
        ':doctor_id' => $doctor_id,
        ':appointment_date' => $appointment_datetime,
        ':reason' => $reason,
        ':notes' => $notes
    ])) {
        $success = "Appointment scheduled successfully!";
    } else {
        $error = "Error scheduling appointment!";
    }
}

// Handle status updates
if ($_POST['action'] ?? '' === 'update_status') {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE appointments SET status = :status WHERE id = :appointment_id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([':status' => $status, ':appointment_id' => $appointment_id])) {
        $success = "Appointment status updated!";
    } else {
        $error = "Error updating appointment!";
    }
}

// Get appointments with filters
$filter = $_GET['filter'] ?? 'today';
$search = $_GET['search'] ?? '';

$query = "SELECT a.*, p.full_name as patient_name, p.patient_id, u.full_name as doctor_name, u.department 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          JOIN users u ON a.doctor_id = u.id 
          WHERE 1=1";

if ($filter === 'today') {
    $query .= " AND DATE(a.appointment_date) = CURDATE()";
} elseif ($filter === 'upcoming') {
    $query .= " AND a.appointment_date >= NOW()";
} elseif ($filter === 'past') {
    $query .= " AND a.appointment_date < NOW()";
}

if (!empty($search)) {
    $query .= " AND (p.full_name LIKE :search OR p.patient_id LIKE :search OR u.full_name LIKE :search)";
}

$query .= " ORDER BY a.appointment_date ASC";

$stmt = $db->prepare($query);
if (!empty($search)) {
    $search_term = "%$search%";
    $stmt->bindParam(':search', $search_term);
}
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patients for dropdown
$query = "SELECT id, full_name, patient_id FROM patients ORDER BY full_name";
$stmt = $db->prepare($query);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get doctors for dropdown
$query = "SELECT id, full_name, department FROM users WHERE role = 'doctor' AND status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Management - PHCHMS</title>
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
                <h2>Appointments Management</h2>
                <div class="breadcrumb">
                    <span>Records</span> / <span>Appointments</span>
                </div>
            </div>

            <!-- Appointment Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'scheduled' && date('Y-m-d', strtotime($a['appointment_date'])) === date('Y-m-d'); })); ?></h3>
                        <p>Today's Appointments</p>
                        <span class="stat-trend trend-up">Scheduled</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'completed'; })); ?></h3>
                        <p>Completed Today</p>
                        <span class="stat-trend trend-up">Finished</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ⏰
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'scheduled' && strtotime($a['appointment_date']) > time(); })); ?></h3>
                        <p>Upcoming</p>
                        <span class="stat-trend trend-neutral">Future</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ❌
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'cancelled'; })); ?></h3>
                        <p>Cancelled</p>
                        <span class="stat-trend trend-down">This week</span>
                    </div>
                </div>
            </div>

            <!-- Schedule New Appointment -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Schedule New Appointment</h3>
                    <span class="ai-badge">AI Optimized</span>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="appointment-form">
                    <input type="hidden" name="action" value="create_appointment">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="patient_id">Select Patient</label>
                            <select id="patient_id" name="patient_id" required onchange="loadPatientInfo(this.value)">
                                <option value="">Choose a patient...</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['id']; ?>">
                                    <?php echo $patient['full_name']; ?> (<?php echo $patient['patient_id']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="doctor_id">Select Doctor</label>
                            <select id="doctor_id" name="doctor_id" required>
                                <option value="">Choose a doctor...</option>
                                <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    Dr. <?php echo $doctor['full_name']; ?> - <?php echo $doctor['department']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date">Appointment Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time">Appointment Time</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="08:00:00">8:00 AM</option>
                                <option value="09:00:00">9:00 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="12:00:00">12:00 PM</option>
                                <option value="13:00:00">1:00 PM</option>
                                <option value="14:00:00">2:00 PM</option>
                                <option value="15:00:00">3:00 PM</option>
                                <option value="16:00:00">4:00 PM</option>
                                <option value="17:00:00">5:00 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason for Visit</label>
                        <input type="text" id="reason" name="reason" required placeholder="Brief description of the visit reason">
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any special requirements or notes..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-icon">📅</span>
                            Schedule Appointment
                        </button>
                        <button type="button" class="btn-secondary" onclick="clearForm()">
                            <span class="btn-icon">🔄</span>
                            Clear Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Appointments List -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Appointments List</h3>
                    <div class="filter-controls">
                        <select id="filterSelect" onchange="filterAppointments()">
                            <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="upcoming" <?php echo $filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="past" <?php echo $filter === 'past' ? 'selected' : ''; ?>>Past</option>
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                        </select>
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search patients..." value="<?php echo htmlspecialchars($search); ?>">
                            <button onclick="searchAppointments()">🔍</button>
                        </div>
                    </div>
                </div>
                
                <div class="appointments-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Department</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong>
                                    <br>
                                    <span class="text-muted"><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo $appointment['patient_name']; ?></strong>
                                    <br>
                                    <span class="text-muted"><?php echo $appointment['patient_id']; ?></span>
                                </td>
                                <td>Dr. <?php echo $appointment['doctor_name']; ?></td>
                                <td><?php echo $appointment['department']; ?></td>
                                <td><?php echo $appointment['reason']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-primary" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                            View
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn-action btn-outline">⋮</button>
                                            <div class="dropdown-content">
                                                <a href="#" onclick="editAppointment(<?php echo $appointment['id']; ?>)">Edit</a>
                                                <a href="#" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'completed')">Mark Complete</a>
                                                <a href="#" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'cancelled')">Cancel</a>
                                                <a href="#" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">Reschedule</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AI Scheduling Assistant -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Scheduling Assistant</h3>
                    <span class="ai-badge">Smart Recommendations</span>
                </div>
                <div class="ai-assistant">
                    <div class="assistant-recommendation">
                        <div class="recommendation-icon">💡</div>
                        <div class="recommendation-content">
                            <h4>Optimal Scheduling</h4>
                            <p>Based on historical data, mornings (9-11 AM) have the highest patient satisfaction rates.</p>
                            <button class="btn-action btn-primary" onclick="applyOptimalScheduling()">Apply Recommendations</button>
                        </div>
                    </div>
                    
                    <div class="assistant-recommendation">
                        <div class="recommendation-icon">📊</div>
                        <div class="recommendation-content">
                            <h4>Capacity Analysis</h4>
                            <p>Dr. Smith has 3 open slots tomorrow. Consider scheduling complex cases during these times.</p>
                            <button class="btn-action btn-outline" onclick="viewDoctorSchedule()">View Schedule</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function loadPatientInfo(patientId) {
        if (!patientId) return;
        // In real implementation, this would fetch patient details via AJAX
        console.log('Loading patient info for:', patientId);
    }

    function clearForm() {
        document.querySelector('.appointment-form').reset();
    }

    function filterAppointments() {
        const filter = document.getElementById('filterSelect').value;
        window.location.href = 'appointments.php?filter=' + filter;
    }

    function searchAppointments() {
        const search = document.getElementById('searchInput').value;
        const filter = document.getElementById('filterSelect').value;
        window.location.href = 'appointments.php?filter=' + filter + '&search=' + encodeURIComponent(search);
    }

    function viewAppointment(appointmentId) {
        window.location.href = 'appointment-details.php?id=' + appointmentId;
    }

    function editAppointment(appointmentId) {
        window.location.href = 'edit-appointment.php?id=' + appointmentId;
    }

    function updateStatus(appointmentId, status) {
        if (confirm('Update appointment status to ' + status + '?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="appointment_id" value="${appointmentId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function rescheduleAppointment(appointmentId) {
        window.location.href = 'reschedule-appointment.php?id=' + appointmentId;
    }

    function applyOptimalScheduling() {
        alert('Applying AI scheduling recommendations...');
    }

    function viewDoctorSchedule() {
        alert('Opening doctor schedule view...');
    }

    // Initialize date to today
    document.getElementById('appointment_date').valueAsDate = new Date();
    </script>

    <style>
    .appointment-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .filter-controls {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .search-box {
        display: flex;
        gap: 0.5rem;
    }

    .search-box input {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        width: 250px;
    }

    .appointments-table {
        overflow-x: auto;
    }

    .action-buttons {
        display: flex;
        gap: 0.3rem;
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background: white;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 6px;
        z-index: 1;
    }

    .dropdown-content a {
        color: #333;
        padding: 0.8rem 1rem;
        text-decoration: none;
        display: block;
        border-bottom: 1px solid #f0f0f0;
    }

    .dropdown-content a:hover {
        background: #f8f9fa;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .ai-assistant {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .assistant-recommendation {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #2980b9;
    }

    .recommendation-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .recommendation-content h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .recommendation-content p {
        margin: 0 0 1rem 0;
        color: #7f8c8d;
    }

    .text-muted {
        color: #6c757d;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .filter-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box input {
            width: 100%;
        }

        .action-buttons {
            flex-direction: column;
        }

        .assistant-recommendation {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>
</body>
</html>