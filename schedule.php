<?php
// /Gymora/schedule.php
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'config/constants.php';

// Fetch all upcoming active classes
$classStmt = $pdo->query("
    SELECT c.*, u.name as trainer_name 
    FROM classes c
    JOIN users u ON c.trainer_id = u.id
    WHERE c.datetime >= NOW() AND c.is_active = 1
    ORDER BY c.datetime ASC
");
$classes = $classStmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <span class="text-primary fw-bold text-uppercase tracking-wide">Live Timetable</span>
            <h1 class="display-4 fw-bold text-dark mt-2">Class Schedule</h1>
            <p class="lead text-muted mx-auto" style="max-width: 600px;">Find the perfect class for your goals. All our sessions are medically supervised and filtered through our intelligent Decision Support System upon booking.</p>
        </div>
    </div>

    <div class="row">
        <?php foreach ($classes as $class): 
            // Determine a visual color tag based on impact level
            $impact_badge = 'bg-secondary';
            if ($class['impact_level'] == 'low') $impact_badge = 'bg-success';
            if ($class['impact_level'] == 'medium') $impact_badge = 'bg-warning text-dark';
            if ($class['impact_level'] == 'high') $impact_badge = 'bg-danger';
            
            $is_full = ($class['enrolled_count'] >= $class['capacity']);
        ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($class['name']) ?></h4>
                            <span class="badge <?= $impact_badge ?> rounded-pill text-uppercase" style="font-size: 0.7rem; padding: 0.4em 0.8em;">
                                <?= htmlspecialchars($class['impact_level']) ?> Impact
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <p class="mb-2 text-muted"><i class="bi bi-calendar-event me-2 text-primary"></i> <strong><?= date('D, M j, Y', strtotime($class['datetime'])) ?></strong></p>
                            <p class="mb-2 text-muted"><i class="bi bi-clock me-2 text-primary"></i> <?= date('g:i A', strtotime($class['datetime'])) ?> (<?= $class['duration_minutes'] ?> min)</p>
                            <p class="mb-2 text-muted"><i class="bi bi-person-badge me-2 text-primary"></i> Trainer: <?= htmlspecialchars($class['trainer_name']) ?></p>
                            <p class="mb-0 text-muted"><i class="bi bi-geo-alt me-2 text-primary"></i> <?= htmlspecialchars($class['location']) ?></p>
                        </div>
                        
                        <p class="small text-secondary mb-4"><?= htmlspecialchars($class['description']) ?></p>
                        
                        <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-bold">
                                <?= $class['enrolled_count'] ?> / <?= $class['capacity'] ?> Booked
                            </span>
                            
                            <?php if ($is_full): ?>
                                <button class="btn btn-secondary btn-sm disabled fw-bold px-3">Class Full</button>
                            <?php else: ?>
                                <a href="auth/login.php" class="btn btn-outline-primary btn-sm fw-bold px-3">Log in to Book</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($classes) == 0): ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-light rounded-3 shadow-sm">
                    <i class="bi bi-calendar-x display-1 text-muted mb-3 d-block"></i>
                    <h3 class="text-muted">No classes scheduled right now.</h3>
                    <p class="text-muted mb-0">Check back later as our trainers update the timetable weekly.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>