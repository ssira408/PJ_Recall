<?php
require '../DB/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ===== ตรวจสอบสิทธิ์ =====
$user = $_SESSION['user'] ?? null;
if (!$user || !in_array($user['role'], ['admin', 'admin support', 'employee'])) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

// ===== ตรวจสอบ project_id =====
if (!isset($_POST['project_id']) || !is_numeric($_POST['project_id'])) {
    die("<p style='color:red;'>ไม่พบรหัสโครงการที่ถูกต้อง</p>");
}

$project_id = intval($_POST['project_id']);

try {
    // ===== ดึงข้อมูลโครงการ =====
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = :project_id");
    $stmt->execute(['project_id' => $project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        die("<p style='color:red;'>ไม่พบโครงการนี้</p>");
    }

    // ===== อัปเดตสถานะเป็น rejected =====
    $update = $pdo->prepare("UPDATE projects SET status = 'rejected' WHERE project_id = :project_id");
    $update->execute(['project_id' => $project_id]);

    // ===== บันทึก activity_log =====
    $logStmt = $pdo->prepare("
        INSERT INTO activity_log (item_type, item_id, item_data, deleted_by, deleted_at)
        VALUES (:item_type, :item_id, :item_data, :deleted_by, NOW())
    ");
    $logStmt->execute([
        'item_type' => 'project',
        'item_id' => $project['project_id'],
        'item_data' => json_encode($project),
        'deleted_by' => $user['fullname'] ?? 'unknown'
    ]);

    echo "<script>alert('ปฏิเสธโครงการเรียบร้อยแล้ว'); window.location.href='../US/admin.php';</script>";

} catch (Exception $e) {
    echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.location.href='../US/admin.php';</script>";
}
?>
