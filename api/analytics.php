<?php
// /Gymora/api/analytics.php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/db.php';
require_once '../config/session.php';

if (!isLoggedIn()) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$type = $_GET['type'] ?? '';
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

if ($_SESSION['role'] === 'user' && $user_id !== $_SESSION['user_id']) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized data access']);
    exit();
}

if ($type === 'progress') {
    try {
        $stmt = $pdo->prepare("SELECT log_date, weight_kg, bmi, body_fat_pct FROM progress_logs WHERE user_id = ? ORDER BY log_date ASC LIMIT 30");
        $stmt->execute([$user_id]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $dates = [];
        $weights = [];
        $bmis = [];
        
        foreach ($logs as $log) {
            $dates[] = date('M j', strtotime($log['log_date']));
            $weights[] = floatval($log['weight_kg']);
            $bmis[] = floatval($log['bmi']);
        }
        
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'dates' => $dates, 
            'weights' => $weights, 
            'bmis' => $bmis
        ]);
        exit();
    } catch (PDOException $e) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'DB Error']);
        exit();
    }
} else {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid analytics type']);
    exit();
}
?>