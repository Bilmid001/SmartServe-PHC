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

// Handle patient actions
if ($_POST['action'] ?? '' === 'create_patient') {
    $patient_id = 'PAT' . date('Ymd') . rand(100, 999);
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $emergency_contact = $_POST['emergency_contact'];
    $blood_type = $_POST['blood_type'];
    $allergies = $_POST['allergies'];
    $medical_history = $_POST['medical_history'];
    
    $query = "INSERT INTO patients (patient_id, full_name, date_of_birth, gender, address, phone, email, emergency_contact, blood_type, allergies, medical_history) 
              VALUES (:patient_id, :full_name, :date_of_birth, :gender, :address, :phone, :email, :emergency_contact, :blood_type, :allergies, :medical_history)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([
        ':patient_id' => $patient_id,
        ':full_name' => $full_name,
        ':date_of_birth' => $date_of_birth,
        ':gender' => $gender,
        ':address' => $address,
        ':phone' => $phone,
        ':email' => $email,
        ':emergency_contact' => $emergency_contact,
        ':blood_type' => $blood_type,
        ':allergies' => $allergies,
        ':medical_history' => $medical_history
    ])) {
        $success = "Patient registered successfully! Patient ID: " . $patient_id;
    } else {
        $error = "Error registering patient!";
    }
}

// Get all patients
$query = "SELECT * FROM patients ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - PHCHMS</title>
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
    
    <div class="dashboard-container">
        
        <div class="main-content">
            <div class="content-header">
                <h2>Patient Records Management</h2>
                <div class="breadcrumb">
                    <span>Records</span> / <span>Patients</span>
                </div>
            </div>

            <!-- Patient Registration Form -->
            <div class="dashboard-section">
                <h3>Register New Patient</h3>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="patient-form">
                    <input type="hidden" name="action" value="create_patient">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth *</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <select id="blood_type" name="blood_type">
                                <option value="">Select</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="emergency_contact">Emergency Contact</label>
                        <input type="text" id="emergency_contact" name="emergency_contact">
                    </div>
                    <div class="form-group">
                        <label for="allergies">Allergies</label>
                        <textarea id="allergies" name="allergies" rows="2" placeholder="List any known allergies"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="medical_history">Medical History</label>
                        <textarea id="medical_history" name="medical_history" rows="3" placeholder="Previous medical conditions, surgeries, etc."></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Register Patient</button>
                </form>
            </div>

            <!-- Patients List -->
            <div class="dashboard-section">
                <h3>Patient Records</h3>
                <div class="search-box">
                    <input type="text" id="patientSearch" placeholder="Search patients...">
                    <button onclick="searchPatients()">Search</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Full Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Phone</th>
                                <th>Last Visit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): 
                                $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
                            ?>
                            <tr>
                                <td><strong><?php echo $patient['patient_id']; ?></strong></td>
                                <td><?php echo $patient['full_name']; ?></td>
                                <td><?php echo $patient['gender']; ?></td>
                                <td><?php echo $age; ?> years</td>
                                <td><?php echo $patient['phone']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($patient['created_at'])); ?></td>
                                <td>
                                    <button class="btn-sm btn-primary" onclick="viewPatient(<?php echo $patient['id']; ?>)">View</button>
                                    <button class="btn-sm btn-secondary" onclick="editPatient(<?php echo $patient['id']; ?>)">Edit</button>
                                    <button class="btn-sm btn-info" onclick="viewHistory(<?php echo $patient['id']; ?>)">History</button>
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
    function viewPatient(patientId) {
        window.location.href = 'patient-details.php?id=' + patientId;
    }
    
    function editPatient(patientId) {
        window.location.href = 'edit-patient.php?id=' + patientId;
    }
    
    function viewHistory(patientId) {
        window.location.href = 'patient-history.php?id=' + patientId;
    }
    
    function searchPatients() {
        const query = document.getElementById('patientSearch').value;
        // Implement search functionality
        alert('Searching for: ' + query);
    }
    </script>
</body>
</html>