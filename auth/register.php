<?php
// /Gymora/auth/register.php
require_once '../config/db.php';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . BASE_URL . $_SESSION['role'] . "/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database
            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            try {
                $insertStmt->execute([$name, $email, $hashed_password, $role]);
                $success = "Registration successful! You can now login.";
            } catch (PDOException $e) {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create an Account</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?> <a href="login.php" class="alert-link">Click here to login</a>.
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">I am a...</label>
                            <select name="role" class="form-select" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="<?= ROLE_USER ?>">Gym Member</option>
                                <option value="<?= ROLE_DOCTOR ?>">Medical Doctor</option>
                                <option value="<?= ROLE_TRAINER ?>">Fitness Trainer</option>
                                </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>