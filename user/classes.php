<?php
// /carefit/user/classes.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_USER);

$user_id = $_SESSION['user_id'];

// 1. Fetch User's Active Medical Conditions for DSS Checking
$condStmt = $pdo->prepare("
    SELECT c.condition_name 
    FROM medical_conditions c
    JOIN medical_assessments a ON c.assessment_id = a.id
    WHERE a.user_id = ? AND a.status = 'submitted' AND c.is_active = 1
");
$condStmt->execute([$user_id]);
$user_conditions = $condStmt->fetchAll(PDO::FETCH_COLUMN); // Gives us a flat array like ['knee_injury', 'hypertension']

// 2. Fetch all upcoming active classes
$classStmt = $pdo->query("
    SELECT c.*, u.name as trainer_name 
    FROM classes c
    JOIN users u ON c.trainer_id = u.id
    WHERE c.datetime >= NOW() AND c.is_active = 1
    ORDER BY c.datetime ASC
");
$classes = $classStmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <h2 class="fw-bold">Book a Class</h2>
        <p class="text-muted">Our DSS engine automatically filters classes based on your medical profile to ensure your safety.</p>
        <hr>
    </div>
</div>

<div class="row">
    <?php foreach ($classes as $class): ?>
        <?php 
            // DSS LOGIC: Check if this class is safe for this user
            $is_safe = true;
            $block_reason = "";
            $class_tags = json_decode($class['contraindication_tags'], true) ?? [];
            
            // Find if any of the user's conditions overlap with the class's danger tags
            $intersect = array_intersect($user_conditions, $class_tags);
            
            if (count($intersect) > 0) {
                $is_safe = false;
                // Grab the first matched condition to show in the warning
                $matched_condition = ucwords(str_replace('_', ' ', reset($intersect)));
                $block_reason = "Contraindicated due to your active diagnosis: " . $matched_condition;
            }
            
            // Also check if the class is full
            $is_full = ($class['enrolled_count'] >= $class['capacity']);
        ?>
        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100 <?= !$is_safe ? 'border-danger bg-light' : 'border-dark' ?>">
                <div class="card-header <?= !$is_safe ? 'bg-danger text-white' : 'bg-dark text-white' ?> d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= htmlspecialchars($class['name']) ?></h5>
                    <?php if (!$is_safe): ?>
                        <span class="badge bg-light text-danger"><i class="bi bi-shield-lock"></i> DSS Blocked</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong><i class="bi bi-calendar"></i> Date:</strong> <?= date('D, M j, Y', strtotime($class['datetime'])) ?></p>
                    <p class="mb-1"><strong><i class="bi bi-clock"></i> Time:</strong> <?= date('g:i A', strtotime($class['datetime'])) ?> (<?= $class['duration_minutes'] ?> min)</p>
                    <p class="mb-1"><strong><i class="bi bi-person-badge"></i> Trainer:</strong> <?= htmlspecialchars($class['trainer_name']) ?></p>
                    <p class="mb-3"><strong><i class="bi bi-geo-alt"></i> Room:</strong> <?= htmlspecialchars($class['location']) ?></p>
                    <p class="small text-muted"><?= htmlspecialchars($class['description']) ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="text-muted small">
                            Capacity: <?= $class['enrolled_count'] ?> / <?= $class['capacity'] ?>
                        </span>
                        
                        <?php if (!$is_safe): ?>
                            <button class="btn btn-danger disabled" title="<?= $block_reason ?>">Medically Unsafe</button>
                        <?php elseif ($is_full): ?>
                            <button class="btn btn-secondary disabled">Class Full</button>
                        <?php else: ?>
                            <form method="POST" action="book_class.php">
                                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                <button type="submit" class="btn btn-primary fw-bold">Book Slot</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$is_safe): ?>
                    <div class="card-footer bg-danger text-white small text-center fw-bold">
                        <?= $block_reason ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (count($classes) == 0): ?>
        <div class="col-12 text-center py-5 text-muted">
            <h4>No classes scheduled at the moment.</h4>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>