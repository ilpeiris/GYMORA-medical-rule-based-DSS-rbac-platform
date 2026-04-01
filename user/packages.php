<?php
// /Gymora/user/packages.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_USER);

$success = '';
$error = '';

// Handle Package Purchase POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $package_id = $_POST['package_id'];
    
    // Fetch package details to calculate expiry and consultations
    $pkgStmt = $pdo->prepare("SELECT duration_months, consultation_count FROM packages WHERE id = ? AND is_active = 1");
    $pkgStmt->execute([$package_id]);
    $package = $pkgStmt->fetch();
    
    if ($package) {
        // Calculate new expiry date
        $duration = $package['duration_months'];
        $expiry_date = date('Y-m-d', strtotime("+$duration months"));
        
        // Update user record
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET package_id = ?, package_expiry = ?, consultations_remaining = ? 
            WHERE id = ?
        ");
        
        try {
            $updateStmt->execute([$package_id, $expiry_date, $package['consultation_count'], $_SESSION['user_id']]);
            $success = "Package purchased successfully! You can now book an appointment.";
        } catch (PDOException $e) {
            $error = "Failed to purchase package. Please try again.";
        }
    }
}

// Fetch all active packages
$stmt = $pdo->query("SELECT * FROM packages WHERE is_active = 1");
$packages = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12 text-center">
        <h2 class="fw-bold">Membership Packages</h2>
        <p class="text-muted">Select a plan to access the gym and book medical consultations.</p>
        <hr>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row justify-content-center mt-4">
    <?php foreach ($packages as $pkg): ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow text-center h-100 border-primary">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><?= htmlspecialchars($pkg['name']) ?></h4>
                </div>
                <div class="card-body d-flex flex-column">
                    <h2 class="card-title my-3">£<?= htmlspecialchars($pkg['price_gbp']) ?><small class="text-muted fs-6">/<?= $pkg['duration_months'] ?> mo</small></h2>
                    <p class="text-dark fw-bold border-bottom pb-2">Includes <?= $pkg['consultation_count'] ?> Doctor Consultation(s)</p>
                    
                    <ul class="list-unstyled mb-4">
                        <?php 
                        $features = json_decode($pkg['features'], true);
                        if ($features) {
                            foreach ($features as $feature) {
                                echo "<li class='mb-2'>✔️ " . htmlspecialchars($feature) . "</li>";
                            }
                        }
                        ?>
                    </ul>
                    
                    <form method="POST" action="" class="mt-auto">
                        <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                        <button type="submit" class="btn btn-outline-primary w-100 fw-bold">Select This Plan</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once '../includes/footer.php'; ?>