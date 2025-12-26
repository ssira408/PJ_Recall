<?php
require '../../DB/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

include '../../inc/header.php';

// ===== ตรวจสอบสิทธิ์ user =====
$user = $_SESSION['user'] ?? null;
if (!$user) {
    die("<p style='color:red;'>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>");
}

// ===== รับ project_id =====
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$project_id) die("<p style='color:red;'>ไม่พบโครงการ</p>");

// ===== ดึงข้อมูลโครงการ =====
$stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = :project_id");
$stmt->execute(['project_id' => $project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) die("<p style='color:red;'>ไม่พบโครงการนี้</p>");

// ===== ตรวจสอบสิทธิ์แก้ไข =====
$can_edit = false;
switch ($user['role']) {
    case 'admin':
    case 'admin support':
        $can_edit = true;
        break;
    case 'employee':
        $authors_list = explode(',', $project['author'] ?? '');
        $author_names = array_map(fn($a)=> explode('||',$a)[0], $authors_list);
        if ($project['department'] === $user['department'] || in_array($user['fullname'], $author_names)) {
            $can_edit = true;
        }
        break;
    case 'user':
        $authors_list = explode(',', $project['author'] ?? '');
        $author_names = array_map(fn($a)=> explode('||',$a)[0], $authors_list);
        if (in_array($user['fullname'], $author_names)) $can_edit = true;
        break;
}

if (!$can_edit) die("<p style='color:red;'>คุณไม่มีสิทธิ์แก้ไขโครงการนี้</p>");

// ===== ตรวจสอบสถานะอนุมัติ =====
$approved = $project['status'] === 'approved';

$departList = [
    "เทคโนโลยีสารสนเทศ","การบัญชี","คหกรรมศาสตร์","ช่างยนต์","ช่างไฟฟ้ากำลัง",
    "ช่างโยธา","ช่างอิเล็กทรอนิกส์","ช่างเชื่อมโลหะ","ช่างกลโรงงาน","สามัญสัมพันธ์"
];
$levelList = ["ปวช.","ปวส."];
$allowed_extensions = ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar','jpg','jpeg','png','gif','bmp','html','css','js','php','py','java','c','cpp','rb','go','sh'];
$max_file_size = 50*1024*1024;

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$approved) {
    // ===== รับข้อมูล form =====
    $title_th = trim($_POST['title_th'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $advisor_main = trim($_POST['advisor_main'] ?? '');
    $advisor_co = trim($_POST['advisor_co'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $objective = trim($_POST['objective'] ?? '');
    $benefit = trim($_POST['benefit'] ?? '');
    $department = trim($_POST['department'] ?? '');

    // ===== ประมวลผลผู้จัดทำ =====
    $authors_input = $_POST['authors'] ?? [];
    $student_ids_input = $_POST['student_ids'] ?? [];
    $levels_input = $_POST['levels'] ?? [];
    $authors_arr = [];
    foreach($authors_input as $i => $author){
        $author = trim($author);
        $sid = trim($student_ids_input[$i] ?? '');
        $level = $levels_input[$i] ?? '';
        if ($author) $authors_arr[] = $author . ($level ? "||$level" : "") . ($sid ? "||$sid" : "");
    }

    // ===== อัปโหลดไฟล์ =====
    $uploaded_files = [];
    if(isset($_FILES['files']) && $_FILES['files']['name'][0] !== ''){
        foreach($_FILES['files']['name'] as $key => $original_name){
            $tmp_name = $_FILES['files']['tmp_name'][$key];
            $error_code = $_FILES['files']['error'][$key];
            $size = $_FILES['files']['size'][$key];

            if($error_code === 0){
                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                if(!in_array($ext, $allowed_extensions)){
                    $error = "ไฟล์ $original_name ไม่รองรับชนิดไฟล์นี้";
                    break;
                }
                if($size > $max_file_size){
                    $error = "ไฟล์ $original_name มีขนาดเกิน 50MB";
                    break;
                }
                $new_name = uniqid() . "_" . preg_replace('/[^A-Za-z0-9_\-]/','_',pathinfo($original_name,PATHINFO_FILENAME)).".".$ext;
                $dest = '../../projects/'.$new_name;
                if(move_uploaded_file($tmp_name,$dest)) $uploaded_files[] = $new_name;
                else { $error = "อัปโหลดไฟล์ $original_name ไม่สำเร็จ"; break; }
            }
        }
    } else {
        $uploaded_files = explode(',', $project['file'] ?? '');
    }

    // ===== UPDATE ฐานข้อมูล =====
    if(!$error){
        $stmt = $pdo->prepare("
            UPDATE projects SET
                title_th=:title_th,
                title_en=:title_en,
                author=:author,
                advisor_main=:advisor_main,
                advisor_co=:advisor_co,
                abstract=:abstract,
                objective=:objective,
                benefit=:benefit,
                file=:file,
                department=:department
            WHERE project_id=:project_id
        ");
        $stmt->execute([
            'title_th'=>$title_th,
            'title_en'=>$title_en,
            'author'=>implode(',', $authors_arr),
            'advisor_main'=>$advisor_main,
            'advisor_co'=>$advisor_co,
            'abstract'=>$abstract,
            'objective'=>$objective,
            'benefit'=>$benefit,
            'file'=>implode(',', $uploaded_files),
            'department'=>$department,
            'project_id'=>$project_id
        ]);
        $success = "แก้ไขโครงการเรียบร้อยแล้ว!";

        // รีเฟรชข้อมูล
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = :project_id");
        $stmt->execute(['project_id'=>$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<h2 style="margin:20px;">แก้ไขโครงการ</h2>
<?php if($approved): ?>
    <p style="color:blue; font-weight:bold;">โครงการนี้ได้รับการอนุมัติแล้ว ไม่สามารถแก้ไขได้</p>
<?php endif; ?>
<?php if($error): ?><p style="color:red; font-weight:bold;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green; font-weight:bold;"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<?php if(!$approved): ?>
    
<form method="POST" enctype="multipart/form-data" style="margin:20px; margin-bottom:20px; padding:10px; border:1px solid #ffc400; border-radius:8px;">
    <div style="margin:20px;">
    <label>ชื่อโครงการ (ไทย):</label><br>
    <input type="text" name="title_th" value="<?= htmlspecialchars($project['title_th']) ?>" required><br><br>

    <label>ชื่อโครงการ (อังกฤษ):</label><br>
    <input type="text" name="title_en" value="<?= htmlspecialchars($project['title_en']) ?>"><br><br>

    <label>ผู้จัดทำ:</label><br>
    <div id="authors-container">
        <?php 
        $authors_list = explode(',', $project['author'] ?? '');
        foreach($authors_list as $a): 
            $parts = explode('||',$a);
            $name = $parts[0] ?? '';
            $level = $parts[1] ?? '';
            $sid = $parts[2] ?? '';
        ?>
        <div class="author-block">
            <input type="text" name="authors[]" value="<?= htmlspecialchars($name) ?>" required>
            <input type="text" name="student_ids[]" value="<?= htmlspecialchars($sid) ?>" placeholder="รหัสนักศึกษา">
            <select name="levels[]">
                <option value="">-- เลือกระดับ --</option>
                <?php foreach($levelList as $l): ?>
                <option value="<?= $l ?>" <?= $level==$l?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="removeAuthor(this)">ลบ</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" onclick="addAuthor()">เพิ่มผู้จัดทำ</button><br><br>

    <label>ครูที่ปรึกษาหลัก:</label><br>
    <input type="text" name="advisor_main" value="<?= htmlspecialchars($project['advisor_main']) ?>" required><br><br>

    <label>ครูที่ปรึกษาร่วม:</label><br>
    <input type="text" name="advisor_co" value="<?= htmlspecialchars($project['advisor_co']) ?>"><br><br>

    <label>บทคัดย่อ:</label><br>
    <textarea name="abstract" rows="4"><?= htmlspecialchars($project['abstract']) ?></textarea><br><br>

    <label>วัตถุประสงค์:</label><br>
    <textarea name="objective" rows="4"><?= htmlspecialchars($project['objective']) ?></textarea><br><br>

    <label>ประโยชน์ที่คาดว่าจะได้รับ:</label><br>
    <textarea name="benefit" rows="4"><?= htmlspecialchars($project['benefit']) ?></textarea><br><br>

    <label>แผนก:</label><br>
    <select name="department">
        <?php foreach($departList as $d): ?>
        <option value="<?= htmlspecialchars($d) ?>" <?= $project['department']==$d?'selected':'' ?>><?= htmlspecialchars($d) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>ไฟล์โครงการ:</label><br>
    <input type="file" name="files[]" multiple>
    <p>ไฟล์เดิม: <?= htmlspecialchars($project['file'] ?? '') ?></p><br><br>

    <button type="submit">บันทึกการแก้ไข</button>
    </div>
</form>

<form method="POST" action="cancel_pj.php" style="margin-top:3px;">
    <input type="hidden" name="project_id" value="<?= $project_id ?>">
    <button type="submit" style="background-color:#f44336; color:white; padding:8px 15px; border:none; border-radius:8px; margin:0 20px 0;">
        ยกเลิกการยื่นโครงการ
    </button>
</form>
<?php endif; ?>

<style>
.author-block {
    border:1px solid #ccc;
    padding:10px;
    border-radius:8px;
    margin-bottom:8px;
    max-width:350px;
}
.author-block input, .author-block select {
    width:70%;
    margin-bottom:5px;
    padding:6px;
    border-radius:8px;
    border:1px solid #ccc;
}
.author-block button {
    margin-top:5px;
    padding:8px 15px;
    background-color:#f44336;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
.author-block button:hover {
    background-color:#d32f2f;
}
</style>

<script>
function addAuthor(){
    const container = document.getElementById('authors-container');
    const div = document.createElement('div');
    div.className = 'author-block';
    div.innerHTML = `
        <input type="text" name="authors[]" required placeholder="ชื่อผู้จัดทำ">
        <input type="text" name="student_ids[]" placeholder="รหัสนักศึกษา">
        <select name="levels[]">
            <option value="">-- เลือกระดับ --</option>
            <?php foreach($levelList as $l): ?>
            <option value="<?= $l ?>"><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" onclick="removeAuthor(this)">ลบ</button>
    `;
    container.appendChild(div);
}
function removeAuthor(btn){
    btn.parentElement.remove();
}
</script>

<?php include '../../inc/footer.php'; ?>
