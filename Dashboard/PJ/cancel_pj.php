<?php
require '../../DB/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ===== ตรวจสอบ login =====
$user = $_SESSION['user'] ?? null;
if (!$user) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

// ===== รับ project_code =====
$project_code = $_POST['project_code'] ?? '';
if (!$project_code) {
    die("<p style='color:red;'>ไม่พบโครงการ</p>");
}

// ===== ดึงข้อมูลโครงการ =====
$stmt = $pdo->prepare("SELECT * FROM projects WHERE project_code = :project_code");
$stmt->execute(['project_code' => $project_code]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    die("<p style='color:red;'>ไม่พบโครงการนี้</p>");
}

// ===== ห้ามยกเลิก ถ้าอนุมัติแล้ว =====
if ($project['status'] === 'approved') {
    die("<p style='color:red;'>โครงการนี้ได้รับการอนุมัติแล้ว ไม่สามารถยกเลิกได้</p>");
}

// ===== ตรวจสอบสิทธิ์ (เหมือน edit_pj.php) =====
$can_cancel = false;
$authors_list = explode(',', $project['author'] ?? '');
$author_names = array_map(fn($a)=> explode('||',$a)[0], $authors_list);

switch ($user['role']) {
    case 'admin':
    case 'admin support':
        $can_cancel = true;
        break;
    case 'employee':
        if ($project['department'] === $user['department'] || in_array($user['fullname'], $author_names)) {
            $can_cancel = true;
        }
        break;
    case 'user':
        if (in_array($user['fullname'], $author_names)) {
            $can_cancel = true;
        }
        break;
}

if (!$can_cancel) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์ยกเลิกโครงการนี้</p>");
}

// ===== ลบโครงการ =====
$stmt = $pdo->prepare("DELETE FROM projects WHERE project_code = :project_code");
$stmt->execute(['project_code' => $project_code]);

echo "<script>
    alert('ยกเลิกการยื่นโครงการเรียบร้อยแล้ว');
    window.location.href='../US/admin.php';
</script>";
exit;
