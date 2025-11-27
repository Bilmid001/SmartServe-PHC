<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('lab') && !hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle file upload
if ($_POST['action'] ?? '' === 'upload_file') {
    $test_id = $_POST['test_id'];
    $file_type = $_POST['file_type'];
    $description = $_POST['description'];
    
    // File upload handling
    if ($_FILES['lab_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/lab_reports/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['lab_file']['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['lab_file']['tmp_name'], $file_path)) {
            // Update database with file info
            $query = "UPDATE lab_tests SET uploaded_file = :file_path WHERE id = :test_id";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([':file_path' => $file_path, ':test_id' => $test_id])) {
                $success = "File uploaded successfully!";
            } else {
                $error = "Error updating database!";
            }
        } else {
            $error = "Error uploading file!";
        }
    } else {
        $error = "File upload error: " . $_FILES['lab_file']['error'];
    }
}

// Get tests that need file uploads
$query = "SELECT lt.*, p.full_name, p.patient_id, u.full_name as doctor_name 
          FROM lab_tests lt 
          JOIN patients p ON lt.patient_id = p.id 
          JOIN users u ON lt.ordered_by = u.id 
          WHERE lt.status = 'completed' 
          AND (lt.uploaded_file IS NULL OR lt.uploaded_file = '')
          ORDER BY lt.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent uploads
