<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHCHMS - AI-Powered Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1a73e8;
            --teal: #009688;
            --green: #4caf50;
            --white: #ffffff;
            --navy: #0d47a1;
            --light-gray: #f8f9fa;
            --dark-gray: #1a73e8;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-secondary: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-scrolled {
            background: rgba(255, 255, 255, 0.98);
            padding: 0.5rem 0;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-icon {
            font-size: 2.5rem;
            margin-right: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            color: var(--dark-gray) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-blue) !important;
            transform: translateY(-2px);
        }

        .btn-get-started {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-get-started:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(58, 110, 189, 0.9)), url('https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 100px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-demo {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-demo:hover {
            background: white;
            color: var(--primary-blue);
            transform: translateY(-3px);
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background: var(--light-gray);
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-gray);
        }

        .feature-card p {
            color: #6c757d;
            line-height: 1.6;
        }

        /* Modules Section */
        .modules {
            padding: 100px 0;
            background: white;
        }

        .module-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 2.5rem 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            border-left: 4px solid var(--primary-blue);
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .module-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        /* AI Features */
        .ai-features {
            padding: 100px 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .ai-features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle fill="rgba(255,255,255,0.05)" cx="500" cy="500" r="400"/></svg>');
        }

        .ai-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .ai-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .ai-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        /* Login Section */
        .login-section {
            padding: 100px 0;
            background: var(--light-gray);
        }

        .login-option {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid transparent;
        }

        .login-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-blue);
        }

        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background: var(--gradient-primary);
            color: white;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background: var(--dark-gray);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #495057;
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            color: #adb5bd;
        }

        /* Animations */
        [data-aos] {
            opacity: 0;
            transition: all 0.6s ease;
        }

        [data-aos].aos-animate {
            opacity: 1;
        }

        /* Floating Elements */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        }

        .logo-icon {
        width: 50px;
        height: 50px;
        object-fit: contain;
        }

        .logo-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--navy);
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-get-started, .btn-demo {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>

</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="logo" href="#">
            <!-- Replace the emoji with your actual logo image -->
            <img src="/assets/images.logo.jpg" alt="PHCHMS Logo" class="logo-icon">
            <span class="logo-text">PHCHMS</span>
        </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#modules">Modules</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ai">AI Power</a></li>
                    <li class="nav-item"><a class="nav-link" href="#login">Access</a></li>
                    <!-- <li class="nav-item"><a class="nav-link" href="./login.php">Login</a></li> -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <a href="./login.php" class="btn btn-get-started ms-3">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1>Revolutionizing Healthcare with AI-Powered Management</h1>
                    <p>Transform your hospital operations with our comprehensive, intelligent management system that leverages artificial intelligence for better patient care and operational efficiency.</p>
                    <div class="hero-buttons">
                        <a href="#login" class="btn btn-get-started">Start Free Trial</a>
                        <a href="#features" class="btn btn-demo">
                            <i class="fas fa-play-circle me-2"></i>Watch Demo
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="text-center floating">
                        <img src="./assets/images/image4.jpg" 
                             alt="Healthcare Technology" class="img-fluid rounded-3 shadow-lg" style="max-height: 500px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Why Choose PHCHMS?</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">🤖</div>
                        <h3>AI-Powered Insights</h3>
                        <p>Advanced machine learning algorithms provide real-time analytics and predictive insights for better decision-making.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Enterprise Security</h3>
                        <p>Bank-level security with HIPAA compliance, role-based access control, and end-to-end encryption.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Cross-Platform</h3>
                        <p>Fully responsive design that works seamlessly across all devices - desktop, tablet, and mobile.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Real-Time Updates</h3>
                        <p>Live data synchronization across all departments with instant notifications and alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section class="modules" id="modules">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Comprehensive Modules</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="module-card">
                        <div class="module-icon">👨‍💼</div>
                        <h3>Administration</h3>
                        <p>Complete system management, user controls, and comprehensive analytics dashboard.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="module-card">
                        <div class="module-icon">📋</div>
                        <h3>Medical Records</h3>
                        <p>Digital patient records, treatment authorization, and complete medical history management.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="module-card">
                        <div class="module-icon">👨‍⚕️</div>
                        <h3>Clinical Management</h3>
                        <p>Patient diagnosis, treatment plans, and AI-powered clinical decision support.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="module-card">
                        <div class="module-icon">💊</div>
                        <h3>Pharmacy System</h3>
                        <p>Inventory management, prescription tracking, and automated stock predictions.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="module-card">
                        <div class="module-icon">🔬</div>
                        <h3>Laboratory</h3>
                        <p>Test processing, result analysis, and AI-assisted abnormality detection.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="module-card">
                        <div class="module-icon">🌿</div>
                        <h3>Environmental Health</h3>
                        <p>Community health tracking, inspections, and outbreak prediction systems.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Features Section -->
    <section class="ai-features" id="ai">
        <div class="container">
            <h2 class="section-title text-white" data-aos="fade-up">AI-Powered Intelligence</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="ai-card">
                        <div class="ai-icon">🔍</div>
                        <h3>Smart Diagnosis</h3>
                        <p>AI-powered symptom analysis and differential diagnosis support for clinicians.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="ai-card">
                        <div class="ai-icon">📈</div>
                        <h3>Predictive Analytics</h3>
                        <p>Forecast patient admissions, resource needs, and disease outbreaks with 95% accuracy.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="ai-card">
                        <div class="ai-icon">⚠️</div>
                        <h3>Risk Detection</h3>
                        <p>Automated detection of abnormal patterns and potential health risks in real-time.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="ai-card">
                        <div class="ai-icon">💡</div>
                        <h3>Clinical Support</h3>
                        <p>Evidence-based treatment recommendations and drug interaction alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Section -->
    <section class="login-section" id="login">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Access Your Dashboard</h2>
            <p class="text-center mb-5" data-aos="fade-up">Select your department to access the specialized management portal</p>
            <div class="row g-4">
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="login-option" onclick="openLoginModal('admin')">
                        <div class="login-icon">👨‍💼</div>
                        <h4>Administration</h4>
                        <p class="text-muted">System Management</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="login-option" onclick="openLoginModal('records')">
                        <div class="login-icon">📋</div>
                        <h4>Records</h4>
                        <p class="text-muted">Patient Management</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="login-option" onclick="openLoginModal('doctors')">
                        <div class="login-icon">👨‍⚕️</div>
                        <h4>Medical Staff</h4>
                        <p class="text-muted">Clinical Operations</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="login-option" onclick="openLoginModal('pharmacy')">
                        <div class="login-icon">💊</div>
                        <h4>Pharmacy</h4>
                        <p class="text-muted">Medication Management</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="login-option" onclick="openLoginModal('lab')">
                        <div class="login-icon">🔬</div>
                        <h4>Laboratory</h4>
                        <p class="text-muted">Testing & Analysis</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="login-option" onclick="openLoginModal('eha')">
                        <div class="login-icon">🌿</div>
                        <h4>EHA</h4>
                        <p class="text-muted">Environmental Health</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="700">
                    <div class="login-option" onclick="openLoginModal('patient')">
                        <div class="login-icon">👤</div>
                        <h4>Patient Portal</h4>
                        <p class="text-muted">Self-Service Access</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="stat-item">
                        <div class="stat-number" id="patients-today">0</div>
                        <div class="stat-label">Patients Managed Daily</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number" id="prescriptions">0</div>
                        <div class="stat-label">Prescriptions Processed</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number" id="lab-tests">0</div>
                        <div class="stat-label">Lab Tests Analyzed</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number" id="ai-recommendations">0</div>
                        <div class="stat-label">AI Recommendations</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <h3>PHCHMS</h3>
                        <p>Transforming healthcare delivery through intelligent, AI-powered management solutions that enhance patient care and operational efficiency.</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-section">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#modules">Modules</a></li>
                            <li><a href="#ai">AI Features</a></li>
                            <li><a href="#login">Access</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h3>Contact Info</h3>
                        <p><i class="fas fa-envelope me-2"></i> phchms@gmail.com</p>
                        <p><i class="fas fa-phone me-2"></i> +234 806 9715 695</p>
                        <p><i class="fas fa-map-marker-alt me-2"></i> Keffi PHC, Nasarawa State.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h3>Connect With Us</h3>
                        <div class="social-links">
                            <a href="#" class="text-light me-3"><i class="fab fa-twitter fa-lg"></i></a>
                            <a href="#" class="text-light me-3"><i class="fab fa-facebook fa-lg"></i></a>
                            <a href="#" class="text-light me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                            <!-- <a href="#" class="text-light"><i class="fab fa-github fa-lg"></i></a> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 PHCHMS - Primary Health Care Hospital Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Animated statistics
        function animateValue(id, start, end, duration) {
            const obj = document.getElementById(id);
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                obj.innerHTML = value.toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Initialize statistics when in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateValue('patients-today', 0, 1250, 2000);
                    animateValue('prescriptions', 0, 890, 2000);
                    animateValue('lab-tests', 0, 640, 2000);
                    animateValue('ai-recommendations', 0, 420, 2000);
                }
            });
        });

        observer.observe(document.querySelector('.stats-section'));

        // Login modal function
        function openLoginModal(department) {
            window.location.href = 'login.php?department=' + department;
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>