<?php
// /Gymora/user/profile.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_USER);

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch latest medical assessment for BMI/Weight data
$medStmt = $pdo->prepare("
    SELECT weight_kg, height_cm, bmi, created_at 
    FROM medical_assessments 
    WHERE user_id = ? AND status = 'submitted' 
    ORDER BY created_at DESC LIMIT 1
");
$medStmt->execute([$_SESSION['user_id']]);
$latest_medical = $medStmt->fetch();

require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <h2 class="fw-bold">My Profile</h2>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Account Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Full Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email Address:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                <p><strong>Account Status:</strong> 
                    <?php if ($user['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Deactivated</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-info">
            <div class="card-header bg-info text-dark">
                <h5 class="mb-0">Latest Health Metrics</h5>
            </div>
            <div class="card-body text-center py-4">
                <?php if ($latest_medical): ?>
                    <div class="row">
                        <div class="col-4">
                            <h3 class="text-primary"><?= htmlspecialchars($latest_medical['weight_kg']) ?> kg</h3>
                            <small class="text-muted">Weight</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-primary"><?= htmlspecialchars($latest_medical['height_cm']) ?> cm</h3>
                            <small class="text-muted">Height</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-primary"><?= htmlspecialchars($latest_medical['bmi']) ?></h3>
                            <small class="text-muted">BMI</small>
                        </div>
                    </div>
                    <p class="mt-4 mb-0 text-muted small">Recorded on: <?= date('F j, Y', strtotime($latest_medical['created_at'])) ?></p>
                <?php else: ?>
                    <p class="text-muted mt-3">No medical assessment data available yet.</p>
                    <p class="small">Book a consultation with a doctor to get your baseline metrics.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>