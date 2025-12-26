<?php
require '../../DB/db.php';
session_start();

/* ===== ตรวจสอบสิทธิ์ admin / adminsupport ===== */
if (
    !isset($_SESSION['user']) ||
    !in_array($_SESSION['user']['role'], ['admin', 'adminsupport'])
) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

/* ===== ตรวจสอบว่ามี user_id ===== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p style='color:red;'>ไม่มี ID ผู้ใช้ที่ระบุหรือไม่ถูกต้อง</p>");
}

$user_id = intval($_GET['id']);

try {

    /* ===== ดึงข้อมูลผู้ใช้ก่อนลบ ===== */
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>
            alert('ไม่พบผู้ใช้ที่ต้องการลบ');
            window.location.href='../US/admin.php';
        </script>";
        exit;
    }

    /* ===== บันทึก activity_log ===== */
    $logStmt = $pdo->prepare("
        INSERT INTO activity_log
        (item_type, item_id, item_data, deleted_by, deleted_at)
        VALUES (:item_type, :item_id, :item_data, :deleted_by, NOW())
    ");
    $logStmt->execute([
        'item_type'  => 'user',
        'item_id'    => $user['user_id'], // INT
        'item_data'  => json_encode($user, JSON_UNESCAPED_UNICODE),
        'deleted_by' => $_SESSION['user']['full_name']
    ]);

    /* ===== ลบผู้ใช้ ===== */
    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);

    echo "<script>
        alert('ลบผู้ใช้เรียบร้อยแล้ว');
        window.location.href='../US/admin.php';
    </script>";

} catch (Exception $e) {
    echo "<script>
        alert('เกิดข้อผิดพลาด');
        window.location.href='../US/admin.php';
    </script>";
}
