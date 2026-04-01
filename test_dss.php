<?php
// /Gymora/test_dss.php
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'dss/dss_engine.php';

// Must be logged in to test
if (!isLoggedIn()) {
    die("Please login first to test the DSS engine.");
}

$user_id = $_SESSION['user_id'];

// Run the engine for the currently logged-in user!
$dss_output = getDSSRestrictionsForUser($user_id);

require_once 'includes/header.php';
?>

<div class="row mt-5">
    <div class="col-md-8 mx-auto">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">DSS Engine Diagnostics</h4>
            </div>
            <div class="card-body">
                <p>Testing DSS rules for User ID: <strong><?= $user_id ?></strong> (<?= htmlspecialchars($_SESSION['name']) ?>)</p>
                <hr>
                
                <h5>Raw Engine Output:</h5>
                <pre class="bg-dark text-success p-3 rounded border border-success">
<?php print_r($dss_output); ?>
                </pre>
                
                <div class="mt-4 alert alert-info">
                    <strong>How to read this:</strong><br>
                    If the arrays above are completely empty, it means this user currently has NO active medical conditions in the system (or their severity is too low to trigger a rule).<br><br>
                    To see it work, log in as a Doctor, submit an assessment for this user with <strong>Hypertension (Severity 3+)</strong> or <strong>Lumbar Disc (Severity 1+)</strong>, and then refresh this page!
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>