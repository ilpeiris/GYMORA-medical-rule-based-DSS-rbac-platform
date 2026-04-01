<?php

session_start();

// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to enforce role-based access control
function requireRole($required_role) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "auth/login.php");
        exit();
    }
    
    // If an array of roles is passed (e.g., ['admin', 'doctor'])
    if (is_array($required_role)) {
        if (!in_array($_SESSION['role'], $required_role)) {
            header("Location: " . BASE_URL . "index.php?error=unauthorized");
            exit();
        }
    } 
    // If a single string role is passed
    else {
        if ($_SESSION['role'] !== $required_role) {
            header("Location: " . BASE_URL . "index.php?error=unauthorized");
            exit();
        }
    }
}
?>