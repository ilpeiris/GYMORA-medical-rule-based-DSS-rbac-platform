<?php
// /Gymora/api/log_progress.php

// --- FORCE ALL ERRORS TO DISPLAY ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../config/session.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $weight = floatval($_POST['weight_kg'] ?? 0);
    $body_fat = !empty($_POST['body_fat_pct']) ? floatval($_POST['body_fat_pct']) : null;
    $notes = trim($_POST['notes'] ?? '');
    $log_date = date('Y-m-d'); 
    
    if ($weight <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Valid weight is required.']);
        exit();
    }

    try {
        // 1. Fetch height from the Doctor's assessment
        $heightStmt = $pdo->prepare("SELECT height_cm FROM medical_assessments WHERE user_id = ? AND status = 'submitted' ORDER BY created_at DESC LIMIT 1");
        $heightStmt->execute([$user_id]);
        $assessment = $heightStmt->fetch();
        
        $bmi = null;
        if ($assessment && $assessment['height_cm'] > 0) {
            $height_m = $assessment['height_cm'] / 100;
            $bmi = round($weight / ($height_m * $height_m), 2);
        }

        // 2. Save the log
        $checkStmt = $pdo->prepare("SELECT id FROM progress_logs WHERE user_id = ? AND log_date = ?");
        $checkStmt->execute([$user_id, $log_date]);
        
        if ($checkStmt->rowCount() > 0) {
            $updateStmt = $pdo->prepare("UPDATE progress_logs SET weight_kg = ?, bmi = ?, body_fat_pct = ?, notes = ? WHERE user_id = ? AND log_date = ?");
            $updateStmt->execute([$weight, $bmi, $body_fat, $notes, $user_id, $log_date]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO progress_logs (user_id, logged_by, log_date, weight_kg, bmi, body_fat_pct, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$user_id, $user_id, $log_date, $weight, $bmi, $body_fat, $notes]);
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Progress logged successfully!', 'bmi' => $bmi]);
        exit();
        
    } catch (PDOException $e) {
        // If the database crashes, print the EXACT SQL error
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}
?>