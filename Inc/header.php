<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
$user_logged_in = !empty($user);
$role = $user['role'] ?? '';
$full_name = $user['fullname'] ?? trim(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Project Recall</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
<link rel="icon" href="/PJ_Recall/assets/logo_no_bg.png">
<style>
:root{--main:#b40000;}
body{margin:0;font-family:'Sarabun',sans-serif;}
.navbar{
    height:140px; background:var(--main);
    display:flex; align-items:center; padding:0 20px; color:#fff;
    position:relative; box-shadow:0 2px 6px rgba(0,0,0,.2);
}
.logo{
    display:flex; align-items:center; gap:20px; font-weight:bold; font-size:2em;
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
}
.logo img{height:100px;}
.nav-menu{
    position:absolute; left:20px; bottom:10px; display:flex; gap:10px;
}
.nav-menu a{
    color:#fff; text-decoration:none; padding:6px 10px; border-radius:6px;
}
.nav-menu a:hover{background:rgba(255,255,255,.15);}
</style>
</head>
<body>
<nav class="navbar">
    <div class="logo">PROJECT <img src="/PJ_Recall/assets/logo_no_bg.png"> RECALL</div>
    <?php if($user_logged_in): ?>
    <div class="nav-menu">
        <a href="/PJ_Recall/dashboard/index.php">หน้าแรก</a>
        <?php if($role==='admin'): ?>
            <a href="/PJ_Recall/Dashboard/US/admin.php">โปรไฟล์</a>
            <a href="/PJ_Recall/Dashboard/US/add_us.php">เพิ่มผู้ใช้</a>
            <a href="/PJ_Recall/Dashboard/PJ/Add_pj.php">เพิ่มโครงงาน</a>
            <a href="/PJ_Recall/dashboard/activity_log.php">ถังขยะ</a>
        <?php elseif($role==='admin_support'): ?>
            <a href="/PJ_Recall/Dashboard/US/admin_support.php">โปรไฟล์</a>
            <a href="/PJ_Recall/Dashboard/PJ/Add_pj.php">เพิ่มโครงงาน</a>
        <?php else: ?>
            <a href="/PJ_Recall/Dashboard/US/<?= $role ?>.php">โปรไฟล์</a>
            <a href="/PJ_Recall/Dashboard/PJ/Add_pj.php">เพิ่มโครงงาน</a>
        <?php endif; ?>
            <a href="/PJ_Recall/Dashboard/logout.php">ออกจากระบบ</a>
    </div>
    <?php endif; ?> 
</nav>
