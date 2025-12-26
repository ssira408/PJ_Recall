<?php
ob_start(); 
require '../../DB/db.php';
include '../../inc/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* ===== ตรวจสอบสิทธิ์ admin ===== */
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

$admin_id      = $_SESSION['user']['user_id'];
$user_fullname = $_SESSION['user']['full_name'];
$profile_img   = $_SESSION['user']['profile_img'] ?? 'default.png';

/* ===== สรุปข้อมูล ===== */
$user_count    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$project_count = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();

/* ===== ดึงผู้ใช้ ===== */
$users = $pdo->query("
    SELECT user_id, full_name, student_id, id_card, email, role, education_level, department, profile_img
    FROM users
    ORDER BY 
        CASE role
            WHEN 'admin' THEN 1
            WHEN 'admin support' THEN 2
            WHEN 'employee' THEN 3
            WHEN 'user' THEN 4
            ELSE 5
        END, full_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ===== ดึงโครงงาน ===== */
$projects = $pdo->query("
    SELECT project_id, project_code, title_th, title_en,
           department, education_level, advisor_main, advisor_co,
           created_at, status
    FROM projects
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ===== สมาชิกโครงงาน ===== */
$project_members_stmt = $pdo->prepare("
    SELECT user_name, student_id, education_level
    FROM project_users
    WHERE project_id = ?
");
?>

<div style="margin:20px;">

<!-- ===== แสดงโปรไฟล์ ===== -->
<div style="position:relative; display:flex; align-items:center; gap:20px; margin-bottom:20px;">
    <img src="/PJ_Recall/uploads/<?= htmlspecialchars($profile_img) ?>" 
        onerror="this.src='/PJ_Recall/assets/default_profile.png'" 
        style="width:100px; height:100px; border-radius:50%; border:2px solid #b40000; object-fit:cover; cursor:pointer;"
        id="profileBtn">
    <div style="display:flex; flex-direction:column; gap:10px;">
        <span style="font-weight:bold; font-size:1.2em;"><?= htmlspecialchars($user_fullname) ?></span>
    </div>

    <!-- Dropdown menu -->
    <div id="profileMenu" style="display:none; position:absolute; top:110px; left:0; background:#fff; border:1px solid #ccc; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:10px; z-index:1000; min-width:150px;">
        <div style="padding:5px; cursor:pointer;" onclick="changeProfile()">เปลี่ยนรูปภาพ</div>
        <div style="padding:5px; cursor:pointer;" onclick="if(confirm('ลบรูปโปรไฟล์หรือไม่?')) location.href='/PJ_Recall/Dashboard/delete_profile.php';">ลบรูปภาพ</div>
    </div>
</div>

<!-- ===== สรุปข้อมูล ===== -->
<div style="margin-bottom:20px; padding:10px; border:1px solid #ffc400; border-radius:8px;">
    <strong>จำนวนผู้ใช้ทั้งหมด:</strong> <?= $user_count ?><br>
    <strong>จำนวนโครงการทั้งหมด:</strong> <?= $project_count ?>
</div>

<!-- =================== ตารางผู้ใช้ =================== -->
<h3>ผู้ใช้ทั้งหมด</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:30px;">
    <tr style="background:#b40000; color:#fff; text-align:center;">
        <th>ชื่อ–นามสกุล</th>
        <th>รหัสประจำตัว</th>
        <th>Email</th>
        <th>แผนก</th>
        <th>ระดับ</th>
        <th>ตำแหน่ง</th>
        <th>จัดการ</th>
    </tr>
    <?php foreach($users as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u['full_name']) ?></td>
        <td>
            <?php 
                if($u['role'] === 'user'){
                    echo htmlspecialchars($u['student_id'] ?? '-');
                } else {
                    echo htmlspecialchars($u['id_card'] ?? '-');
                }
            ?>
        </td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['department'] ?? '-') ?></td>
        <td><?= htmlspecialchars($u['education_level'] ?? '-') ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td align="center">
            <a href="view_us.php?id=<?= $u['user_id'] ?>"><button>ดู</button></a>
            <a href="edit_us.php?id=<?= $u['user_id'] ?>"><button>แก้ไข</button></a>
            <a href="delete_us.php?id=<?= $u['user_id'] ?>" onclick="return confirm('ลบผู้ใช้นี้?')">
                <button style="background:red;color:white;">ลบ</button>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- =================== ตารางโครงงาน =================== -->
<h3>โครงงานทั้งหมด</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <tr style="background:#b40000; color:#fff; text-align:center;">
        <th>รหัสโครงงาน</th>
        <th>ชื่อโครงงาน</th>
        <th>ผู้จัดทำ</th>
        <th>อาจารย์ที่ปรึกษา</th>
        <th>วันที่สร้าง</th>
        <th>แผนก</th>
        <th>สถานะ</th>
        <th>จัดการ</th>
    </tr>

    <?php foreach($projects as $p):
        $project_members_stmt->execute([$p['project_id']]);
        $members = $project_members_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <tr>
        <td align="center"><strong><?= htmlspecialchars($p['project_code']) ?></strong></td>
        <td>
            <?= htmlspecialchars($p['title_th']) ?>
            <?php if($p['title_en']) echo '<br><small>'.$p['title_en'].'</small>'; ?>
        </td>
        <td>
            <?php foreach($members as $m): ?>
                <?= htmlspecialchars($m['user_name']) ?>
                <?= $m['education_level'] ? '(' . $m['education_level'] . ')' : '' ?><br>
            <?php endforeach; ?>
        </td>
        <td>
            <?= htmlspecialchars($p['advisor_main']) ?>
            <?php if($p['advisor_co']) echo '<br>'.$p['advisor_co']; ?>
        </td>
        <td><?= $p['created_at'] ?></td>
        <td><?= htmlspecialchars($p['department']) ?></td>
        <td align="center">
            <?php
                if($p['status']=='pending')   echo '<span style="color:orange;">รอดำเนินการ</span>';
                if($p['status']=='approved')  echo '<span style="color:green;">อนุมัติแล้ว</span>';
                if($p['status']=='rejected')  echo '<span style="color:red;">ถูกปฏิเสธ</span>';
            ?>
        </td>
        <td align="center">
            <a href="../PJ/view_pj.php?code=<?= $p['project_code'] ?>"><button>ดู</button></a>
            <a href="../PJ/edit_pj.php?code=<?= $p['project_code'] ?>"><button>แก้ไข</button></a>
            <a href="../PJ/delete_pj.php?code=<?= $p['project_code'] ?>" onclick="return confirm('ลบโครงงานนี้?')">
                <button style="background:red;color:white;">ลบ</button>
            </a>

            <?php if($p['status']=='pending'): ?>
                <button
                    class="btn approve"
                    onclick='openApprove({
                        code:"<?= $p["project_code"] ?>",
                        title_th:"<?= htmlspecialchars($p["title_th"],ENT_QUOTES) ?>",
                        title_en:"<?= htmlspecialchars($p["title_en"]??"",ENT_QUOTES) ?>",
                        status:"<?= $p["status"] ?>"
                    })'>อนุมัติ
                </button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- =================== APPROVE WRAPPER =================== -->
