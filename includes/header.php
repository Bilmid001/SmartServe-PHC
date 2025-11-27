<?php
$user_role = $_SESSION['role'] ?? 'default';

$role_classes = [
    'admin' => 'admin-header',
    'doctor' => 'doctor-header',
    'records' => 'records-header',
    'pharmacy' => 'pharmacy-header',
    'lab' => 'lab-header',
    'eha' => 'eha-header',
    'patient' => 'patient-header'
];

$header_class = $role_classes[$user_role] ?? 'default-header';
?>
<style>
/* ─────────────────────────── HEADER — PROTECTED STYLE ─────────────────────────── */
.dashboard-header {
    position: fixed !important;
    top: 0 !important;
    left: 260px !important; /* Because sidebar is 260px */
    height: 65px !important;
    width: calc(100% - 260px) !important;
    background: #2a2a40 !important;
    color: white !important;
    display: flex !important;
    align-items: center !important;
    z-index: 99999 !important;
    padding: 0 20px !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important;
    font-family: Arial, sans-serif !important;
}

.header-content {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
}

/* Logo / Role name section */
.logo {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

.logo-icon {
    font-size: 1.8rem !important;
}

.logo h1 {
    font-size: 20px !important;
    margin: 0 !important;
    font-weight: bold !important;
}

/* User info area */
.user-info {
    display: flex !important;
    align-items: center !important;
    gap: 25px !important;
}

/* Welcome text */
.user-welcome {
    display: flex !important;
    flex-direction: column !important;
    text-align: right !important;
}

.welcome-text {
    font-size: 13px !important;
    color: #ccc !important;
}

.user-name {
    font-size: 15px !important;
    font-weight: bold !important;
}

/* Header Buttons */
.header-actions {
    display: flex !important;
    align-items: center !important;
    gap: 15px !important;
}

/* Notification button */
.header-btn {
    position: relative !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer !important;
    font-size: 1.3rem !important;
    color: white !important;
}

.notification-count {
    position: absolute !important;
    top: -5px !important;
    right: -8px !important;
    background: red !important;
    color: white !important;
    font-size: 10px !important;
    padding: 1px 5px !important;
    border-radius: 50% !important;
}

/* Logout button */
.logout-btn {
    background: #ff4d4d !important;
    color: white !important;
    padding: 7px 15px !important;
    border-radius: 5px !important;
    text-decoration: none !important;
    font-size: 14px !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    transition: 0.2s !important;
}

.logout-btn:hover {
    background: #d93636 !important;
}

/* Bootstrap icon styling */
.logout-icon,
.btn-icon {
    font-size: 1.2rem !important;
}

/* Prevent content from going under header */
.main-content {
    margin-top: 2px !important;
}
</style>



<!-- ─────────────────────────── HEADER HTML ─────────────────────────── -->
<div class="dashboard-header <?= $header_class ?>">
    <div class="header-content">

        <div class="logo">
            <span class="logo-icon">
                <?php 
                switch($user_role) {
                    case 'admin': echo '<i class="bi bi-shield-lock"></i>'; break;
                    case 'doctor': echo '<i class="bi bi-heart-pulse"></i>'; break;
                    case 'records': echo '<i class="bi bi-folder2-open"></i>'; break;
                    case 'pharmacy': echo '<i class="bi bi-capsule"></i>'; break;
                    case 'lab': echo '<i class="bi bi-flask"></i>'; break;
                    case 'eha': echo '<i class="bi bi-tree"></i>'; break;
                    case 'patient': echo '<i class="bi bi-person-circle"></i>'; break;
                    default: echo '<i class="bi bi-hospital"></i>';
                }
                ?>
            </span>
            <h1>PHCHMS <?= ucfirst($user_role) ?></h1>
        </div>

        <div class="user-info">
            <div class="user-welcome">
                <span class="welcome-text">
                    <?php 
                    switch($user_role) {
                        case 'admin': echo 'System Administrator'; break;
                        case 'doctor': echo 'Welcome, Doctor'; break;
                        case 'records': echo 'Records Officer'; break;
                        case 'pharmacy': echo 'Pharmacy Staff'; break;
                        case 'lab': echo 'Lab Technician'; break;
                        case 'eha': echo 'EHA Officer'; break;
                        case 'patient': echo 'Patient Portal'; break;
                        default: echo 'Welcome';
                    }
                    ?>
                </span>
                <span class="user-name"><?= $_SESSION['full_name'] ?? 'User' ?></span>
            </div>

            <div class="header-actions">
                <button class="header-btn" onclick="showNotifications()">
                    <i class="bi bi-bell btn-icon"></i>
                    <span class="notification-count">3</span>
                </button>
                <a href="../../logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-right logout-icon"></i>
                    Logout
                </a>
            </div>
        </div>

    </div>
</div>


<script>
function showNotifications() {
    alert('Notifications Panel Coming Soon!');
}
</script>
