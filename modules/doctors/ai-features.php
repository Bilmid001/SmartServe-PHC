<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Features - PHCHMS</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            line-height: 2.4 !important;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 125px !important;
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

        .header-left h2 {
            font-size: 28px !important;
            font-weight: 700 !important;
            color: #1a365d !important;
            margin-bottom: 5px !important;
        }

        .welcome-message {
            color: #718096 !important;
            font-size: 16px !important;
        }

        .ai-status {
            display: flex !important;
            align-items: center !important;
            background: #e6fffa !important;
            padding: 8px 16px !important;
            border-radius: 20px !important;
            border: 1px solid #81e6d9 !important;
        }

        .status-indicator {
            width: 10px !important;
            height: 10px !important;
            border-radius: 50% !important;
            background: #38b2ac !important;
            margin-right: 8px !important;
            animation: pulse 2s infinite !important;
        }

        .status-text {
            color: #234e52 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }

        @keyframes pulse {
            0% { opacity: 1 !important; }
            50% { opacity: 0.5 !important; }
            100% { opacity: 1 !important; }
        }

        /* Stats Cards Grid */
        .stats-cards-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 30px !important;
        }

        .stat-card {
            background: white !important;
            border-radius: 12px !important;
            padding: 20px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
            display: flex !important;
            align-items: center !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
            border-left: 4px solid !important;
        }

        .stat-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-card-primary {
            border-left-color: #4299e1 !important;
        }

        .stat-card-success {
            border-left-color: #48bb78 !important;
        }

        .stat-card-info {
            border-left-color: #0bc5ea !important;
        }

        .stat-card-warning {
            border-left-color: #ed8936 !important;
        }

        .stat-icon {
            margin-right: 15px !important;
        }

        .icon-circle {
            width: 50px !important;
            height: 50px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
            background: rgba(66, 153, 225, 0.1) !important;
        }

        .stat-card-success .icon-circle {
            background: rgba(72, 187, 120, 0.1) !important;
        }

        .stat-card-info .icon-circle {
            background: rgba(11, 197, 234, 0.1) !important;
        }

        .stat-card-warning .icon-circle {
            background: rgba(237, 137, 54, 0.1) !important;
        }

        .stat-content h3 {
            font-size: 24px !important;
            font-weight: 700 !important;
            color: #2d3748 !important;
            margin-bottom: 5px !important;
        }

        .stat-content p {
            color: #718096 !important;
            font-size: 14px !important;
            margin-bottom: 5px !important;
        }

        .stat-trend {
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .trend-up {
            color: #48bb78 !important;
        }

        /* Content Grid */
        .content-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* Content Cards */
        .content-card {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            margin-bottom: 20px !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }

        .content-card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 20px !important;
            border-bottom: 1px solid #e1e5eb !important;
        }

        .card-header h3 {
            font-size: 20px !important;
            font-weight: 600 !important;
            color: #2d3748 !important;
        }

        .ai-badge {
            background: #e6fffa !important;
            color: #234e52 !important;
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .ai-feature-content {
            padding: 20px !important;
        }

        .feature-description {
            margin-bottom: 20px !important;
            color: #718096 !important;
            line-height: 1.6 !important;
        }

        /* Symptom Checker Styles */
        .symptom-textarea {
            width: 100% !important;
            min-height: 120px !important;
            padding: 12px !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 8px !important;
            resize: vertical !important;
            font-size: 14px !important;
            transition: border-color 0.3s ease !important;
        }

        .symptom-textarea:focus {
            outline: none !important;
            border-color: #4299e1 !important;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1) !important;
        }

        .symptom-actions {
            display: flex !important;
            gap: 10px !important;
            margin-top: 15px !important;
        }

        .btn-primary, .btn-outline {
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .btn-primary {
            background: #4299e1 !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: #3182ce !important;
        }

        .btn-outline {
            background: transparent !important;
            color: #718096 !important;
            border: 1px solid #e1e5eb !important;
        }

        .btn-outline:hover {
            background: #f7fafc !important;
        }

        .symptom-results {
            margin-top: 20px !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
            animation: fadeIn 0.5s ease !important;
        }

        @keyframes fadeIn {
            from { opacity: 0 !important; }
            to { opacity: 1 !important; }
        }

        .results-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 15px !important;
        }

        .results-header h4 {
            color: #2d3748 !important;
            font-size: 18px !important;
        }

        .confidence-score {
            background: #c6f6d5 !important;
            color: #22543d !important;
            padding: 4px 10px !important;
            border-radius: 12px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .condition-list, .recommendations {
            margin-bottom: 20px !important;
        }

        .condition-list h5, .recommendations h5 {
            color: #4a5568 !important;
            margin-bottom: 10px !important;
            font-size: 16px !important;
        }

        .condition-item {
            display: flex !important;
            justify-content: space-between !important;
            padding: 8px 0 !important;
            border-bottom: 1px solid #e1e5eb !important;
        }

        .condition-name {
            color: #2d3748 !important;
        }

        .probability {
            color: #718096 !important;
            font-weight: 600 !important;
        }

        .recommendations ul {
            list-style-type: none !important;
        }

        .recommendations li {
            padding: 5px 0 !important;
            position: relative !important;
            padding-left: 20px !important;
        }

        .recommendations li:before {
            content: "•" !important;
            color: #4299e1 !important;
            position: absolute !important;
            left: 0 !important;
        }

        /* Clinical Support Tools */
        .tool-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 15px !important;
        }

        @media (max-width: 768px) {
            .tool-grid {
                grid-template-columns: 1fr !important;
            }
        }

        .tool-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 15px !important;
            display: flex !important;
            align-items: center !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
        }

        .tool-card:hover {
            background: #edf2f7 !important;
            border-color: #4299e1 !important;
            transform: translateY(-3px) !important;
        }

        .tool-icon {
            font-size: 24px !important;
            margin-right: 15px !important;
        }

        .tool-content h5 {
            color: #2d3748 !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
        }

        .tool-content p {
            color: #718096 !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
        }

        /* Predictive Analytics */
        .analytics-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 20px !important;
        }

        .analytics-card {
            background: #f7fafc !important;
            border-radius: 8px !important;
            padding: 20px !important;
        }

        .analytics-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 15px !important;
        }

        .analytics-header h4 {
            color: #2d3748 !important;
            font-size: 16px !important;
        }

        .accuracy {
            background: #bee3f8 !important;
            color: #1a365d !important;
            padding: 4px 10px !important;
            border-radius: 12px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        .chart-container-small {
            height: 200px !important;
            margin-bottom: 15px !important;
        }

        .prediction-insight {
            background: white !important;
            padding: 12px !important;
            border-radius: 8px !important;
            border-left: 4px solid #4299e1 !important;
        }

        .prediction-insight p {
            color: #4a5568 !important;
            font-size: 14px !important;
        }

        .outbreak-alerts {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
        }

        .alert-item {
            display: flex !important;
            align-items: center !important;
            background: white !important;
            padding: 12px !important;
            border-radius: 8px !important;
            border-left: 4px solid !important;
        }

        .alert-low {
            border-left-color: #48bb78 !important;
        }

        .alert-medium {
            border-left-color: #ed8936 !important;
        }

        .alert-icon {
            margin-right: 10px !important;
            font-size: 18px !important;
        }

        .alert-content h5 {
            color: #2d3748 !important;
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }

        .alert-content p {
            color: #718096 !important;
            font-size: 13px !important;
        }

        /* AI Assistant Chat */
        .chat-container {
            border: 1px solid #e1e5eb !important;
            border-radius: 8px !important;
            overflow: hidden !important;
        }

        .chat-messages {
            height: 300px !important;
            overflow-y: auto !important;
            padding: 20px !important;
            background: #f7fafc !important;
        }

        .message {
            display: flex !important;
            margin-bottom: 15px !important;
        }

        .user-message {
            justify-content: flex-end !important;
        }

        .user-message .message-content {
            background: #4299e1 !important;
            color: white !important;
        }

        .ai-message .message-content {
            background: white !important;
            color: #2d3748 !important;
            border: 1px solid #e1e5eb !important;
        }

        .message-avatar {
            width: 36px !important;
            height: 36px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-right: 10px !important;
            background: #e1e5eb !important;
            color: #718096 !important;
        }

        .user-message .message-avatar {
            margin-right: 0 !important;
            margin-left: 10px !important;
            background: #4299e1 !important;
            color: white !important;
        }

        .message-content {
            max-width: 70% !important;
            padding: 12px 16px !important;
            border-radius: 18px !important;
            position: relative !important;
        }

        .message-content p {
            margin-bottom: 5px !important;
        }

        .message-time {
            font-size: 11px !important;
            color: #a0aec0 !important;
        }

        .user-message .message-time {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .chat-input {
            padding: 15px !important;
            border-top: 1px solid #e1e5eb !important;
            background: white !important;
        }

        .input-group {
            display: flex !important;
            margin-bottom: 10px !important;
        }

        .form-control {
            flex: 1 !important;
            padding: 12px !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 8px 0 0 8px !important;
            font-size: 14px !important;
        }

        .input-group button {
            border-radius: 0 8px 8px 0 !important;
            padding: 0 15px !important;
        }

        .quick-questions {
            display: flex !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
        }

        .quick-question {
            background: #f7fafc !important;
            border: 1px solid #e1e5eb !important;
            border-radius: 16px !important;
            padding: 6px 12px !important;
            font-size: 12px !important;
            color: #718096 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }

        .quick-question:hover, .quick-question.active {
            background: #4299e1 !important;
            color: white !important;
            border-color: #4299e1 !important;
        }

        /* Model Performance */
        .performance-metrics {
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
        }

        .metric-item {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
        }

        .metric-info {
            display: flex !important;
            justify-content: space-between !important;
        }

        .metric-name {
            color: #4a5568 !important;
            font-size: 14px !important;
        }

        .metric-value {
            color: #2d3748 !important;
            font-weight: 600 !important;
        }

        .metric-bar {
            height: 8px !important;
            background: #e1e5eb !important;
            border-radius: 4px !important;
            overflow: hidden !important;
        }

        .bar-fill {
            height: 100% !important;
            background: #4299e1 !important;
            border-radius: 4px !important;
            transition: width 1s ease !important;
        }

        /* AI Training */
        .training-stats {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
        }

        .training-metric {
            text-align: center !important;
            padding: 15px !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
        }

        .training-metric .metric-value {
            font-size: 24px !important;
            font-weight: 700 !important;
            color: #4299e1 !important;
            margin-bottom: 5px !important;
        }

        .training-metric .metric-label {
            color: #718096 !important;
            font-size: 14px !important;
        }

        .training-progress h5 {
            color: #2d3748 !important;
            margin-bottom: 10px !important;
        }

        .progress-bar-container {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
        }

        .progress-bar {
            flex: 1 !important;
            height: 8px !important;
            background: #e1e5eb !important;
            border-radius: 4px !important;
            overflow: hidden !important;
        }

        .progress-fill {
            height: 100% !important;
            background: #48bb78 !important;
            border-radius: 4px !important;
            transition: width 1s ease !important;
        }

        .progress-text {
            color: #718096 !important;
            font-size: 14px !important;
            white-space: nowrap !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
            
            .content-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px !important;
            }
            
            .stats-cards-grid {
                grid-template-columns: 1fr !important;
            }
            
            .symptom-actions {
                flex-direction: column !important;
            }
            
            .message-content {
                max-width: 85% !important;
            }
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="content-header">
            <div class="header-left">
                <h2>AI-Powered Features</h2>
                <p class="welcome-message">Advanced artificial intelligence for enhanced healthcare delivery</p>
            </div>
            <div class="header-right">
                <div class="ai-status">
                    <span class="status-indicator status-active"></span>
                    <span class="status-text">AI Systems Active</span>
                </div>
            </div>
        </div>

        <!-- AI Overview Cards -->
        <div class="stats-cards-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <div class="icon-circle">
                        🤖
                    </div>
                </div>
                <div class="stat-content">
                    <h3>12</h3>
                    <p>Active AI Models</p>
                    <span class="stat-trend trend-up">+2 this month</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <div class="icon-circle">
                        📊
                    </div>
                </div>
                <div class="stat-content">
                    <h3>98.3%</h3>
                    <p>Prediction Accuracy</p>
                    <span class="stat-trend trend-up">+1.2% improvement</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <div class="icon-circle">
                        ⚡
                    </div>
                </div>
                <div class="stat-content">
                    <h3>2.1M</h3>
                    <p>Data Points Analyzed</p>
                    <span class="stat-trend trend-up">Real-time processing</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <div class="icon-circle">
                        💡
                    </div>
                </div>
                <div class="stat-content">
                    <h3>256</h3>
                    <p>Daily Recommendations</p>
                    <span class="stat-trend trend-up">+15% from last week</span>
                </div>
            </div>
        </div>

        <!-- Main AI Features Grid -->
        <div class="content-grid">
            <!-- AI Symptom Checker -->
            <div class="content-card">
                <div class="card-header">
                    <h3>AI Symptom Checker</h3>
                    <span class="ai-badge">Patient-Facing</span>
                </div>
                <div class="ai-feature-content">
                    <div class="feature-description">
                        <p>Advanced natural language processing to analyze patient symptoms and provide preliminary assessments.</p>
                    </div>
                    <div class="symptom-checker-interface">
                        <div class="input-group">
                            <textarea id="symptomInput" class="symptom-textarea" placeholder="Describe your symptoms in detail... (e.g., headache, fever, cough for 3 days)"></textarea>
                        </div>
                        <div class="symptom-actions">
                            <button class="btn-primary" onclick="analyzeSymptoms()">
                                <i class="fas fa-robot me-2"></i>
                                Analyze Symptoms
                            </button>
                            <button class="btn-outline" onclick="clearSymptoms()">
                                Clear
                            </button>
                        </div>
                        <div id="symptomResults" class="symptom-results" style="display: none;">
                            <div class="results-header">
                                <h4>AI Analysis Results</h4>
                                <span class="confidence-score">92% Confidence</span>
                            </div>
                            <div class="results-content">
                                <div class="condition-list">
                                    <h5>Possible Conditions:</h5>
                                    <div class="condition-item">
                                        <span class="condition-name">Upper Respiratory Infection</span>
                                        <span class="probability">65% match</span>
                                    </div>
                                    <div class="condition-item">
                                        <span class="condition-name">Seasonal Allergies</span>
                                        <span class="probability">25% match</span>
                                    </div>
                                    <div class="condition-item">
                                        <span class="condition-name">Influenza</span>
                                        <span class="probability">10% match</span>
                                    </div>
                                </div>
                                <div class="recommendations">
                                    <h5>Recommendations:</h5>
                                    <ul>
                                        <li>Rest and stay hydrated</li>
                                        <li>Monitor temperature regularly</li>
                                        <li>Consult healthcare provider if symptoms worsen</li>
                                        <li>Consider over-the-counter cold medication</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Decision Support -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Clinical Decision Support</h3>
                    <span class="ai-badge">Doctor-Facing</span>
                </div>
                <div class="ai-feature-content">
                    <div class="feature-description">
                        <p>AI-powered assistance for diagnosis, treatment planning, and evidence-based recommendations.</p>
                    </div>
                    <div class="clinical-support-tools">
                        <div class="tool-grid">
                            <div class="tool-card" onclick="openDrugInteractionChecker()">
                                <div class="tool-icon">💊</div>
                                <div class="tool-content">
                                    <h5>Drug Interaction Checker</h5>
                                    <p>Check for potential medication conflicts and side effects</p>
                                </div>
                            </div>
                            <div class="tool-card" onclick="openTreatmentGuidelines()">
                                <div class="tool-icon">📋</div>
                                <div class="tool-content">
                                    <h5>Treatment Guidelines</h5>
                                    <p>Evidence-based treatment protocols and recommendations</p>
                                </div>
                            </div>
                            <div class="tool-card" onclick="openDifferentialDiagnosis()">
                                <div class="tool-icon">🔍</div>
                                <div class="tool-content">
                                    <h5>Differential Diagnosis</h5>
                                    <p>AI-assisted differential diagnosis based on symptoms</p>
                                </div>
                            </div>
                            <div class="tool-card" onclick="openRiskAssessment()">
                                <div class="tool-icon">⚠️</div>
                                <div class="tool-content">
                                    <h5>Risk Assessment</h5>
                                    <p>Predict patient risks and complications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predictive Analytics -->
        <div class="content-card">
            <div class="card-header">
                <h3>Predictive Analytics Dashboard</h3>
                <span class="ai-badge">Machine Learning</span>
            </div>
            <div class="predictive-analytics">
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-header">
                            <h4>Patient Admission Forecast</h4>
                            <span class="accuracy">94% Accuracy</span>
                        </div>
                        <div class="chart-container-small">
                            <canvas id="admissionChart"></canvas>
                        </div>
                        <div class="prediction-insight">
                            <p><strong>Insight:</strong> Expected 18% increase in admissions next week due to seasonal factors.</p>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-header">
                            <h4>Medication Demand Prediction</h4>
                            <span class="accuracy">89% Accuracy</span>
                        </div>
                        <div class="chart-container-small">
                            <canvas id="medicationChart"></canvas>
                        </div>
                        <div class="prediction-insight">
                            <p><strong>Insight:</strong> High demand predicted for antibiotics and pain relievers.</p>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-header">
                            <h4>Disease Outbreak Detection</h4>
                            <span class="accuracy">96% Accuracy</span>
                        </div>
                        <div class="outbreak-alerts">
                            <div class="alert-item alert-low">
                                <div class="alert-icon">✅</div>
                                <div class="alert-content">
                                    <h5>Normal Activity</h5>
                                    <p>No unusual disease patterns detected</p>
                                </div>
                            </div>
                            <div class="alert-item alert-medium">
                                <div class="alert-icon">⚠️</div>
                                <div class="alert-content">
                                    <h5>Seasonal Alert</h5>
                                    <p>Increased respiratory cases in region</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Assistant Chat -->
        <div class="content-card">
            <div class="card-header">
                <h3>AI Healthcare Assistant</h3>
                <span class="ai-badge">Real-time Support</span>
            </div>
            <div class="ai-assistant">
                <div class="chat-container">
                    <div class="chat-messages" id="chatMessages">
                        <div class="message ai-message">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                <p>Hello! I'm your AI healthcare assistant. How can I help you today? I can assist with medical queries, data analysis, or system guidance.</p>
                                <span class="message-time">Just now</span>
                            </div>
                        </div>
                    </div>
                    <div class="chat-input">
                        <div class="input-group">
                            <input type="text" id="chatInput" placeholder="Ask me anything about healthcare, data, or the system..." class="form-control">
                            <button class="btn-primary" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="quick-questions">
                            <button class="quick-question" onclick="askQuestion('Show patient statistics for this month')">
                                Patient Stats
                            </button>
                            <button class="quick-question" onclick="askQuestion('Recent abnormal lab results')">
                                Lab Alerts
                            </button>
                            <button class="quick-question" onclick="askQuestion('Drug interactions for metformin')">
                                Drug Check
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Model Performance -->
        <div class="content-card">
            <div class="card-header">
                <h3>AI Model Performance</h3>
                <span class="ai-badge">Monitoring</span>
            </div>
            <div class="model-performance">
                <div class="performance-metrics">
                    <div class="metric-item">
                        <div class="metric-info">
                            <span class="metric-name">Symptom Checker Accuracy</span>
                            <span class="metric-value">94.2%</span>
                        </div>
                        <div class="metric-bar">
                            <div class="bar-fill" style="width: 94.2%"></div>
                        </div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-info">
                            <span class="metric-name">Diagnosis Support Accuracy</span>
                            <span class="metric-value">91.8%</span>
                        </div>
                        <div class="metric-bar">
                            <div class="bar-fill" style="width: 91.8%"></div>
                        </div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-info">
                            <span class="metric-name">Drug Interaction Detection</span>
                            <span class="metric-value">98.5%</span>
                        </div>
                        <div class="metric-bar">
                            <div class="bar-fill" style="width: 98.5%"></div>
                        </div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-info">
                            <span class="metric-name">Predictive Analytics Accuracy</span>
                            <span class="metric-value">89.7%</span>
                        </div>
                        <div class="metric-bar">
                            <div class="bar-fill" style="width: 89.7%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Training & Improvement -->
        <div class="content-card">
            <div class="card-header">
                <h3>AI Training & Continuous Learning</h3>
                <span class="ai-badge">Active Learning</span>
            </div>
            <div class="ai-training">
                <div class="training-stats">
                    <div class="training-metric">
                        <div class="metric-value">12,458</div>
                        <div class="metric-label">Medical Cases Analyzed</div>
                    </div>
                    <div class="training-metric">
                        <div class="metric-value">3.2M</div>
                        <div class="metric-label">Data Points Processed</div>
                    </div>
                    <div class="training-metric">
                        <div class="metric-value">87%</div>
                        <div class="metric-label">Model Improvement</div>
                    </div>
                </div>
                <div class="training-progress">
                    <h5>Current Training Progress</h5>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 75%"></div>
                        </div>
                        <span class="progress-text">75% Complete - Advanced Diagnosis Model</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
        // Admission Forecast Chart
        const admissionCtx = document.getElementById('admissionChart').getContext('2d');
        new Chart(admissionCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Predicted Admissions',
                    data: [45, 52, 48, 55, 60, 42, 38],
                    borderColor: '#1a73e8',
                    backgroundColor: 'rgba(26, 115, 232, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Medication Demand Chart
        const medicationCtx = document.getElementById('medicationChart').getContext('2d');
        new Chart(medicationCtx, {
            type: 'bar',
            data: {
                labels: ['Antibiotics', 'Pain Relief', 'Cardiac', 'Diabetes', 'Other'],
                datasets: [{
                    label: 'Demand Prediction',
                    data: [65, 45, 30, 25, 20],
                    backgroundColor: [
                        '#1a73e8', '#28a745', '#ffc107', '#dc3545', '#6f42c1'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });

    // Symptom Checker Functionality
    function analyzeSymptoms() {
        const symptomInput = document.getElementById('symptomInput');
        const resultsDiv = document.getElementById('symptomResults');
        
        if (symptomInput.value.trim() === '') {
            alert('Please describe your symptoms first.');
            return;
        }
        
        // Show loading state
        symptomInput.disabled = true;
        
        // Simulate AI processing
        setTimeout(() => {
            resultsDiv.style.display = 'block';
            symptomInput.disabled = false;
            
            // Scroll to results
            resultsDiv.scrollIntoView({ behavior: 'smooth' });
        }, 2000);
    }

    function clearSymptoms() {
        document.getElementById('symptomInput').value = '';
        document.getElementById('symptomResults').style.display = 'none';
    }

    // Clinical Tools
    function openDrugInteractionChecker() {
        alert('Opening Drug Interaction Checker...');
    }

    function openTreatmentGuidelines() {
        alert('Opening Treatment Guidelines...');
    }

    function openDifferentialDiagnosis() {
        alert('Opening Differential Diagnosis Tool...');
    }

    function openRiskAssessment() {
        alert('Opening Risk Assessment Tool...');
    }

    // AI Assistant Chat
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (message === '') return;
        
        // Add user message
        addMessage(message, 'user');
        input.value = '';
        
        // Simulate AI response
        setTimeout(() => {
            const responses = [
                "Based on your query, I can help analyze that data. Let me process the information...",
                "I understand your question. Here's what I found in our system...",
                "That's an interesting medical query. Let me provide some insights based on current data...",
                "I can assist with that. Let me check the latest medical guidelines and data..."
            ];
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            addMessage(randomResponse, 'ai');
        }, 1000);
    }

    function askQuestion(question) {
        document.getElementById('chatInput').value = question;
        sendMessage();
    }

    function addMessage(text, sender) {
        const messagesDiv = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const avatar = sender === 'user' ? 
            '<div class="message-avatar"><i class="fas fa-user"></i></div>' :
            '<div class="message-avatar"><i class="fas fa-robot"></i></div>';
        
        messageDiv.innerHTML = `
            ${avatar}
            <div class="message-content">
                <p>${text}</p>
                <span class="message-time">Just now</span>
            </div>
        `;
        
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Quick question buttons
    document.querySelectorAll('.quick-question').forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('active');
            setTimeout(() => this.classList.remove('active'), 200);
        });
    });
    </script>

</body>
</html>