<div id="approveWrapper" class="approve-wrapper">
    <div class="approve-card">
        <h3 id="apTitle"></h3>
        <p id="apTitleEn"></p>
        <p>สถานะ: <strong id="apStatus"></strong></p>

        <form method="post" action="../PJ/Approve_pj.php">
            <input type="hidden" name="project_code" id="apId">

            <button type="submit" name="action" value="approve" class="btn approve">อนุมัติ</button>
            <button type="submit" name="action" value="reject" class="btn reject"
                onclick="return confirm('ยืนยันปฏิเสธ?')">ปฏิเสธ</button>
            <button type="button" class="btn close" onclick="closeApprove()">ปิด</button>
        </form>
    </div>
</div>

<style>
.approve-wrapper {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.approve-card {
    background: #fff;
    width: 100%;
    max-width: 500px;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
    animation: pop .25s ease;
}
@keyframes pop {
    from {transform:scale(.9);opacity:0}
    to {transform:scale(1);opacity:1}
}
.approve-card form {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
/* ปิดไว้ก่อน */
/* .btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
} */
.btn.approve {background:#28a745;color:#fff}
.btn.reject {background:#dc3545;color:#fff}
.btn.close {background:#aaa}
</style>

<script>
function openApprove(p){
    document.getElementById('apId').value = p.code;
    document.getElementById('apTitle').innerText = p.title_th;
    document.getElementById('apTitleEn').innerText = p.title_en || '';
    document.getElementById('apStatus').innerText = p.status;
    document.getElementById('approveWrapper').style.display='flex';
}
function closeApprove(){
    document.getElementById('approveWrapper').style.display='none';
}
</script>


<?php
include '../../inc/footer.php';
ob_end_flush();
?>
