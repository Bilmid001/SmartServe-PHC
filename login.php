<?php
require_once 'config/init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $department = $_POST['department'];
    
    $database = new Database();
    // $db = $database->getConnection();
    

    $user = $db->table('users')->where('username', $username)
                                ->where('role', $department)
                                ->where('status', 'active')
                                ->first();

    // For demo purposes - in production, use proper password hashing
    // $query = "SELECT * FROM users WHERE username = :username AND role = :role AND status = 'active'";
    // $stmt = $db->prepare($query);
    // $stmt->bindParam(':username', $username);
    // $stmt->bindParam(':role', $department);
    // $stmt->execute();
    
    if ($user) {
        
        // Demo password check - in production, use password_verify()
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Redirect to role-specific dashboard
            switch($user['role']) {
                case 'admin':
                    header("Location: modules/admin/index.php");
                    break;
                case 'doctor':
                    header("Location: modules/doctors/index.php");
                    break;
                case 'records':
                    header("Location: modules/records/index.php");
                    break;
                case 'pharmacy':
                    header("Location: modules/pharmacy/index.php");
                    break;
                case 'lab':
                    header("Location: modules/laboratory/index.php");
                    break;
                case 'eha':
                    header("Location: modules/eha/index.php");
                    break;
                case 'patient':
                    header("Location: modules/patient/index.php");
                    break;
                default:
                    header("Location: modules/admin/index.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found or inactive!";
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: modules/$role/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHCHMS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
        --primary-blue: #1a73e8;
        --teal: #009688;
        --green: #4caf50;
        --white: #ffffff;
        --navy: #0d47a1;
        --light-gray: #f5f5f5;
        --dark-gray: #424242;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .login-container {
        padding:  10px;
        background: var(--white);
        border-radius: 10px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        height: 90vh;
        max-height: 700px;
        width: 95%;
        max-width: 1200px;
    }
    
    .carousel-section {
        background: linear-gradient(135deg, var(--primary-blue), var(--navy));
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    
    .carousel-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    
    .carousel-item {
        height: 100%;
        position: relative;
    }
    
    .carousel-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .carousel-content {
        position: absolute;
        bottom: 60px;
        left: 40px;
        right: 40px;
        color: var(--white);
        z-index: 2;
    }
    
    .carousel-content h3 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }
    
    .carousel-content p {
        font-size: 1rem;
        opacity: 0.9;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 8rem;
    }
    
    .login-section {
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--white);
    }
    
    .logo-container {
        text-align: center;
    }
    
    .logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-blue), var(--navy));
        border-radius: 50%;
        margin-bottom: 0.5rem;
    }
    
    .logo-icon {
        font-size: 2rem;
        color: var(--white);
    }
    
    .login-title {
        color: var(--navy);
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 1.8rem;
    }
    
    .login-subtitle {
        color: var(--dark-gray);
        margin-bottom: 1.0rem;
        font-size: 0.95rem;
    }
    
    .form-group {
        margin-bottom: 1.2rem;
    }
    
    .form-label {
        color: var(--dark-gray);
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        height: 45px;
    }
    
    .form-control:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25);
    }
    
    .input-group {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dark-gray);
        z-index: 3;
    }
    
    .input-with-icon {
        padding-left: 45px;
    }
    
    .btn-login {
        background: linear-gradient(135deg, var(--primary-blue), var(--navy));
        border: none;
        border-radius: 10px;
        padding: 0.7rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--white);
        transition: all 0.3s ease;
        width: 100%;
        height: 45px;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(26, 115, 232, 0.3);
    }
    
    .demo-credentials {
        background: var(--light-gray);
        border-radius: 10px;
        padding: 1.2rem;
        margin-top: 1.5rem;
    }
    
    .demo-title {
        color: var(--navy);
        font-weight: 600;
        margin-bottom: 0.8rem;
        font-size: 0.9rem;
    }
    
    .credential-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.4rem;
        padding: 0.4rem 0;
        border-bottom: 1px solid #dee2e6;
        font-size: 0.85rem;
    }
    
    .credential-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .role-badge {
        background: var(--primary-blue);
        color: var(--white);
        padding: 0.2rem 0.6rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .alert {
        border-radius: 10px;
        border: none;
        padding: 0.8rem 1.2rem;
        margin-bottom: 1.2rem;
        font-size: 0.9rem;
    }
    
    .carousel-indicators [data-bs-target] {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin: 0 4px;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        margin: 0 15px;
    }

    /* Remove any vertical scrolling */
    .login-section::-webkit-scrollbar {
        width: 0;
        background: transparent;
    }

    @media (max-width: 768px) {
        .login-container {
            height: 95vh;
            width: 95%;
            margin: 0 auto;
        }
        
        .carousel-section {
            height: 35vh;
        }
        
        .carousel-item {
            height: 35vh;
        }
        
        .carousel-content {
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        
        .carousel-content h3 {
            font-size: 1.3rem;
        }
        
        .carousel-content p {
            font-size: 0.9rem;
        }
        
        .login-section {
            padding: 1.5rem;
            height: 60vh;
        }
        
        .logo {
            width: 60px;
            height: 60px;
        }
        
        .logo-icon {
            font-size: 1.7rem;
        }
        
        .login-title {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 576px) {
        body {
            padding: 10px;
        }
        
        .login-container {
            height: 98vh;
            width: 100%;
        }
        
        .carousel-section {
            height: 30vh;
        }
        
        .carousel-item {
            height: 30vh;
        }
        
        .login-section {
            height: 68vh;
            padding: 1rem;
        }
    }
    .logo-image {
    width: 90px;  /* Adjust size as needed */
    height: 90px; /* Adjust size as needed */
    object-fit: contain;
    border-radius: 50px; /* Optional: remove if you don't want rounded corners */
    }
</style>
</head>
<body>
    <div class="container">
        <div class="row login-container">
            <!-- Left Side - Image Carousel -->
            <div class="col-lg-6 col-md-6 d-none d-md-block p-0">
                <div class="carousel-section h-100">
                    <div id="loginCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="2"></button>
                        </div>
                        <div class="carousel-inner h-100">
                            <div class="carousel-item active">
                                <img src="./assets/images/image1.jpg" class="carousel-image" alt="Modern Healthcare">
                                <div class="carousel-content">
                                    <h3>Advanced Healthcare Management</h3>
                                    <p>Comprehensive patient care with cutting-edge technology and AI-powered insights.</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="./assets/images/image4.jpg" class="carousel-image" alt="Medical Team">
                                <div class="carousel-content">
                                    <h3>Collaborative Healthcare Teams</h3>
                                    <p>Seamless coordination between doctors, nurses, pharmacists, and laboratory staff.</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="./assets/images/image3.jpg" class="carousel-image" alt="Digital Health">
                                <div class="carousel-content">
                                    <h3>Digital Health Innovation</h3>
                                    <p>Revolutionary healthcare solutions with real-time monitoring and intelligent analytics.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="login-section">
            <div class="logo-container">
                <div class="logo">
                    <img src="./assets/images/logo.jpg" alt="PHCHMS Logo" class="logo-image">
                </div>
                <h1 class="login-title">PHCHMS</h1>
                <p class="login-subtitle">Primary Health Care Hospital Management System</p>
            </div>                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control input-with-icon" id="username" name="username" placeholder="Enter your username" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="department" class="form-label">Department / Role</label>
                            <select class="form-control" id="department" name="department" required>
                                <option value="">Select your department</option>
                                <option value="admin">Administration</option>
                                <option value="records">Records Department</option>
                                <option value="doctor">Doctors/Clinicians</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="lab">Laboratory</option>
                                <option value="eha">Environmental Health</option>
                                <option value="patient">Patient Portal</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-login mt-4">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login to System
                        </button>
                    </form>
                    
                    <!-- <div class="demo-credentials">
                        <h6 class="demo-title">Demo Credentials:</h6>
                        <div class="credential-item">
                            <span><strong>Admin:</strong> admin / password</span>
                            <span class="role-badge">Administrator</span>
                        </div>
                        <div class="credential-item">
                            <span><strong>Records:</strong> records1 / password</span>
                            <span class="role-badge">Records Officer</span>
                        </div>
                        <div class="credential-item">
                            <span><strong>Doctor:</strong> dr_smith / password</span>
                            <span class="role-badge">Medical Doctor</span>
                        </div>
                        <div class="credential-item">
                            <span><strong>Pharmacy:</strong> pharmacy1 / password</span>
                            <span class="role-badge">Pharmacy Staff</span>
                        </div>
                        <div class="credential-item">
                            <span><strong>Laboratory:</strong> lab1 / password</span>
                            <span class="role-badge">Lab Technician</span>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-advance carousel every 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            var loginCarousel = new bootstrap.Carousel(document.getElementById('loginCarousel'), {
                interval: 5000,
                wrap: true
            });
        });
    </script>
</body>
</html>