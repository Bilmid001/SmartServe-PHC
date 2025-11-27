<?php
$user_role = $_SESSION['role'] ?? 'default';

$role_classes = [
    'admin' => 'admin-sidebar',
    'doctor' => 'doctor-sidebar',
    'records' => 'records-sidebar',
    'pharmacy' => 'pharmacy-sidebar',
    'lab' => 'lab-sidebar',
    'eha' => 'eha-sidebar',
    'patient' => 'patient-sidebar'
];

$sidebar_class = $role_classes[$user_role] ?? 'default-sidebar';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>

/* FORCE VERTICAL SIDEBAR — CANNOT BECOME HORIZONTAL */
.sidebar {
    position: fixed !important;
    top: 0;
    left: 0;
    height: 100vh !important;
    width: 260px !important;
    background: #1f1f2f !important;
    color: #fff !important;
    padding-top: 50px !important;
    overflow-y: auto !important;
    z-index: 99999 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: flex-start !important;
    white-space: normal !important;
}

/* Ensure list behaves vertical */
.sidebar ul {
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    display: block !important;
}

.sidebar li {
    display: block !important;
    width: 100% !important;

}

/* Links forced to be full-width vertical */
.sidebar .nav-link {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-start !important;

    width: 100% !important;
    padding: 12px 20px !important;
    color: #ccc !important;
    text-decoration: none !important;
    border-left: 3px solid transparent !important;
}

.sidebar .nav-link:hover,
.nav-item.active .nav-link {
    background: #29293f !important;
    color: #fff !important;
    border-left: 3px solid #0d6efd !important;
}

.nav-icon {
    margin-right: 12px !important;
    font-size: 1.2rem !important;
}

/* Main content always moves right of sidebar */
.main-content {
    margin-left: 260px !important;
    padding: 20px !important;
}
</style>

    <!-- ──────────────── SIDEBAR + MAIN CONTENT WRAPPER ──────────────── -->
    <div class="sidebar <?php echo $sidebar_class; ?>">
        <nav class="sidebar-nav">

            <?php if ($user_role == 'admin'): ?>
            <!-- ADMIN -->
            <div class="nav-section">
                <h3 class="nav-section-title">Administration</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-speedometer2 nav-icon"></i> Admin Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>">
                        <a href="users.php" class="nav-link">
                            <i class="bi bi-people nav-icon"></i> User Management
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='analytics.php'?'active':'' ?>">
                        <a href="analytics.php" class="nav-link">
                            <i class="bi bi-bar-chart nav-icon"></i> AI Analytics
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='reports.php'?'active':'' ?>">
                        <a href="reports.php" class="nav-link">
                            <i class="bi bi-file-earmark-bar-graph nav-icon"></i> Reports
                        </a>
                    </li>
                </ul>
            </div>

            <?php elseif ($user_role == 'doctor'): ?>
            <!-- DOCTOR -->
            <div class="nav-section">
                <h3 class="nav-section-title">Clinical</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-speedometer2 nav-icon"></i> Doctor Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='patients.php'?'active':'' ?>">
                        <a href="patients.php" class="nav-link">
                            <i class="bi bi-person-lines-fill nav-icon"></i> My Patients
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='prescriptions.php'?'active':'' ?>">
                        <a href="prescriptions.php" class="nav-link">
                            <i class="bi bi-capsule nav-icon"></i> Prescriptions
                        </a>
                    </li>
                     <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='teleconsultation.php'?'active':'' ?>">
                        <a href="teleconsultation.php" class="nav-link">
                            <i class="bi bi-person nav-icon"></i> Teleconsultation
                        </a>
                    </li>
                </ul>
            </div>

            <?php elseif ($user_role == 'records'): ?>
            <!-- RECORDS -->
            <div class="nav-section">
                <h3 class="nav-section-title">Records Management</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-speedometer2 nav-icon"></i> Records Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='patients.php'?'active':'' ?>">
                        <a href="patients.php" class="nav-link">
                            <i class="bi bi-journal-medical nav-icon"></i> Patient Records
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='appointments.php'?'active':'' ?>">
                        <a href="appointments.php" class="nav-link">
                            <i class="bi bi-calendar2-week nav-icon"></i> Appointments
                        </a>
                    </li>
                     <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='authorization.php'?'active':'' ?>">
                        <a href="authorization.php" class="nav-link">
                            <i class="bi bi-journal-medical nav-icon"></i> Authorization
                        </a>
                    </li>
                </ul>
            </div>

            <?php elseif ($user_role == 'pharmacy'): ?>
            <!-- PHARMACY -->
            <div class="nav-section">
                <h3 class="nav-section-title">Pharmacy</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-speedometer2 nav-icon"></i> Pharmacy Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='inventory.php'?'active':'' ?>">
                        <a href="inventory.php" class="nav-link">
                            <i class="bi bi-box-seam nav-icon"></i> Inventory
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='prescriptions.php'?'active':'' ?>">
                        <a href="prescriptions.php" class="nav-link">
                            <i class="bi bi-capsule nav-icon"></i> Prescriptions
                        </a>
                    </li>
                </ul>
            </div>

            <?php elseif ($user_role == 'lab'): ?>
            <!-- LAB -->
            <div class="nav-section">
                <h3 class="nav-section-title">Laboratory</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-speedometer2 nav-icon"></i> Lab Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='tests.php'?'active':'' ?>">
                        <a href="tests.php" class="nav-link">
                            <i class="bi bi-eyedropper nav-icon"></i> Lab Tests
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='results.php'?'active':'' ?>">
                        <a href="results.php" class="nav-link">
                            <i class="bi bi-file-medical nav-icon"></i> Test Results
                        </a>
                    </li>
                </ul>
            </div>

            <?php elseif ($user_role == 'eha'): ?>
            <!-- EHA -->
            <div class="nav-section">
                <h3 class="nav-section-title">Environmental Health</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-speedometer2 nav-icon"></i> EHA Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='inspections.php'?'active':'' ?>">
                        <a href="inspections.php" class="nav-link">
                            <i class="bi bi-binoculars nav-icon"></i> Inspections
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='visits.php'?'active':'' ?>">
                        <a href="visits.php" class="nav-link">
                            <i class="bi bi-house-door nav-icon"></i> Home Visits
                        </a>
                    </li>
                </ul>
            </div>

            <?php elseif ($user_role == 'patient'): ?>
            <!-- PATIENT -->
            <div class="nav-section">
                <h3 class="nav-section-title">My Health</h3>
                <ul>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-person-heart nav-icon"></i> Patient Dashboard
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='appointments.php'?'active':'' ?>">
                        <a href="appointments.php" class="nav-link">
                            <i class="bi bi-calendar-heart nav-icon"></i> Appointments
                        </a>
                    </li>

                    <li class="nav-item <?= basename($_SERVER['PHP_SELF'])=='records.php'?'active':'' ?>">
                        <a href="records.php" class="nav-link">
                            <i class="bi bi-file-earmark-medical nav-icon"></i> Medical Records
                        </a>
                    </li>
                    
                </ul>
            </div>
            <?php endif; ?>

        <!-- COMMON LINKS -->
    <div class="nav-section">
        <h3 class="nav-section-title">Quick Access</h3>
        <ul>
            <li class="nav-item">
                <a href="ai-features.php" class="nav-link">
                    <i class="bi bi-robot nav-icon"></i> AI Features
                </a>
            </li>
            <li>
            <a href="../../logout.php" class="logout-btn">
                <i class="bi bi-box-arrow-right logout-icon"></i>
                Logout</a></div>
        </ul>
    </div>
    </nav>
    </div>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-content">
