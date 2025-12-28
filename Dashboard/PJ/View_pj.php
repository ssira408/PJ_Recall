<?php
require '../../DB/db.php';
include '../../inc/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ตรวจสอบ login
if (!isset($_SESSION['user'])) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

// กำหนดสิทธิ์
$user_role = $_SESSION['user']['role'] ?? '';
$is_admin = ($user_role === 'admin' || $user_role === 'adminsupport');
$is_employee = ($user_role === 'employee');
$is_user = ($user_role === 'user');

// ===== รับ project_code =====
if (!isset($_GET['code'])) die("<p style='color:red;'>ไม่พบโครงงาน</p>");
$project_code = trim($_GET['code']);

// ===== ดึงข้อมูลโครงงาน =====
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_code = :code");
$stmt->execute([':code' => $project_code]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) die("<p style='color:red;'>ไม่พบโครงการ</p>");

// ใช้ project_id ภายในระบบ
$project_id = $project['project_id'];

// ดึงผู้จัดทำจาก project_users
$stmtUsers = $conn->prepare("
    SELECT user_name, student_id, education_level 
    FROM project_users 
    WHERE project_id = :pid
");
$stmtUsers->execute([':pid' => $project_id]);
$authors = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

include '../../inc/header.php';
?>

<div style="margin:20px; font-size:1.80em;">
    <h2>รายละเอียดโครงงาน</h2>
</div>

<div style="border:1px solid #ffc400; padding:10px; margin:15px; border-radius:8px;">

<?php if($is_admin || $is_employee): ?>
    <p><strong>รหัสโครงงาน :</strong> <?= htmlspecialchars($project['project_code']) ?></p>
<?php endif; ?>

<p><strong>ชื่อโครงงาน (ไทย) :</strong> <?= htmlspecialchars($project['title_th'] ?? '-') ?></p>
<p><strong>ชื่อโครงงาน (อังกฤษ) :</strong> <?= htmlspecialchars($project['title_en'] ?? '-') ?></p>

<p><strong>แผนกวิชา/ระดับการศึกษา :</strong>
    <?= htmlspecialchars($project['department'] ?? '-') ?> /
    <?= htmlspecialchars($project['education_level'] ?? '-') ?>
</p>

<p><strong>ผู้จัดทำ :</strong><br>
<?php
$author_list = [];
foreach($authors as $a){
    $name = htmlspecialchars($a['user_name'] ?? '');
    if($name) $author_list[] = $name;
}
echo !empty($author_list) ? implode("<br>&nbsp;&nbsp;&nbsp;&nbsp;", $author_list) : '-';
?>
</p>

<p><strong>ครูที่ปรึกษาโครงงาน :</strong> <?= htmlspecialchars($project['advisor_main'] ?? '-') ?></p>
<p><strong>ครูที่ปรึกษาร่วม :</strong> <?= htmlspecialchars($project['advisor_co'] ?? '-') ?></p>

<?php if($is_admin || $is_employee): ?>
    <?php if (!empty($project['abstract'])): ?>
        <p><strong>บทคัดย่อ :</strong><br><?= nl2br(htmlspecialchars($project['abstract'])) ?></p>
    <?php endif; ?>
<?php endif; ?>

<p><strong>วัตถุประสงค์ :</strong><br><?= nl2br(htmlspecialchars($project['objective'] ?? '-')) ?></p>
<p><strong>วิธีดำเนินการ :</strong><br><?= nl2br(htmlspecialchars($project['working_principle'] ?? '-')) ?></p>
<p><strong>ขอบเขต :</strong><br><?= nl2br(htmlspecialchars($project['highlight'] ?? '-')) ?></p>
<p><strong>ประโยชน์ :</strong><br><?= nl2br(htmlspecialchars($project['benefit'] ?? '-')) ?></p>
<p><strong>ระยะเวลา :</strong> <?= htmlspecialchars($project['duration'] ?? '-') ?></p>
<p><strong>วิทยาลัย :</strong> <?= htmlspecialchars($project['college'] ?? '-') ?></p>

<?php
// ===== ไฟล์เอกสาร =====
$stmtFiles = $conn->prepare("SELECT * FROM project_files WHERE project_id = :pid");
$stmtFiles->execute([':pid' => $project_id]);
$files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

if(!empty($files)){
    echo "<p><strong>ไฟล์เอกสาร :</strong><br>";
    foreach($files as $f){
        $file_path = '../../projects/' . basename($f['file_name']);
        if(file_exists($file_path)){
            echo "<a href='$file_path' download>"
                . htmlspecialchars($f['file_name']) .
                "</a><br>";
        }
    }
    echo "</p>";
}
?>

<?php if($is_admin || $is_employee): ?>
    <?php if (!empty($project['github_link'])): ?>
        <p><strong>GitHub :</strong>
            <a href="<?= htmlspecialchars($project['github_link']) ?>" target="_blank">
                <?= htmlspecialchars($project['github_link']) ?>
            </a>
        </p>
    <?php endif; ?>
<?php endif; ?>

<a href="<?= $is_admin ? '../US/admin.php' : ($is_employee ? '../US/employee.php' : '../US/user.php') ?>">
    <button class="back-btn">กลับ</button>
</a>

</div>

<style>
.back-btn {
    padding: 6px 12px;
    background: #b40000;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.back-btn:hover {
    background: #900000;
}
</style>

<?php include '../../inc/footer.php'; ?>
