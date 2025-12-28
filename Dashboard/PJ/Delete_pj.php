<?php
require '../../DB/db.php';
session_start();

/* ===== ตรวจสอบสิทธิ์ ===== */
if (
    !isset($_SESSION['user']) ||
    !in_array($_SESSION['user']['role'], ['admin', 'adminsupport'])
) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

/* ===== ตรวจสอบ project_code ===== */
if (!isset($_GET['code'])) {
    die("<p style='color:red;'>ไม่พบรหัสโครงงาน</p>");
}

$project_code = $_GET['code'];

try {

    /* ===== ดึงข้อมูลโครงงาน ===== */
    $stmt = $conn->prepare("
        SELECT *
        FROM projects
        WHERE project_code = ?
    ");
    $stmt->execute([$project_code]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo "<script>alert('ไม่พบโครงงาน'); location.href='../US/admin.php';</script>";
        exit;
    }

    /* ===== บันทึก activity log (ใช้ project_code เท่านั้น) ===== */
    $logStmt = $conn->prepare("
        INSERT INTO activity_log
        (item_type, item_id, item_data, deleted_by)
        VALUES ('project', ?, ?, ?)
    ");

    $logStmt->execute([
        $project['project_code'], // ✅ ใช้ project_code
        json_encode([
            'project_code' => $project['project_code'],
            'title_th'     => $project['title_th'] ?? null,
            'title_en'     => $project['title_en'] ?? null
        ], JSON_UNESCAPED_UNICODE),
        $_SESSION['user']['full_name']
    ]);

    /* ===== ลบโครงงาน ===== */
    $del = $conn->prepare("
        DELETE FROM projects
        WHERE project_code = ?
    ");
    $del->execute([$project_code]);

    echo "<script>alert('ลบโครงการเรียบร้อยแล้ว'); location.href='../US/admin.php';</script>";

} catch (Exception $e) {
    echo "<script>alert('เกิดข้อผิดพลาด'); location.href='../US/admin.php';</script>";
}
