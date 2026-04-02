<?php
// /Gymora/api/log_progress.php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $weight = floatval($_POST['weight_kg']);
    $body_fat = !empty($_POST['body_fat_pct']) ? floatval($_POST['body_fat_pct']) : null;
    $notes = trim($_POST['notes']);
    $log_date = date('Y-m-d'); // Today's date
    
    if ($weight <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Valid weight is required.']);
        exit();
    }

    try {
        // 1. Fetch the user's height from their latest medical assessment to calculate BMI
        $heightStmt = $pdo->prepare("SELECT height_cm FROM medical_assessments WHERE user_id = ? AND status = 'submitted' ORDER BY created_at DESC LIMIT 1");
        $heightStmt->execute([$user_id]);
        $assessment = $heightStmt->fetch();
        
        $bmi = null;
        if ($assessment && $assessment['height_cm'] > 0) {
            $height_m = $assessment['height_cm'] / 100;
            $bmi = round($weight / ($height_m * $height_m), 2);
        }

        // 2. Prevent multiple logs on the exact same day (Update instead of Insert)
        $checkStmt = $pdo->prepare("SELECT id FROM progress_logs WHERE user_id = ? AND log_date = ?");
        $checkStmt->execute([$user_id, $log_date]);
        
        if ($checkStmt->rowCount() > 0) {
            // Update today's entry
            $updateStmt = $pdo->prepare("UPDATE progress_logs SET weight_kg = ?, bmi = ?, body_fat_pct = ?, notes = ? WHERE user_id = ? AND log_date = ?");
            $updateStmt->execute([$weight, $bmi, $body_fat, $notes, $user_id, $log_date]);
        } else {
            // Insert new entry
            $insertStmt = $pdo->prepare("INSERT INTO progress_logs (user_id, logged_by, log_date, weight_kg, bmi, body_fat_pct, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$user_id, $user_id, $log_date, $weight, $bmi, $body_fat, $notes]);
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Progress logged successfully!', 'bmi' => $bmi]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
}
?>