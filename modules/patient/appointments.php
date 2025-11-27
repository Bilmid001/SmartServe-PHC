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

// Handle appointment booking
if ($_POST['action'] ?? '' === 'book_appointment') {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];
    $doctor_id = $_POST['doctor_id'];
    
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    
    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason, status) 
              VALUES (:patient_id, :doctor_id, :appointment_date, :reason, 'scheduled')";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':patient_id' => $patient_id,
        ':doctor_id' => $doctor_id,
        ':appointment_date' => $appointment_datetime,
        ':reason' => $reason
    ])) {
        $success = "Appointment booked successfully!";
    } else {
        $error = "Error booking appointment!";
    }
}

// Get patient appointments
$query = "SELECT a.*, u.full_name as doctor_name, u.department 
          FROM appointments a 
          JOIN users u ON a.doctor_id = u.id 
          WHERE a.patient_id = :patient_id 
          ORDER BY a.appointment_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available doctors
$query = "SELECT * FROM users WHERE role = 'doctor' AND status = 'active'";
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
    <title>My Appointments - PHCHMS</title>
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
                <h2>My Appointments</h2>
                <div class="breadcrumb">
                    <span>Patient</span> / <span>Appointments</span>
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
                        <h3><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'scheduled'; })); ?></h3>
                        <p>Upcoming Appointments</p>
                        <span class="stat-trend trend-neutral">Scheduled</span>
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
                        <p>Completed Visits</p>
                        <span class="stat-trend trend-up">All done</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            👨‍⚕️
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_unique(array_column($appointments, 'doctor_id'))); ?></h3>
                        <p>Doctors Visited</p>
                        <span class="stat-trend trend-up">+2 this year</span>
                    </div>
                </div>
            </div>

            <!-- Book New Appointment -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Book New Appointment</h3>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="appointment-form">
                    <input type="hidden" name="action" value="book_appointment">
                    <div class="form-row">
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
                        <div class="form-group">
                            <label for="appointment_date">Appointment Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_time">Preferred Time</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="09:00:00">9:00 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="14:00:00">2:00 PM</option>
                                <option value="15:00:00">3:00 PM</option>
                                <option value="16:00:00">4:00 PM</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason for Visit</label>
                            <input type="text" id="reason" name="reason" required placeholder="Briefly describe your symptoms">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Book Appointment</button>
                </form>
            </div>

            <!-- AI Symptom Checker -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Symptom Checker</h3>
                    <span class="ai-badge">Powered by AI</span>
                </div>
                <div class="symptom-checker">
                    <p>Get preliminary assessment before your appointment</p>
                    <div class="symptom-input">
                        <textarea id="symptoms" placeholder="Describe your symptoms in detail..."></textarea>
                        <button class="btn-primary" onclick="checkSymptoms()">Analyze Symptoms</button>
                    </div>
                    <div id="symptom-results" class="symptom-results" style="display: none;">
                        <h4>AI Analysis Results</h4>
                        <div class="results-content"></div>
                    </div>
                </div>
            </div>

            <!-- Appointment History -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Appointment History</h3>
                </div>
                <div class="appointments-list">
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-item">
                            <div class="appointment-info">
                                <div class="appointment-datetime">
                                    <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong>
                                    <span><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="appointment-details">
                                    <h4>Dr. <?php echo $appointment['doctor_name']; ?></h4>
                                    <p><?php echo $appointment['department']; ?></p>
                                    <p class="appointment-reason"><?php echo $appointment['reason']; ?></p>
                                </div>
                                <div class="appointment-status">
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <?php if ($appointment['status'] === 'scheduled'): ?>
                                    <button class="btn-sm btn-outline" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">
                                        Reschedule
                                    </button>
                                    <button class="btn-sm btn-danger" onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                        Cancel
                                    </button>
                                <?php endif; ?>
                                <button class="btn-sm btn-primary" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                    Details
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-appointments">
                            <p>No appointments found. Book your first appointment above!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function checkSymptoms() {
        const symptoms = document.getElementById('symptoms').value;
        if (symptoms.trim() === '') {
            alert('Please describe your symptoms.');
            return;
        }
        
        // Simulate AI analysis
        const conditions = ['Common Cold', 'Allergic Rhinitis', 'Migraine', 'Stress-related symptoms'];
        const randomCondition = conditions[Math.floor(Math.random() * conditions.length)];
        const urgency = Math.random() > 0.8 ? 'Seek immediate medical attention' : 'Schedule a routine appointment';
        
        const resultsDiv = document.getElementById('symptom-results');
        const resultsContent = resultsDiv.querySelector('.results-content');
        
        resultsContent.innerHTML = `
            <div class="result-item">
                <h5>Possible Conditions:</h5>
                <p>${randomCondition}</p>
            </div>
            <div class="result-item">
                <h5>Recommendation:</h5>
                <p>${urgency}</p>
            </div>
            <div class="result-item">
                <h5>Self-care Tips:</h5>
                <p>Rest, stay hydrated, and monitor symptoms. Contact healthcare provider if symptoms worsen.</p>
            </div>
            <div class="disclaimer">
                <small>This is an AI-powered preliminary assessment and should not replace professional medical advice.</small>
            </div>
        `;
        
        resultsDiv.style.display = 'block';
    }
    
    function rescheduleAppointment(appointmentId) {
        alert('Reschedule functionality for appointment: ' + appointmentId);
    }
    
    function cancelAppointment(appointmentId) {
        if (confirm('Are you sure you want to cancel this appointment?')) {
            alert('Cancel appointment: ' + appointmentId);
        }
    }
    
    function viewAppointment(appointmentId) {
        window.location.href = 'appointment-details.php?id=' + appointmentId;
    }
    </script>
</body>
</html>