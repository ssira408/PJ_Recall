<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../inc/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $status  = $_POST['status'] ?? null;

    if (!$user_id || !$status) {
        header("Location: ../admin_users.php?error=missing");
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE users 
        SET status = :status, approved_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':status' => $status,
        ':id'     => $user_id
    ]);

    header("Location: ../admin_users.php?success=1");
    exit;
}
