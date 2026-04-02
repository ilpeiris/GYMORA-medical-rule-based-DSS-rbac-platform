<?php
// /Gymora/packages.php
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'config/constants.php';

// Fetch all active packages from the database
$stmt = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY price_gbp ASC");
$packages = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <span class="text-primary fw-bold text-uppercase tracking-wide">Membership Plans</span>
            <h1 class="display-4 fw-bold text-dark mt-2">Transparent, Medical-Grade Pricing</h1>
            <p class="lead text-muted mx-auto" style="max-width: 600px;">Every Gymora membership includes dedicated clinical consultations to ensure your fitness journey is mathematically and medically optimized for your body.</p>
        </div>
    </div>

    <div class="row justify-content-center align-items-center">
        <?php foreach ($packages as $index => $pkg): 
            $features = json_decode($pkg['features'], true) ?? [];
            // Make the middle package "pop" as the recommended option
            $is_popular = ($index === 1); 
            $card_class = $is_popular ? 'border-primary shadow-lg' : 'border-0 shadow-sm';
            $btn_class = $is_popular ? 'btn-primary' : 'btn-outline-primary';
        ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 <?= $card_class ?> position-relative bg-white" style="<?= $is_popular ? 'transform: scale(1.05); z-index: 10;' : '' ?>">
                    
                    <?php if ($is_popular): ?>
                        <div class="position-absolute top-0 start-50 translate-middle-x">
                            <span class="badge bg-primary px-3 py-2 rounded-bottom-3 text-uppercase tracking-wide">Most Popular</span>
                        </div>
                    <?php endif; ?>

                    <div class="card-body p-5 text-center">
                        <h4 class="fw-bold mb-3 text-dark"><?= htmlspecialchars($pkg['name']) ?></h4>
                        <div class="mb-4">
                            <span class="display-5 fw-bold text-dark">£<?= htmlspecialchars($pkg['price_gbp']) ?></span>
                            <span class="text-muted">/ <?= $pkg['duration_months'] ?> mo</span>
                        </div>
                        
                        <p class="fw-bold text-success mb-4 pb-3 border-bottom">
                            <i class="bi bi-heart-pulse-fill me-2"></i> Includes <?= $pkg['consultation_count'] ?> Doctor Consultations
                        </p>
                        
                        <ul class="list-unstyled text-start mb-5">
                            <?php foreach ($features as $feature): ?>
                                <li class="mb-3 text-muted">
                                    <i class="bi bi-check2-circle text-primary me-2 fs-5 align-middle"></i> 
                                    <?= htmlspecialchars($feature) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="mt-auto">
                            <a href="auth/register.php?plan=<?= $pkg['id'] ?>" class="btn <?= $btn_class ?> btn-lg w-100 fw-bold rounded-pill">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($packages) == 0): ?>
            <div class="col-12 text-center text-muted py-5">
                <h4>No membership packages currently available.</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>