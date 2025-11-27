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

// Get patient data
$query = "SELECT * FROM patients WHERE id = :patient_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_POST['action'] ?? '' === 'update_profile') {
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
    
    $query = "UPDATE patients SET 
              full_name = :full_name,
              date_of_birth = :date_of_birth,
              gender = :gender,
              address = :address,
              phone = :phone,
              email = :email,
              emergency_contact = :emergency_contact,
              blood_type = :blood_type,
              allergies = :allergies,
              medical_history = :medical_history
              WHERE id = :patient_id";
    
    $stmt = $db->prepare($query);
    if ($stmt->execute([
        ':full_name' => $full_name,
        ':date_of_birth' => $date_of_birth,
        ':gender' => $gender,
        ':address' => $address,
        ':phone' => $phone,
        ':email' => $email,
        ':emergency_contact' => $emergency_contact,
        ':blood_type' => $blood_type,
        ':allergies' => $allergies,
        ':medical_history' => $medical_history,
        ':patient_id' => $patient_id
    ])) {
        $success = "Profile updated successfully!";
        // Refresh patient data
        $query = "SELECT * FROM patients WHERE id = :patient_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Error updating profile!";
    }
}
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    
    <style>
    .profile-overview {
        padding: 1rem 0;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 2px solid #e9ecef;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #c0392b, #a93226);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
    }

    .profile-info h2 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .patient-id {
        color: #7f8c8d;
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .profile-badges {
        display: flex;
        gap: 0.5rem;
    }

    .badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-primary {
        background: #3498db;
        color: white;
    }

    .badge-success {
        background: #27ae60;
        color: white;
    }

    .profile-details {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .detail-section h4 {
        color: #2c3e50;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-weight: 600;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .detail-value {
        color: #2c3e50;
        line-height: 1.5;
    }

    .health-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #c0392b;
    }

    .summary-icon {
        font-size: 2rem;
    }

    .summary-content h4 {
        margin: 0 0 0.3rem 0;
        color: #2c3e50;
        font-size: 1rem;
    }

    .summary-content p {
        margin: 0;
        color: #7f8c8d;
        font-weight: 600;
    }

    .profile-form {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .form-section {
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .form-section h4 {
        margin: 0 0 1.5rem 0;
        color: #2c3e50;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
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

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 0.8rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #c0392b;
        box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .emergency-info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .emergency-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #fff3cd;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }

    .emergency-icon {
        font-size: 2rem;
    }

    .emergency-content h4 {
        margin: 0 0 0.5rem 0;
        color: #856404;
    }

    .emergency-content p {
        margin: 0;
        color: #856404;
    }

    .emergency-details {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .emergency-contact,
    .emergency-medical {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .medical-alerts {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .alert-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem;
        border-radius: 6px;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .alert-info {
        background: #cce7ff;
        color: #004085;
        border: 1px solid #b3d7ff;
    }

    .account-settings {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #c0392b;
    }

    .setting-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .setting-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .health-summary {
            grid-template-columns: 1fr;
        }

        .setting-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .form-actions {
            flex-direction: column;
        }
    }
    </style>
</head>
<body>
        <div class="main-content">
            <div class="content-header">
                <h2>My Profile</h2>
                <div class="breadcrumb">
                    <span>Patient</span> / <span>Profile</span>
                </div>
            </div>

            <!-- Profile Overview -->
            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Personal Information</h3>
                        <button class="btn-primary" onclick="editProfile()">
                            <span class="btn-icon">✏️</span>
                            Edit Profile
                        </button>
                    </div>
                    <div class="profile-overview">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <span class="avatar-icon">👤</span>
                            </div>
                            <div class="profile-info">
                                <h2><?php echo htmlspecialchars($patient['full_name']); ?></h2>
                                <p class="patient-id">Patient ID: <?php echo htmlspecialchars($patient['patient_id']); ?></p>
                                <div class="profile-badges">
                                    <span class="badge badge-primary">Active Patient</span>
                                    <span class="badge badge-success">Verified</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-details">
                            <div class="detail-section">
                                <h4>Basic Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">Date of Birth:</span>
                                        <span class="detail-value"><?php echo $patient['date_of_birth'] ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'Not set'; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Gender:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($patient['gender'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Blood Type:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($patient['blood_type'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Age:</span>
                                        <span class="detail-value">
                                            <?php 
                                            if ($patient['date_of_birth']) {
                                                $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
                                                echo $age . ' years';
                                            } else {
                                                echo 'Not set';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4>Contact Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">Phone:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($patient['phone'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($patient['email'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Emergency Contact:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($patient['emergency_contact'] ?? 'Not set'); ?></span>
                                    </div>
                                    <div class="detail-item full-width">
                                        <span class="detail-label">Address:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($patient['address'] ?? 'Not set'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4>Medical Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item full-width">
                                        <span class="detail-label">Allergies:</span>
                                        <span class="detail-value"><?php echo $patient['allergies'] ? nl2br(htmlspecialchars($patient['allergies'])) : 'No known allergies'; ?></span>
                                    </div>
                                    <div class="detail-item full-width">
                                        <span class="detail-label">Medical History:</span>
                                        <span class="detail-value"><?php echo $patient['medical_history'] ? nl2br(htmlspecialchars($patient['medical_history'])) : 'No significant medical history'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Health Summary</h3>
                    </div>
                    <div class="health-summary">
                        <div class="summary-item">
                            <div class="summary-icon">📅</div>
                            <div class="summary-content">
                                <h4>Last Visit</h4>
                                <p>2 weeks ago</p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon">💊</div>
                            <div class="summary-content">
                                <h4>Current Medications</h4>
                                <p>3 active prescriptions</p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon">🔬</div>
                            <div class="summary-content">
                                <h4>Recent Tests</h4>
                                <p>2 tests this month</p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon">⭐</div>
                            <div class="summary-content">
                                <h4>Health Score</h4>
                                <p>85/100</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="content-card" id="editProfileForm" style="display: none;">
                <div class="card-header">
                    <h3>Edit Profile</h3>
                    <button class="btn-secondary" onclick="cancelEdit()">
                        <span class="btn-icon">❌</span>
                        Cancel
                    </button>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-section">
                        <h4>Personal Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($patient['date_of_birth']); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($patient['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($patient['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($patient['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="blood_type">Blood Type</label>
                                <select id="blood_type" name="blood_type">
                                    <option value="">Select Blood Type</option>
                                    <option value="A+" <?php echo ($patient['blood_type'] ?? '') === 'A+' ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo ($patient['blood_type'] ?? '') === 'A-' ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo ($patient['blood_type'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo ($patient['blood_type'] ?? '') === 'B-' ? 'selected' : ''; ?>>B-</option>
                                    <option value="AB+" <?php echo ($patient['blood_type'] ?? '') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo ($patient['blood_type'] ?? '') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                    <option value="O+" <?php echo ($patient['blood_type'] ?? '') === 'O+' ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo ($patient['blood_type'] ?? '') === 'O-' ? 'selected' : ''; ?>>O-</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Contact Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact">Emergency Contact</label>
                            <input type="text" id="emergency_contact" name="emergency_contact" value="<?php echo htmlspecialchars($patient['emergency_contact'] ?? ''); ?>" placeholder="Name and phone number">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($patient['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Medical Information</h4>
                        <div class="form-group">
                            <label for="allergies">Allergies</label>
                            <textarea id="allergies" name="allergies" rows="3" placeholder="List any known allergies..."><?php echo htmlspecialchars($patient['allergies'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="medical_history">Medical History</label>
                            <textarea id="medical_history" name="medical_history" rows="4" placeholder="Previous medical conditions, surgeries, chronic illnesses..."><?php echo htmlspecialchars($patient['medical_history'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-icon">💾</span>
                            Save Changes
                        </button>
                        <button type="button" class="btn-secondary" onclick="cancelEdit()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Emergency Information -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Emergency Information</h3>
                    <span class="emergency-badge">Important</span>
                </div>
                <div class="emergency-info">
                    <div class="emergency-item">
                        <div class="emergency-icon">🆘</div>
                        <div class="emergency-content">
                            <h4>In Case of Emergency</h4>
                            <p>This information is critical for healthcare providers in emergency situations.</p>
                        </div>
                    </div>
                    <div class="emergency-details">
                        <div class="emergency-contact">
                            <strong>Emergency Contact:</strong>
                            <span><?php echo htmlspecialchars($patient['emergency_contact'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="emergency-medical">
                            <strong>Critical Medical Information:</strong>
                            <div class="medical-alerts">
                                <?php if ($patient['allergies']): ?>
                                <div class="alert-item alert-warning">
                                    <span class="alert-icon">⚠️</span>
                                    <span class="alert-text">Allergies: <?php echo htmlspecialchars($patient['allergies']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($patient['blood_type']): ?>
                                <div class="alert-item alert-info">
                                    <span class="alert-icon">💉</span>
                                    <span class="alert-text">Blood Type: <?php echo htmlspecialchars($patient['blood_type']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Account Settings</h3>
                </div>
                <div class="account-settings">
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Login Credentials</h4>
                            <p>Update your username and password</p>
                        </div>
                        <button class="btn-outline" onclick="changePassword()">
                            Change Password
                        </button>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Notification Preferences</h4>
                            <p>Manage how you receive alerts and reminders</p>
                        </div>
                        <button class="btn-outline" onclick="manageNotifications()">
                            Manage
                        </button>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Privacy Settings</h4>
                            <p>Control your data sharing preferences</p>
                        </div>
                        <button class="btn-outline" onclick="privacySettings()">
                            Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editProfile() {
        document.getElementById('editProfileForm').style.display = 'block';
        window.scrollTo({ top: document.getElementById('editProfileForm').offsetTop, behavior: 'smooth' });
    }

    function cancelEdit() {
        document.getElementById('editProfileForm').style.display = 'none';
    }

    function changePassword() {
        alert('Opening password change dialog...');
    }

    function manageNotifications() {
        alert('Opening notification preferences...');
    }

    function privacySettings() {
        alert('Opening privacy settings...');
    }

    // Form validation
    document.querySelector('.profile-form').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let valid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                valid = false;
                field.style.borderColor = '#e74c3c';
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    </script>
</body>
</html>