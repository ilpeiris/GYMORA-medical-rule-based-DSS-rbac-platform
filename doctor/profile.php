<?php
// /Gymora/doctor/profile.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_DOCTOR);
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// --- FORM HANDLING: UPDATE PROFILE & TITLE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $title = trim($_POST['title']);
    
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->execute([$email, $user_id]);
    
    if ($checkStmt->rowCount() > 0) {
        $error = "That email address is already in use.";
    } else {
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, title = ? WHERE id = ?");
        $updateStmt->execute([$name, $email, $title, $user_id]);
        $_SESSION['name'] = $name;
        $success = "Doctor profile updated successfully!";
    }
}

// --- FORM HANDLING: CHANGE PASSWORD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    $userStmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $userStmt->execute([$user_id]);
    $userRow = $userStmt->fetch();
    
    if (!password_verify($current_pass, $userRow['password_hash'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $passStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $passStmt->execute([$new_hash, $user_id]);
        $success = "Security credentials updated!";
    }
}

// 1. Fetch current doctor data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Fetch Doctor's Clinical Stats
$statStmt = $pdo->prepare("SELECT COUNT(*) as total_assessments FROM medical_assessments WHERE doctor_id = ? AND status = 'submitted'");
$statStmt->execute([$user_id]);
$stats = $statStmt->fetch();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="bi bi-person-badge"></i> Doctor Profile</h2>
            <hr>
        </div>
    </div>

    <?php if ($success): ?><div class="alert alert-success shadow-sm"><i class="bi bi-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 bg-white mb-4">
                <div class="card-body text-center py-5">
                    <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h4 class="fw-bold mb-1">Dr. <?= htmlspecialchars($user['name']) ?></h4>
                    <p class="text-danger fw-bold small text-uppercase mb-2"><?= htmlspecialchars($user['title'] ?? 'Clinical Physician') ?></p>
                    <p class="text-muted small"><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-danger bg-white">
                <div class="card-header bg-danger text-white fw-bold"><i class="bi bi-activity"></i> Clinical Impact</div>
                <div class="card-body text-center py-4">
                    <h1 class="display-4 fw-bold text-danger mb-0"><?= $stats['total_assessments'] ?></h1>
                    <p class="text-muted text-uppercase small tracking-wide fw-bold">Patients Assessed</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-dark mb-4">
                <div class="card-header bg-dark text-white fw-bold">Professional Details</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Professional Title (Publicly Visible)</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($user['title'] ?? '') ?>" placeholder="e.g. Chief Medical Officer, Physiotherapist">
                            </div>
                        </div>
                        <div class="text-end"><button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button></div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-secondary">
                <div class="card-header bg-secondary text-white fw-bold"><i class="bi bi-shield-lock"></i> Security</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3"><label class="form-label fw-bold">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">New Password</label><input type="password" name="new_password" class="form-control" minlength="6" required></div>
                            <div class="col-md-6 mb-4"><label class="form-label fw-bold">Confirm</label><input type="password" name="confirm_password" class="form-control" minlength="6" required></div>
                        </div>
                        <div class="text-end"><button type="submit" class="btn btn-dark fw-bold px-4">Update Password</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>