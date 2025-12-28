<?php
require '../DB/db.php';
include '../inc/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ===== ตรวจสอบสิทธิ์ admin =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

// ===== ลบ log ที่เกิน 5 ปี =====
$deleteBefore = date('Y-m-d H:i:s', strtotime('-5 years'));
$pdo->prepare("DELETE FROM activity_log WHERE deleted_at < ?")->execute([$deleteBefore]);

// ===== กู้คืนข้อมูล =====
if (isset($_GET['restore_id'])) {
    $restoreId = intval($_GET['restore_id']);

    $stmt = $pdo->prepare("
        SELECT * FROM activity_log
        WHERE id = ? AND restore_status = 0
    ");
    $stmt->execute([$restoreId]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($log) {
        $data = json_decode($log['item_data'], true);
        $type = $log['item_type'];

        switch ($type) {
            case 'user':
                $table = 'users';
                break;
            case 'project':
                $table = 'projects';
                break;
            case 'file':
                $table = 'project_files';
                break;
            default:
                die("ประเภทข้อมูลไม่รองรับ");
        }

        /* ===================================================
           FIX เดียวที่จำเป็นจริง
           =================================================== */
        if ($type === 'project') {

            // ถ้าไม่มี creator_id → ใช้ admin ปัจจุบัน
            if (empty($data['creator_id'])) {
                $data['creator_id'] = $_SESSION['user']['user_id'];
            } else {
                // ถ้ามี creator_id → เช็กว่าผู้ใช้ยังอยู่ไหม
                $chk = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
                $chk->execute([$data['creator_id']]);

                if (!$chk->fetch()) {
                    echo "<script>
                        alert('ไม่สามารถกู้คืนโครงงานได้ เนื่องจากผู้สร้างโครงงานถูกลบออกจากระบบแล้ว');
                        window.location.href='activity_log.php';
                    </script>";
                    exit;
                }
            }
        }

        // ===== Insert กลับเข้าตาราง =====
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $pdo->prepare($sql)->execute($values);

        // ===== อัปเดตสถานะ log =====
        $pdo->prepare("
            UPDATE activity_log
            SET restore_status = 1, restored_at = NOW()
            WHERE id = ?
        ")->execute([$restoreId]);

        echo "<script>
            alert('กู้คืนข้อมูลเรียบร้อยแล้ว');
            window.location.href='activity_log.php';
        </script>";
        exit;
    }
}

// ===== ดึงข้อมูลย้อนหลัง 5 ปี =====
$fiveYearsAgo = date('Y-m-d H:i:s', strtotime('-5 years'));
$stmt = $pdo->prepare("
    SELECT * FROM activity_log
    WHERE deleted_at >= ?
    ORDER BY deleted_at DESC
");
$stmt->execute([$fiveYearsAgo]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== สรุป =====
$total_logs = count($logs);
$restored_count = count(array_filter($logs, fn($l) => $l['restore_status'] == 1));
?>

<h2 style="margin:20px;">ถังขยะ</h2>
<p>ข้อมูลที่ถูกลบย้อนหลัง 5 ปี</p>

<div style="margin:15px 0; padding:10px; border:1px solid #ffc400; border-radius:8px;">
    <strong>จำนวน log ทั้งหมด:</strong> <?= $total_logs ?><br>
    <strong>กู้คืนแล้ว:</strong> <?= $restored_count ?>
</div>

<!-- ================= USER TABLE ================= -->
<h3 style="margin:20px 0 10px;">ถังขยะผู้ใช้ (Users)</h3>

<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">
<tr style="background:#b40000;color:#fff;text-align:center;">
    <th style="width:150px;">เวลา</th>
    <th>รหัสนักศึกษา</th>
    <th>ชื่อที่ถูกลบ</th>
    <th>ผู้ลบ</th>
    <th>สถานะ</th>
    <th>จัดการ</th>
</tr>

<?php foreach ($logs as $log): ?>
<?php
if ($log['item_type'] !== 'user') continue;
$data = json_decode($log['item_data'], true);
$deleted_name = $data['fullname'] ?? $data['email'] ?? '-';
?>
<tr>
    <td><?= htmlspecialchars($log['deleted_at']) ?></td>
    <td style="text-align:center;"><?= htmlspecialchars($log['item_id']) ?></td>
    <td><?= htmlspecialchars($deleted_name) ?></td>
    <td><?= htmlspecialchars($log['deleted_by']) ?></td>
    <td style="text-align:center;"><?= $log['restore_status'] ? 'กู้แล้ว' : 'ยังไม่ได้กู้' ?></td>
    <td style="text-align:center;">
        <?php if (!$log['restore_status']): ?>
            <a href="activity_log.php?restore_id=<?= $log['id'] ?>" onclick="return confirm('ยืนยันการกู้คืนข้อมูลนี้?')">
                <button style="background:#28a745;color:white;">Restore</button>
            </a>
        <?php else: ?>
            <button disabled style="background:#ccc;">Restore</button>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

<!-- ================= PROJECT TABLE ================= -->
<h3 style="margin:30px 0 10px;">ถังขยะโครงงาน (Projects)</h3>

<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">
<tr style="background:#b40000;color:#fff;text-align:center;">
    <th style="width:150px;">เวลา</th>
    <th>Project Code</th>
    <th>ชื่อที่ถูกลบ</th>
    <th>ผู้ลบ</th>
    <th>สถานะ</th>
    <th>จัดการ</th>
</tr>

<?php foreach ($logs as $log): ?>
<?php
if ($log['item_type'] !== 'project') continue;
$data = json_decode($log['item_data'], true);

$project_code = $data['project_code'] ?? '-';
$title_th = $data['title_th'] ?? '';
$title_en = $data['title_en'] ?? '';

if ($title_th && $title_en) {
    $deleted_name = htmlspecialchars($title_th) . "<br><small>(" . htmlspecialchars($title_en) . ")</small>";
} elseif ($title_th) {
    $deleted_name = htmlspecialchars($title_th);
} elseif ($title_en) {
    $deleted_name = htmlspecialchars($title_en);
} else {
    $deleted_name = '-';
}
?>
<tr>
    <td><?= htmlspecialchars($log['deleted_at']) ?></td>
    <td style="text-align:center;"><?= htmlspecialchars($project_code) ?></td>
    <td><?= $deleted_name ?></td>
    <td><?= htmlspecialchars($log['deleted_by']) ?></td>
    <td style="text-align:center;"><?= $log['restore_status'] ? 'กู้แล้ว' : 'ยังไม่ได้กู้' ?></td>
    <td style="text-align:center;">
        <?php if (!$log['restore_status']): ?>
            <a href="activity_log.php?restore_id=<?= $log['id'] ?>" onclick="return confirm('ยืนยันการกู้คืนข้อมูลนี้?')">
                <button style="background:#28a745;color:white;">Restore</button>
            </a>
        <?php else: ?>
            <button disabled style="background:#ccc;">Restore</button>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php include '../inc/footer.php'; ?>
