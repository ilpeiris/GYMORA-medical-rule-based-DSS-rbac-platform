<?php
// /Gymora/includes/footer.php
?>
</div> <footer class="bg-dark text-light py-5 mt-5 border-top border-secondary">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-heart-pulse-fill text-success me-2"></i>Gymora
                </h5>
                <p class="text-secondary small pe-lg-4">
                    The UK's premier Medical-Integrated Smart Gym. We bridge the gap between clinical healthcare and commercial fitness using our intelligent Decision Support System.
                </p>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <h6 class="fw-bold text-uppercase tracking-wide mb-3">Quick Links</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="<?= BASE_URL ?>index.php" class="text-secondary text-decoration-none hover-white">Home</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>about.php" class="text-secondary text-decoration-none hover-white">About Us</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>packages.php" class="text-secondary text-decoration-none hover-white">Membership Plans</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>team.php" class="text-secondary text-decoration-none hover-white">Clinical Team</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h6 class="fw-bold text-uppercase tracking-wide mb-3">Contact</h6>
                <ul class="list-unstyled text-secondary small mb-3">
                    <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> 123 Innovation Way, Cardiff, UK</li>
                    <li class="mb-2"><i class="bi bi-envelope me-2 text-info"></i> support@Gymora.com</li>
                    <li class="mb-2"><i class="bi bi-telephone me-2 text-success"></i> +44 29 2000 1234</li>
                </ul>
            </div>
        </div>
        
        <hr class="border-secondary my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start text-secondary small">
                &copy; <?= date('Y') ?> Gymora System. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end text-secondary small">
                <span class="me-3">GDPR Compliant</span> | <span class="ms-3">v1.0 (Iterative Prototype)</span>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Quick hover effect for footer links */
    .hover-white:hover { color: #ffffff !important; transition: color 0.2s ease-in-out; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>