<?php
// /Gymora/team.php
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'config/constants.php';

// Fetch all active Doctors and Trainers from the database
$stmt = $pdo->query("
    SELECT name, email, role 
    FROM users 
    WHERE role IN ('doctor', 'trainer') AND is_active = 1 
    ORDER BY role ASC, name ASC
");
$team_members = $stmt->fetchAll();

// Group them by role for display
$doctors = array_filter($team_members, fn($m) => $m['role'] === 'doctor');
$trainers = array_filter($team_members, fn($m) => $m['role'] === 'trainer');

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <span class="text-success fw-bold text-uppercase tracking-wide">Our Experts</span>
            <h1 class="display-4 fw-bold text-dark mt-2">Meet the Gymora Team</h1>
            <p class="lead text-muted mx-auto" style="max-width: 600px;">Our facility bridges the gap between clinical healthcare and commercial fitness. Meet the medical professionals and certified trainers dedicated to your safe progression.</p>
        </div>
    </div>

    <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-heart-pulse text-danger"></i> Clinical Medical Team</h3>
    <div class="row mb-5">
        <?php if (count($doctors) > 0): ?>
            <?php foreach ($doctors as $doc): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body pt-5 pb-4">
                            <div class="mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <i class="bi bi-person-hearts fs-1 text-danger"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-1">Dr. <?= htmlspecialchars($doc['name']) ?></h4>
                            <p class="text-danger fw-bold small text-uppercase mb-3">Resident Physician</p>
                            <p class="text-muted small">Specializes in pre-exercise medical assessments, injury diagnostics, and DSS clinical clearances.</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-muted">No medical staff currently listed.</div>
        <?php endif; ?>
    </div>

    <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-activity text-primary"></i> Certified Training Team</h3>
    <div class="row">
        <?php if (count($trainers) > 0): ?>
            <?php foreach ($trainers as $trainer): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body pt-5 pb-4">
                            <div class="mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <i class="bi bi-person-badge fs-1 text-primary"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-1"><?= htmlspecialchars($trainer['name']) ?></h4>
                            <p class="text-primary fw-bold small text-uppercase mb-3">Expert Trainer</p>
                            <p class="text-muted small">Designs medically-safe workout plans and leads DSS-filtered group fitness classes.</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-muted">No trainers currently listed.</div>
        <?php endif; ?>
    </div>
    
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="p-5 bg-light rounded-3">
                <h3 class="fw-bold">Ready to start your safe fitness journey?</h3>
                <p class="text-muted mb-4">Join today to book your initial medical consultation.</p>
                <a href="auth/register.php" class="btn btn-success btn-lg fw-bold px-5">Create an Account</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>