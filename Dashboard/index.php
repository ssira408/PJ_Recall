<?php
require '../DB/db.php';
if (session_status() == PHP_SESSION_NONE) session_start();
include '../Inc/header.php';

$user_logged_in = isset($_SESSION['user']);

// ดึงโครงงานทั้งหมด
$stmt = $conn->prepare("
    SELECT p.* 
    FROM projects p
    ORDER BY p.project_id DESC
");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>โครงงานทั้งหมด</h2>

    <?php if(empty($projects)): ?>
        <p>ยังไม่มีโครงงานที่ได้รับการอนุมัติ</p>
    <?php else: ?>
        <?php foreach($projects as $p): ?>
            <div class="project-card">

                <!-- แผนกวิชา (แสดงบนสุด) -->
                <?php if(!empty($p['department'])): ?>
                    <span class="dept-badge">
                        <?= htmlspecialchars($p['department']) ?>
                    </span>
                <?php endif; ?>

                <h3>
                    <a href="<?= $user_logged_in ? 'PJ/view_pj.php?id='.htmlspecialchars($p['project_id']) : 'login.php' ?>">
                        <?= htmlspecialchars($p['title_th'] ?: $p['title_en'] ?: '-') ?>
                    </a>
                </h3>

                <!-- ระดับการศึกษา -->
                <p><strong>ระดับการศึกษา:</strong> <?= htmlspecialchars($p['education_level']) ?></p>

                <!-- ผู้จัดทำหลายคน -->
                <?php
                    $stmtUsers = $conn->prepare("
                        SELECT user_name, student_id
                        FROM project_users
                        WHERE project_id = :pid
                    ");
                    $stmtUsers->execute([':pid'=>$p['project_id']]);
                    $authors = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <p><strong>ผู้จัดทำ:</strong>
                    <?php if(!empty($authors)): ?>
                        <?php
                            $author_list = [];
                            foreach($authors as $a){
                                $author_list[] = htmlspecialchars($a['user_name']);
                            }
                            echo implode(', ', $author_list);
                        ?>
                    <?php else: ?>
                        <span style="color:#999;">ไม่พบข้อมูลผู้จัดทำ</span>
                    <?php endif; ?>
                </p>

                <!-- ครูที่ปรึกษา -->
                <?php if(!empty($p['advisor_main']) || !empty($p['advisor_co'])): ?>
                    <p><strong>ครูที่ปรึกษาโครงงาน:</strong> <?= htmlspecialchars($p['advisor_main'] ?? '-') ?></p>
                    <p><strong>ครูที่ปรึกษาร่วม:</strong> <?= htmlspecialchars($p['advisor_co'] ?? '-') ?></p>
                <?php endif; ?>


                <!-- บทคัดย่อ -->
                <?php if(!empty($p['abstract'])): ?>
                    <p><strong>บทคัดย่อ:</strong><br><?= nl2br(htmlspecialchars($p['abstract'])) ?></p>
                <?php endif; ?>

                <a href="<?= $user_logged_in ? 'PJ/view_pj.php?id='.htmlspecialchars($p['project_id']) : 'login.php' ?>">
                    <button class="<?= $user_logged_in ? 'btn-view' : 'btn-login' ?>">
                        <?= $user_logged_in ? 'ดูรายละเอียดโครงงาน' : 'เข้าสู่ระบบเพื่อดูรายละเอียด' ?>
                    </button>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.container { margin:20px; font-family: 'Sarabun', sans-serif; }
.project-card { 
    border:1px solid #ffc400; 
    padding:15px; 
    margin:15px 0; 
    border-radius:8px; 
    background:#fff; 
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
.project-card h3 { margin-top:0; }
.project-card h3 a { text-decoration:none; color:#000; }
.btn-view { 
    padding:10px 15px; 
    background:#4CAF50; 
    color:white; 
    border:none; 
    border-radius:8px; 
    cursor:pointer; 
    margin-top:10px;
}
.btn-login {
    padding:10px 15px; 
    background:#2196F3; 
    color:white; 
    border:none; 
    border-radius:8px; 
    cursor:pointer;  
    margin-top:10px;
}
.dept-badge{
    display: inline-block;
    padding: 6px 14px;
    margin-bottom: 8px;
    background: #2b96f5ff;
    color: #ffffffff;
    /* border: 1px solid #ffc400; */
    border-radius: 6px; /*ค่าเดิม 20 */
    font-size: 14px;
    font-weight: 600;
}

</style>

<?php include '../Inc/footer.php'; ?>
