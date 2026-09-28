<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Bulk Import Customers';

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-file-import"></i> Bulk Import Customers</h1>
            <div class="header-actions">
                <a href="customers.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
            </div>
        </div>
        
        <!-- Step 1: Download Template -->
        <div class="step-card">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3>Download Template</h3>
                <p>Download the Excel template to get the correct format</p>
                <a href="<?php echo BASE_URL; ?>api/import_export.php?action=template" class="btn btn-success" id="downloadTemplate">
                    <i class="fas fa-download"></i> Download Excel Template
                </a>
            </div>
        </div>
        
        <!-- Step 2: Upload File -->
        <div class="step-card">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3>Upload Excel File</h3>
                <p>Upload your filled Excel file (.xlsx or .xls)</p>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & drop or click to select file</p>
                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required>
                        <small>Supported formats: .xlsx, .xls (Max 10MB)</small>
                    </div>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="fas fa-upload"></i> Import Customers
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Step 3: Results -->
        <div class="step-card" id="resultsCard" style="display: none;">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3>Import Results</h3>
                <div id="importResults"></div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="info-card">
            <h4><i class="fas fa-info-circle"></i> Instructions</h4>
            <ul>
                <li><strong>Name column</strong> is required (marked with *)</li>
                <li>Date format must be <strong>YYYY-MM-DD</strong> (e.g., 2026-06-04)</li>
                <li>If date is empty, today's date will be used automatically</li>
                <li>Maximum file size: 10MB</li>
                <li>You can import up to 1000 customers at once</li>
                <li>Email and phone numbers are optional but recommended</li>
            </ul>
        </div>
    </div>
</div>

<style>
.step-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.step-number {
    width: 45px;
    height: 45px;
    background: #3498db;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
}

.step-content p {
    margin: 0 0 15px 0;
    color: #666;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.upload-area:hover {
    border-color: #3498db;
    background: #f8f9fa;
}

.upload-area i {
    font-size: 48px;
    color: #3498db;
    margin-bottom: 10px;
}

.upload-area p {
    margin: 10px 0;
}

.upload-area input {
    display: none;
}

.upload-area.drag-over {
    border-color: #27ae60;
    background: #e8f5e9;
}

.info-card {
    background: #e8f4fd;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #3498db;
}

.info-card h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #2c3e50;
}

.info-card ul {
    margin: 0;
    padding-left: 20px;
}

.info-card li {
    margin: 8px 0;
    color: #555;
}

.result-success {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.result-error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.errors-list {
    max-height: 200px;
    overflow-y: auto;
    font-size: 12px;
    margin-top: 10px;
    padding-left: 20px;
}

.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

@media (max-width: 768px) {
    .step-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .upload-area {
        padding: 20px;
    }
}
</style>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';

// Drag & drop functionality
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('excel_file');

uploadArea.addEventListener('click', () => fileInput.click());
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
});
uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('drag-over');
});
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    fileInput.files = e.dataTransfer.files;
    updateFileName();
});

fileInput.addEventListener('change', updateFileName);

function updateFileName() {
    if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        uploadArea.innerHTML = `
            <i class="fas fa-file-excel" style="color: #27ae60;"></i>
            <p><strong>${fileName}</strong></p>
            <small>Click or drag to change file</small>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls">
        `;
        document.getElementById('excel_file').addEventListener('change', updateFileName);
    }
}

// Import form submission
document.getElementById('importForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const file = fileInput.files[0];
    if (!file) {
        alert('Please select an Excel file');
        return;
    }
    
    const formData = new FormData();
    formData.append('excel_file', file);
    
    const importBtn = document.getElementById('importBtn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    
    try {
        const response = await fetch(BASE_URL + 'api/import_export.php?action=import', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const resultsCard = document.getElementById('resultsCard');
        const resultsDiv = document.getElementById('importResults');
        
        if (data.success) {
            resultsDiv.innerHTML = `
                <div class="result-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>✅ Import Completed!</strong><br><br>
                    📊 Successfully imported: <strong>${data.data.success_count}</strong> customers<br>
                    ❌ Failed: <strong>${data.data.error_count}</strong> customers
                </div>
                ${data.data.errors.length > 0 ? `
                    <div class="result-error">
                        <strong>⚠️ Errors Details:</strong>
                        <div class="errors-list">
                            ${data.data.errors.map(err => `• ${err}`).join('<br>')}
                        </div>
                    </div>
                ` : ''}
                <div class="btn-group">
                    <a href="customers.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> View All Customers
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Import More
                    </button>
                </div>
            `;
        } else {
            resultsDiv.innerHTML = `
                <div class="result-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>❌ Import Failed!</strong><br><br>
                    ${data.message}
                </div>
                <button onclick="location.reload()" class="btn btn-secondary mt-3">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            `;
        }
        
        resultsCard.style.display = 'flex';
        resultsCard.scrollIntoView({ behavior: 'smooth' });
        
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Customers';
    }
});
</script>

<?php include_once '../../components/footer.php'; ?>