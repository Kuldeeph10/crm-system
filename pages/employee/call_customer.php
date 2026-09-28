<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$customer_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$followup_id = isset($_GET['followup_id']) ? (int) $_GET['followup_id'] : 0;
$customer = getUserById($customer_id);

// Verify customer is assigned to this employee
if (!$customer || $customer['assigned_to'] != $_SESSION['user_id']) {
    header('Location: my_customers.php');
    exit();
}

$page_title = 'Call Customer - ' . htmlspecialchars($customer['name']);
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $notes = trim($_POST['notes'] ?? '');
    $interest_level = $_POST['interest_level'] ?? 'follow_up';
    $follow_up_date = $_POST['follow_up_date'] ?? null;
    $recording_file = null;

    // Validate
    if (empty($notes)) {
        $error = 'Please enter call notes';
    } else {
        // Handle file upload
        if (isset($_FILES['recording']) && $_FILES['recording']['error'] == 0) {
            $allowed = ['mp3', 'wav', 'm4a', 'ogg'];
            $filename = $_FILES['recording']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $upload_dir = '../../uploads/recordings/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_filename = 'call_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['recording']['tmp_name'], $upload_path)) {
                    $recording_file = $new_filename;
                }
            }
        }

        // Save call record
        $result = addCallRecord($customer_id, $_SESSION['user_id'], $notes, $interest_level, $follow_up_date, $recording_file, $followup_id);

        if ($result) {
            $success = true;
        } else {
            $error = 'Failed to save call record';
        }
    }
}

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_employee.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-phone"></i> Call Customer</h1>
            <div class="header-actions">
                <?php if ($followup_id > 0): ?>
                    <a href="follow_ups.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Follow-ups
                    </a>
                <?php else: ?>
                    <a href="my_customers.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to My Customers
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Call recorded successfully!
            </div>
            <?php if ($followup_id > 0): ?>
                <script>
                    setTimeout(function () {
                        window.location.href = 'follow_ups.php';
                    }, 2000);
                </script>
            <?php else: ?>
                <script>
                    setTimeout(function () {
                        window.location.href = 'my_customers.php';
                    }, 2000);
                </script>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Customer Info Card -->
        <div class="customer-info-card">
            <h3><i class="fas fa-user-circle"></i> Customer Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Name:</label>
                    <span><?php echo htmlspecialchars($customer['name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($customer['email']) ?: 'N/A'; ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($customer['phone']) ?: 'N/A'; ?></span>
                </div>
                <div class="info-item">
                    <label>Company:</label>
                    <span><?php echo htmlspecialchars($customer['company']) ?: 'N/A'; ?></span>
                </div>
            </div>
        </div>

        <!-- Call Form -->
        <div class="form-card">
            <h3><i class="fas fa-clipboard-list"></i> Call Details</h3>
            <form method="POST" action="" enctype="multipart/form-data" id="callForm">
                <div class="form-group">
                    <label for="notes">Call Notes <span class="required">*</span></label>
                    <textarea id="notes" name="notes" class="form-control" rows="5"
                        placeholder="Enter detailed notes about the call..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="interest_level">Interest Level</label>
                        <select id="interest_level" name="interest_level" class="form-control">
                            <option value="interested">✅ Interested - Positive response</option>
                            <option value="not_interested">❌ Not Interested - Rejected</option>
                            <option value="follow_up" selected>🔄 Follow Up - Call again later</option>
                        </select>
                    </div>

                    <div class="form-group" id="followUpGroup">
                        <label for="follow_up_date">Follow-up Date</label>
                        <input type="date" id="follow_up_date" name="follow_up_date" class="form-control">
                        <small>Required if interest level is "Follow Up"</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="recording">Call Recording (Optional)</label>
                    <input type="file" id="recording" name="recording" class="form-control"
                        accept=".mp3,.wav,.m4a,.ogg">
                    <small>Supported formats: MP3, WAV, M4A, OGG (Max 10MB)</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Call Record
                    </button>
                    <?php if ($followup_id > 0): ?>
                        <a href="follow_ups.php" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <a href="my_customers.php" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .customer-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
    }

    .customer-info-card h3 {
        margin: 0 0 15px 0;
        font-size: 18px;
    }

    .customer-info-card h3 i {
        margin-right: 10px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-item label {
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 5px;
    }

    .info-item span {
        font-size: 16px;
        font-weight: 500;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .form-card h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
        color: #333;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .required {
        color: #e74c3c;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    select.form-control {
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }

    .btn-primary {
        background: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: #2980b9;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-secondary:hover {
        background: #7f8c8d;
    }

    .btn-danger {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-danger:hover {
        background: #c0392b;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Show/hide follow-up date based on interest level
    document.getElementById('interest_level').addEventListener('change', function () {
        const followUpGroup = document.getElementById('followUpGroup');
        if (this.value === 'follow_up') {
            followUpGroup.style.display = 'block';
        } else {
            followUpGroup.style.display = 'none';
            document.getElementById('follow_up_date').value = '';
        }
    });

    // Form validation
    document.getElementById('callForm').addEventListener('submit', function (e) {
        const notes = document.getElementById('notes').value.trim();
        const interestLevel = document.getElementById('interest_level').value;
        const followUpDate = document.getElementById('follow_up_date').value;

        if (notes === '') {
            e.preventDefault();
            alert('Please enter call notes');
            return false;
        }

        if (interestLevel === 'follow_up' && followUpDate === '') {
            e.preventDefault();
            alert('Please select a follow-up date');
            return false;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    });
</script>

<?php include_once '../../components/footer.php'; ?>