$query = "SELECT lt.*, p.full_name, p.patient_id, u.full_name as doctor_name 
          FROM lab_tests lt 
          JOIN patients p ON lt.patient_id = p.id 
          JOIN users u ON lt.ordered_by = u.id 
          WHERE lt.uploaded_file IS NOT NULL 
          AND lt.uploaded_file != ''
          ORDER BY lt.updated_at DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Uploads - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h2>Laboratory File Uploads</h2>
                <div class="breadcrumb">
                    <span>Laboratory</span> / <span>File Uploads</span>
                </div>
            </div>

            <!-- Upload Statistics -->
            <div class="stats-cards-grid">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📤
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($pending_uploads); ?></h3>
                        <p>Pending Uploads</p>
                        <span class="stat-trend trend-up">Needs attention</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            ✅
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($recent_uploads); ?></h3>
                        <p>Recent Uploads</p>
                        <span class="stat-trend trend-up">Today</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            💾
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>2.3GB</h3>
                        <p>Storage Used</p>
                        <span class="stat-trend trend-up">+150MB this week</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <div class="icon-circle">
                            📊
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>98%</h3>
                        <p>Upload Success Rate</p>
                        <span class="stat-trend trend-up">Excellent</span>
                    </div>
                </div>
            </div>

            <!-- File Upload Form -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Upload Laboratory Report</h3>
                    <span class="ai-badge">Secure Upload</span>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="action" value="upload_file">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="test_id">Select Test</label>
                            <select id="test_id" name="test_id" required onchange="loadTestDetails(this.value)">
                                <option value="">Choose a test...</option>
                                <?php foreach ($pending_uploads as $test): ?>
                                <option value="<?php echo $test['id']; ?>">
                                    <?php echo $test['test_name']; ?> - <?php echo $test['full_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file_type">File Type</label>
                            <select id="file_type" name="file_type" required>
                                <option value="pdf">PDF Report</option>
                                <option value="image">Image (JPG/PNG)</option>
                                <option value="document">Document (DOC/DOCX)</option>
                                <option value="spreadsheet">Spreadsheet (XLS/XLSX)</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="testDetails" class="test-details" style="display: none;">
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Patient:</span>
                                <span class="detail-value" id="detailPatient">-</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Test Type:</span>
                                <span class="detail-value" id="detailTestType">-</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ordered By:</span>
                                <span class="detail-value" id="detailDoctor">-</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value" id="detailStatus">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">File Description</label>
                        <input type="text" id="description" name="description" placeholder="Brief description of the file contents...">
                    </div>
                    
                    <div class="form-group">
                        <label for="lab_file">Select File</label>
                        <div class="file-upload-area" id="uploadArea">
                            <div class="upload-placeholder">
                                <span class="upload-icon">📁</span>
                                <h4>Drag & Drop Files Here</h4>
                                <p>or click to browse</p>
                                <span class="upload-note">Max file size: 10MB</span>
                            </div>
                            <input type="file" id="lab_file" name="lab_file" required style="display: none;" onchange="handleFileSelect(this)">
                        </div>
                        <div id="filePreview" class="file-preview" style="display: none;"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-icon">📤</span>
                            Upload File
                        </button>
                        <button type="button" class="btn-secondary" onclick="clearUploadForm()">
                            <span class="btn-icon">🔄</span>
                            Clear Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Pending Uploads -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Pending File Uploads</h3>
                    <span class="badge"><?php echo count($pending_uploads); ?> tests</span>
                </div>
                <div class="pending-uploads">
                    <?php if (count($pending_uploads) > 0): ?>
                        <div class="uploads-table">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Test ID</th>
                                        <th>Patient</th>
                                        <th>Test Name</th>
                                        <th>Ordered By</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_uploads as $test): ?>
                                    <tr>
                                        <td><strong>#<?php echo $test['id']; ?></strong></td>
                                        <td>
                                            <strong><?php echo $test['full_name']; ?></strong>
                                            <br>
                                            <span class="text-muted"><?php echo $test['patient_id']; ?></span>
                                        </td>
                                        <td><?php echo $test['test_name']; ?></td>
                                        <td>Dr. <?php echo $test['doctor_name']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($test['created_at'])); ?></td>
                                        <td>
                                            <button class="btn-action btn-primary" onclick="quickUpload(<?php echo $test['id']; ?>)">
                                                Upload Now
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-pending">
                            <div class="no-pending-icon">✅</div>
                            <h4>All Caught Up!</h4>
                            <p>No pending file uploads. All tests have been processed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recently Uploaded Files</h3>
                    <a href="upload-history.php" class="view-all">View All</a>
                </div>
                <div class="recent-uploads">
                    <?php if (count($recent_uploads) > 0): ?>
                        <div class="uploads-grid">
                            <?php foreach ($recent_uploads as $upload): ?>
                            <div class="upload-item">
                                <div class="file-icon">📄</div>
                                <div class="file-info">
                                    <h4><?php echo $upload['test_name']; ?></h4>
                                    <p class="file-patient"><?php echo $upload['full_name']; ?></p>
                                    <p class="file-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($upload['updated_at'])); ?></p>
                                </div>
                                <div class="file-actions">
                                    <button class="btn-action btn-outline" onclick="downloadFile(<?php echo $upload['id']; ?>)">
                                        Download
                                    </button>
                                    <button class="btn-action btn-primary" onclick="viewFile(<?php echo $upload['id']; ?>)">
                                        View
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-recent">
                            <p>No recent file uploads.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bulk Upload Tools -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Bulk Upload Tools</h3>
                    <span class="ai-badge">Batch Processing</span>
                </div>
                <div class="bulk-tools">
                    <div class="tool-card">
                        <div class="tool-icon">📦</div>
                        <div class="tool-content">
                            <h4>Batch Upload</h4>
                            <p>Upload multiple files at once for different tests</p>
                            <button class="btn-tool" onclick="openBatchUpload()">Start Batch Upload</button>
                        </div>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">🔄</div>
                        <div class="tool-content">
                            <h4>Auto-Processing</h4>
                            <p>Set up automatic file processing rules</p>
                            <button class="btn-tool" onclick="configureAutoProcess()">Configure</button>
                        </div>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">🔍</div>
                        <div class="tool-content">
                            <h4>File Validation</h4>
                            <p>Validate uploaded files for quality and format</p>
                            <button class="btn-tool" onclick="runValidation()">Validate Files</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function loadTestDetails(testId) {
        if (!testId) {
            document.getElementById('testDetails').style.display = 'none';
            return;
        }
        
        // In real implementation, this would fetch test details via AJAX
        const testDetails = {
            patient: 'John Smith',
            testType: 'Blood Work',
            doctor: 'Dr. Sarah Johnson',
            status: 'Completed'
        };
        
        document.getElementById('detailPatient').textContent = testDetails.patient;
        document.getElementById('detailTestType').textContent = testDetails.testType;
        document.getElementById('detailDoctor').textContent = testDetails.doctor;
        document.getElementById('detailStatus').textContent = testDetails.status;
        document.getElementById('testDetails').style.display = 'block';
    }

    function handleFileSelect(input) {
        const file = input.files[0];
        if (file) {
            const preview = document.getElementById('filePreview');
            const uploadArea = document.getElementById('uploadArea');
            
            preview.innerHTML = `
                <div class="file-info">
                    <span class="file-icon">📄</span>
                    <div class="file-details">
                        <h5>${file.name}</h5>
                        <p>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        <p>Type: ${file.type || 'Unknown'}</p>
                    </div>
                    <button type="button" class="btn-remove" onclick="removeFile()">×</button>
                </div>
            `;
            preview.style.display = 'block';
            uploadArea.style.display = 'none';
        }
    }

    function removeFile() {
        document.getElementById('lab_file').value = '';
        document.getElementById('filePreview').style.display = 'none';
        document.getElementById('uploadArea').style.display = 'block';
    }

    function clearUploadForm() {
        document.querySelector('.upload-form').reset();
        removeFile();
        document.getElementById('testDetails').style.display = 'none';
    }

    function quickUpload(testId) {
        document.getElementById('test_id').value = testId;
        loadTestDetails(testId);
        document.querySelector('.upload-form').scrollIntoView({ behavior: 'smooth' });
    }

    function downloadFile(fileId) {
        alert('Downloading file ' + fileId);
    }

    function viewFile(fileId) {
        alert('Viewing file ' + fileId);
    }

    function openBatchUpload() {
        alert('Opening batch upload interface...');
    }

    function configureAutoProcess() {
        alert('Opening auto-processing configuration...');
    }

    function runValidation() {
        alert('Running file validation...');
    }

    // Drag and drop functionality
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('lab_file');

    uploadArea.addEventListener('click', () => fileInput.click());
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.background = '#f0f8ff';
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.background = '';
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.background = '';
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(fileInput);
    });
    </script>

    <style>
    .upload-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .test-details {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #16a085;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem;
        background: white;
        border-radius: 6px;
    }

    .detail-label {
        font-weight: 600;
        color: #2c3e50;
    }

    .detail-value {
        color: #16a085;
        font-weight: 500;
    }

    .file-upload-area {
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-area:hover {
        border-color: #16a085;
        background: #f0f8ff;
    }

    .upload-placeholder .upload-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.7;
    }

    .upload-note {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    .file-preview {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .file-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .file-details h5 {
        margin: 0 0 0.3rem 0;
        color: #2c3e50;
    }

    .file-details p {
        margin: 0.2rem 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .btn-remove {
        background: #e74c3c;
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        margin-left: auto;
    }

    .pending-uploads, .recent-uploads {
        margin-top: 1rem;
    }

    .uploads-table {
        overflow-x: auto;
    }

    .uploads-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }

    .upload-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #16a085;
    }

    .file-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .file-patient, .file-date {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0.2rem 0;
    }

    .file-actions {
        margin-left: auto;
        display: flex;
        gap: 0.5rem;
    }

    .bulk-tools {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .tool-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        text-align: center;
        border-left: 4px solid #16a085;
    }

    .tool-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .tool-content h4 {
        margin: 0 0 0.8rem 0;
        color: #2c3e50;
    }

    .tool-content p {
        margin: 0 0 1.5rem 0;
        color: #7f8c8d;
        line-height: 1.5;
    }

    .btn-tool {
        background: #16a085;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-tool:hover {
        background: #138d75;
        transform: translateY(-2px);
    }

    .no-pending, .no-recent {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
    }

    .no-pending-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .details-grid {
            grid-template-columns: 1fr;
        }

        .uploads-grid {
            grid-template-columns: 1fr;
        }

        .upload-item {
            flex-direction: column;
            text-align: center;
        }

        .file-actions {
            margin-left: 0;
            justify-content: center;
        }

        .bulk-tools {
            grid-template-columns: 1fr;
        }

        .file-info {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>
</body>
</html>