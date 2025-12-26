<?php
require '../../DB/db.php';
session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){
    die('unauthorized');
}

$project_code = $_POST['project_code'];
$action = $_POST['action'];

$status = ($action === 'approve') ? 'approved' : 'rejected';

$stmt = $pdo->prepare("
    UPDATE projects
    SET status = ?, approved_at = NOW()
    WHERE project_code = ? AND status = 'pending'
");
$stmt->execute([$status, $project_code]);

header("Location: ../US/admin.php");
exit;
