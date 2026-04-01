<?php
// /Gymora/user/dashboard.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

// SECURITY GATEWAY: Only allow 'user' roles to view this page
requireRole(ROLE_USER); 

// Fetch the user's details and their active package name
$stmt = $pdo->prepare("
    SELECT u.*, p.name as package_name 
    FROM users u 
    LEFT JOIN packages p ON u.package_id = p.id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();


require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <h2 class="fw-bold">Welcome back, <?= htmlspecialchars($user['name']) ?>!</h2>
        <hr>


<?php if (isset($_GET['msg']) && $_GET['msg'] === 'class_booked'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong><i class="bi bi-check-circle"></i> Success!</strong> Your class has been successfully booked. Our DSS engine verified it is medically safe for you to attend.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>




    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Membership</h5>
            </div>
            <div class="card-body">
                <?php if ($user['package_id']): ?>
                    <p class="mb-2"><strong>Active Package:</strong> <?= htmlspecialchars($user['package_name']) ?></p>
                    <p class="mb-2"><strong>Expires On:</strong> <?= date('F j, Y', strtotime($user['package_expiry'])) ?></p>
                    <p class="mb-0"><strong>Doctor Consultations Remaining:</strong> 
                        <span class="badge bg-success fs-6 ms-2"><?= $user['consultations_remaining'] ?></span>
                    </p>
                <?php else: ?>
                    <div class="text-center py-3">
                        <p class="text-muted mb-3">You do not have an active membership package.</p>
                        <a href="packages.php" class="btn btn-primary w-100">Browse Packages</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-dark">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body text-center py-4">
                <p class="text-muted mb-4">Book your next consultation or view your current workout plan.</p>
                <div class="d-grid gap-2">
                    <a href="appointments.php" class="btn btn-outline-dark">Book an Appointment</a>
                    <a href="workout_plan.php" class="btn btn-outline-primary">View Workout Plan</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>