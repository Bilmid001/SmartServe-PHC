CREATE DATABASE IF NOT EXISTS phchms;
USE phchms;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'records', 'doctor', 'pharmacy', 'lab', 'eha', 'patient') NOT NULL,
    department VARCHAR(50),
    full_name VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    emergency_contact VARCHAR(100),
    blood_type VARCHAR(5),
    allergies TEXT,
    medical_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    doctor_id INT,
    appointment_date DATETIME,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    reason TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Medical records table
CREATE TABLE medical_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    doctor_id INT,
    visit_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    symptoms TEXT,
    diagnosis TEXT,
    treatment TEXT,
    prescription TEXT,
    notes TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Laboratory tests table
CREATE TABLE lab_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    test_type VARCHAR(100),
    test_name VARCHAR(100),
    ordered_by INT,
    status ENUM('pending', 'in-progress', 'completed') DEFAULT 'pending',
    results TEXT,
    normal_range TEXT,
    flag ENUM('normal', 'abnormal'),
    uploaded_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (ordered_by) REFERENCES users(id)
);

-- Pharmacy inventory table
CREATE TABLE pharmacy_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    drug_name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100),
    category VARCHAR(50),
    quantity INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    unit_price DECIMAL(10,2),
    expiry_date DATE,
    supplier VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- EHA reports table
CREATE TABLE eha_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(50),
    location VARCHAR(100),
    inspector_id INT,
    inspection_date DATE,
    findings TEXT,
    recommendations TEXT,
    risk_level ENUM('low', 'medium', 'high'),
    status ENUM('open', 'in-progress', 'resolved'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspector_id) REFERENCES users(id)
);

-- AI recommendations table
CREATE TABLE ai_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    recommendation_type VARCHAR(50),
    description TEXT,
    confidence_level DECIMAL(5,2),
    status ENUM('pending', 'reviewed', 'implemented'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Insert sample data
INSERT INTO users (username, password, email, role, department, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@phchms.com', 'admin', 'Administration', 'System Administrator'),
('records1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'records@phchms.com', 'records', 'Records', 'Records Officer'),
('dr_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr.smith@phchms.com', 'doctor', 'Cardiology', 'Dr. John Smith'),
('pharmacy1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacy@phchms.com', 'pharmacy', 'Pharmacy', 'Pharmacy Manager');