<?php
// /Gymora/user/book_class.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_USER);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['class_id'])) {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];
$class_id = $_POST['class_id'];

try {
    // 1. Fetch class details
    $stmt = $pdo->prepare("SELECT capacity, enrolled_count, contraindication_tags FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
    $class = $stmt->fetch();
    
    if (!$class) {
        die("Class not found.");
    }

    // 2. Strict DSS Server-Side Check (Security Layer)
    $condStmt = $pdo->prepare("
        SELECT c.condition_name 
        FROM medical_conditions c
        JOIN medical_assessments a ON c.assessment_id = a.id
        WHERE a.user_id = ? AND a.status = 'submitted' AND c.is_active = 1
    ");
    $condStmt->execute([$user_id]);
    $user_conditions = $condStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $class_tags = json_decode($class['contraindication_tags'], true) ?? [];
    $intersect = array_intersect($user_conditions, $class_tags);
    
    if (count($intersect) > 0) {
        // Log the blocked attempt!
        $reason = "DSS Blocked: User has " . implode(', ', $intersect);
        $logStmt = $pdo->prepare("INSERT INTO bookings (user_id, class_id, status, dss_block_reason) VALUES (?, ?, 'blocked', ?)");
        $logStmt->execute([$user_id, $class_id, $reason]);
        
        die("Security Check Failed: This class is medically unsafe for you. Our DSS engine has blocked this transaction.");
    }

    // 3. Check Capacity
    if ($class['enrolled_count'] >= $class['capacity']) {
        die("Sorry, this class is already full.");
    }

    // 4. Check for double booking (User already booked this exact class)
    $checkStmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND class_id = ? AND status = 'confirmed'");
    $checkStmt->execute([$user_id, $class_id]);
    if ($checkStmt->rowCount() > 0) {
        die("You are already booked for this class!");
    }

    // 5. If everything is safe, execute the booking transaction
    $pdo->beginTransaction();
    
    $bookStmt = $pdo->prepare("INSERT INTO bookings (user_id, class_id, status) VALUES (?, ?, 'confirmed')");
    $bookStmt->execute([$user_id, $class_id]);
    
    $updateStmt = $pdo->prepare("UPDATE classes SET enrolled_count = enrolled_count + 1 WHERE id = ?");
    $updateStmt->execute([$class_id]);
    
    $pdo->commit();
    
    // Redirect back with success (Using a simple GET param for the alert)
    header("Location: dashboard.php?msg=class_booked");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Booking failed: " . $e->getMessage());
}
?>