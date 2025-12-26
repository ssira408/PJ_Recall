<?php
require '../DB/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) exit;

$user_id = $_SESSION['user']['user_id'];
$img = $_SESSION['user']['profile_img'] ?? '';

if ($img && $img !== 'default.png') {
    @unlink('../uploads/' . $img);
}

$stmt = $conn->prepare(
    "UPDATE users SET profile_img=NULL WHERE user_id=?"
);
$stmt->execute([$user_id]);

$_SESSION['user']['profile_img'] = 'default.png';

header("Location: ".$_SERVER['HTTP_REFERER']);
