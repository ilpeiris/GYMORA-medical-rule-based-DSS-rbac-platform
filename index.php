<?php
// /Gymora/index.php
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'config/constants.php';

require_once 'includes/header.php';
?>

<div class="bg-dark text-white py-5 mb-5 shadow" style="background: linear-gradient(135deg, var(--cf-slate-900) 0%, #1e293b 100%);">
    <div class="container py-5 text-center">
        <h1 class="display-3 fw-bold mb-3" style="color: #cbd5e1;">Medical-Grade Fitness.</h1>
        <p class="lead mb-5 mx-auto" style="max-width: 700px; color: #cbd5e1;">
            Gymora bridges the gap between clinical healthcare and commercial gyms. Our Rule-Based Decision Support System ensures every workout and class is mathematically safe for your body.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="auth/register.php" class="btn btn-primary btn-lg fw-bold px-4">Start Your Journey</a>
            <a href="auth/login.php" class="btn btn-outline-light btn-lg fw-bold px-4">Member Login</a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <span class="text-success fw-bold text-uppercase tracking-wide">Core Technology</span>
            <h2 class="fw-bold text-dark mt-2">Why Choose Gymora?</h2>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="mb-3"><i class="bi bi-heart-pulse text-danger" style="font-size: 3rem;"></i></div>
                <h4 class="fw-bold">Clinical Assessments</h4>
                <p class="text-muted small">Every membership begins with a comprehensive physical assessment by our resident doctors to establish your medical baseline.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="mb-3"><i class="bi bi-cpu text-primary" style="font-size: 3rem;"></i></div>
                <h4 class="fw-bold">Smart DSS Engine</h4>
                <p class="text-muted small">Our intelligent Decision Support System cross-references your medical data against exercise contraindications to prevent injuries.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="mb-3"><i class="bi bi-shield-lock text-success" style="font-size: 3rem;"></i></div>
                <h4 class="fw-bold">UK GDPR Compliant</h4>
                <p class="text-muted small">Your medical data is encrypted and handled with strict Role-Based Access Control, ensuring total privacy and legal compliance.</p>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="p-5 bg-light rounded-4 shadow-sm text-center">
        <h3 class="fw-bold text-dark">Ready to see our facilities?</h3>
        <p class="text-muted mb-4">Browse our upcoming classes or meet our team of clinical professionals.</p>
        <a href="schedule.php" class="btn btn-outline-dark fw-bold px-4 me-2">View Schedule</a>
        <a href="team.php" class="btn btn-outline-primary fw-bold px-4">Meet the Team</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>