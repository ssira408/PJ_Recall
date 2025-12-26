<?php 
require_once '../../DB/db.php';
require_once '../../Inc/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) die("<div style='color:red;'>คุณต้องเข้าสู่ระบบก่อน</div>");

$position        = $_SESSION['user']['role'] ?? '';
$user_fullname   = $_SESSION['user']['full_name'] ?? '';
$user_department = $_SESSION['user']['department'] ?? '';

$departList = ["เทคโนโลยีสารสนเทศ","การบัญชี","คหกรรมศาสตร์","ช่างยนต์","ช่างไฟฟ้ากำลัง","ช่างโยธา","ช่างอิเล็กทรอนิกส์","ช่างเชื่อมโลหะ","ช่างกลโรงงาน","สามัญสัมพันธ์"];
$levelList  = ["ปวช.","ปวส."];

$error = $success = '';
$title_th = $title_en = $advisor_main = $advisor_co = $abstract = $objective = $method = $highlight = $benefit = $duration = $github_link = '';
$authors_input = $levels_input = $student_ids_input = [];
$uploaded_files = [];

$department = ($position === 'admin' || $position === 'adminsupport') ? '' : $user_department;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $department   = ($position === 'admin' || $position === 'adminsupport') ? trim($_POST['department'] ?? '') : $user_department;
    $title_th     = trim($_POST['title_th'] ?? '');
    $title_en     = trim($_POST['title_en'] ?? '');
    $advisor_main = trim($_POST['advisor_main'] ?? '');
    $advisor_co   = trim($_POST['advisor_co'] ?? '');
    $abstract     = trim($_POST['abstract'] ?? '');
    $objective    = trim($_POST['objective'] ?? '');
    $method       = trim($_POST['method'] ?? '');
    $highlight    = trim($_POST['highlight'] ?? '');
    $benefit      = trim($_POST['benefit'] ?? '');
    $duration     = trim($_POST['duration'] ?? '');
    $github_link  = trim($_POST['github_link'] ?? '');

    $authors_input     = $_POST['authors'] ?? [];
    $levels_input      = $_POST['levels'] ?? [];
    $student_ids_input = $_POST['student_ids'] ?? [];

    /* ===============================
       สร้าง project_code รูปแบบ YY0001
       =============================== */
    $yearShort = date('y'); // เช่น 2025 → 25

    $stmt = $conn->prepare("
        SELECT project_code 
        FROM projects 
        WHERE project_code LIKE :y 
        ORDER BY project_code DESC 
        LIMIT 1
    ");
    $stmt->execute([':y' => $yearShort . '%']);
    $lastCode = $stmt->fetchColumn();

    $nextNumber = $lastCode ? ((int)substr($lastCode, 2, 4) + 1) : 1;
    $project_code = $yearShort . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    // ตัวอย่าง: 250001

    /* ===============================
       อัปโหลดไฟล์
       =============================== */
    $uploadDir = __DIR__ . '/../../projects/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $allowed_extensions = ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar','jpg','jpeg','png','gif','mp4','mp3','html','css','js','php','json'];
    $max_file_size = 200 * 1024 * 1024;

    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['name'] as $i => $original_name) {
            if ($_FILES['files']['error'][$i] !== 0) continue;

            $ext  = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $size = $_FILES['files']['size'][$i];

            if (!in_array($ext, $allowed_extensions)) {
                $error = "ไฟล์ $original_name ไม่รองรับ";
                break;
            }
            if ($size > $max_file_size) {
                $error = "ไฟล์ $original_name ใหญ่เกิน 200MB";
                break;
            }

            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
            $newName  = uniqid() . "_" . $safeName . "." . $ext;

            if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $uploadDir . $newName)) {
                $uploaded_files[] = [
                    'file_name' => $newName,
                    'file_path' => '/projects/' . $newName,
                    'file_type' => $ext
                ];
            }
        }
    }

    if (!$error) {
        try {
            $conn->beginTransaction();

            /* ===============================
               INSERT projects (แก้ SQL ให้ถูก)
               =============================== */
            $stmt = $conn->prepare("
                INSERT INTO projects (
                    project_code, title_th, title_en, education_level, department,
                    advisor_main, advisor_co, objective, working_principle,
                    highlight, duration, abstract, benefit, college,
                    github_link, creator_id
                ) VALUES (
                    :project_code, :title_th, :title_en, :education_level, :department,
                    :advisor_main, :advisor_co, :objective, :working_principle,
                    :highlight, :duration, :abstract, :benefit, :college,
                    :github_link, :creator_id
                )
            ");

            $stmt->execute([
                ':project_code'       => $project_code,
                ':title_th'           => $title_th,
                ':title_en'           => $title_en,
                ':education_level'    => $levels_input[0] ?? '',
                ':department'         => $department,
                ':advisor_main'       => $advisor_main,
                ':advisor_co'         => $advisor_co,
                ':objective'          => $objective,
                ':working_principle'  => $method,
                ':highlight'          => $highlight,
                ':duration'           => $duration,
                ':abstract'           => $abstract,
                ':benefit'            => $benefit,
                ':college'            => 'วิทยาลัยเทคนิคสว่างแดนดิน',
                ':github_link'        => $github_link,
                ':creator_id'         => $_SESSION['user']['user_id']
            ]);

            $project_id = $conn->lastInsertId();

            /* ===============================
               ผู้จัดทำ
               =============================== */
            foreach ($authors_input as $i => $name) {
                if (!$name) continue;
                $stmt = $conn->prepare("
                    INSERT INTO project_users 
                    (project_id, user_name, student_id, education_level)
                    VALUES (:pid, :name, :sid, :level)
                ");
                $stmt->execute([
                    ':pid'   => $project_id,
                    ':name'  => $name,
                    ':sid'   => $student_ids_input[$i] ?? '',
                    ':level' => $levels_input[$i] ?? ''
                ]);
            }

            /* ===============================
               ไฟล์
               =============================== */
            foreach ($uploaded_files as $f) {
                $stmt = $conn->prepare("
                    INSERT INTO project_files 
                    (project_id, file_name, file_path, file_type)
                    VALUES (:pid, :fname, :fpath, :ftype)
                ");
                $stmt->execute([
                    ':pid'   => $project_id,
                    ':fname' => $f['file_name'],
                    ':fpath' => $f['file_path'],
                    ':ftype' => $f['file_type']
                ]);
            }

            $conn->commit();
            $success = "เพิ่มโครงการสำเร็จ (รหัสโครงการ: $project_code)";
        } catch (Exception $e) {
            $conn->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<h2 style="margin:20px;">เพิ่มโครงการ</h2>
<?php if($error): ?><p style="color:red;font-weight:bold;"><?=$error?></p><?php endif; ?>
<?php if($success): ?><p style="color:green;font-weight:bold;"><?=$success?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="project-form">
    <div class="form-row">
        <div>
            <label>ชื่อโครงการ (ไทย):</label>
            <input type="text" name="title_th" required value="<?=htmlspecialchars($title_th)?>">
        </div>
        <div>
            <label>ชื่อโครงการ (อังกฤษ):</label>
            <input type="text" name="title_en" value="<?=htmlspecialchars($title_en)?>">
        </div>
    </div>

    <label>ผู้จัดทำ:</label>
    <div id="authors-container">
        <div class="author-block">
            <input type="text" name="authors[]" value="<?=htmlspecialchars($user_fullname)?>" required placeholder="ชื่อผู้จัดทำ">
            <input type="text" name="student_ids[]" placeholder="รหัสนักศึกษา">
            <select name="levels[]">
                <option value="">-- เลือกระดับ --</option>
                <?php foreach($levelList as $lv): ?>
                    <option value="<?=$lv?>"><?=$lv?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="this.parentElement.remove()">ลบ</button>
        </div>
    </div>
    <button type="button" onclick="addAuthor()">เพิ่มผู้จัดทำ</button>

    <div class="form-row">
        <div>
            <label>ครูที่ปรึกษาโครงงาน:</label>
            <input type="text" name="advisor_main" required value="<?=htmlspecialchars($advisor_main)?>">
        </div>
        <div>
            <label>ครูที่ปรึกษาร่วม:</label>
            <input type="text" name="advisor_co" value="<?=htmlspecialchars($advisor_co)?>">
        </div>
    </div>

    <label>บทคัดย่อ:</label>
    <textarea name="abstract" rows="4" required><?=htmlspecialchars($abstract)?></textarea>
    <label>วัตถุประสงค์:</label>
    <textarea name="objective" rows="4" required><?=htmlspecialchars($objective)?></textarea>
    <label>วิธีดำเนินการ:</label>
    <textarea name="method" rows="4"><?=htmlspecialchars($method)?></textarea>
    <label>ขอบเขต:</label>
    <textarea name="highlight" rows="4"><?=htmlspecialchars($highlight)?></textarea>
    <label>ประโยชน์:</label>
    <textarea name="benefit" rows="4"><?=htmlspecialchars($benefit)?></textarea>

    <div class="form-row">   
        <div>
            <label>ระยะเวลา:</label>
            <input type="text" name="duration" value="<?=htmlspecialchars($duration)?>">
        </div>
        <div>
            <?php if($position==='admin'||$position==='adminsupport'): ?>
                <label>แผนกวิชา:</label>
                <select name="department" required>
                    <?php foreach($departList as $d): ?>
                        <option value="<?=$d?>" <?=($department===$d)?'selected':''?>><?=$d?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="department" value="<?=$user_department?>">
            <?php endif; ?>
        </div>
    </div>

    <label>GitHub Repository (ถ้ามี):</label>
    <input type="url" name="github_link" placeholder="https://github.com/username/project" value="<?=htmlspecialchars($github_link)?>">

    <label>ไฟล์โครงการ:</label>
    <input type="file" name="files[]" multiple id="projectFiles">
    <div id="fileNamesContainer"></div><br>

    <button type="submit">เพิ่มโครงการ</button>
</form>

<style>
.project-form{max-width:700px;margin:20px auto;padding:20px;border:1px solid #ffc400;border-radius:10px;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,0.1);font-family:Sarabun,sans-serif;}
.project-form label{display:block;margin:20px 10px 0;font-weight:600;}
.project-form input, .project-form select, .project-form textarea{width:100%;padding:8px;border-radius:5px;border:1px solid #ccc;}
.author-block{border:1px solid #ccc; padding:8px; border-radius:8px; margin-bottom:8px; display:flex; gap:5px;}
.author-block input, .author-block select{flex:1;}
.form-row { display: flex; gap: 20px; margin-bottom: 15px;}
.form-row > div { flex: 1; }
</style>

<script>
function addAuthor(){
    const container=document.getElementById('authors-container');
    const div=document.createElement('div');
    div.className='author-block';
    div.innerHTML=`
        <input type="text" name="authors[]" required placeholder="ชื่อผู้จัดทำ">
        <input type="text" name="student_ids[]" placeholder="รหัสนักศึกษา">
        <select name="levels[]">
            <option value="">-- เลือกระดับ --</option>
            <?php foreach($levelList as $lv): ?>
            <option value="<?=$lv?>"><?=$lv?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" onclick="this.parentElement.remove()">ลบ</button>
    `;
    container.appendChild(div);
}

const fileInput=document.getElementById('projectFiles');
const fileContainer=document.getElementById('fileNamesContainer');
fileInput.addEventListener('change', function(){
    fileContainer.innerHTML='';
    for(let i=0;i<fileInput.files.length;i++){
        const f=fileInput.files[i];
        const div=document.createElement('div');
        div.className='author-block';
        div.innerHTML=`<p>ไฟล์: ${f.name}</p><input type="text" name="file_names[]" placeholder="ตั้งชื่อไฟล์ (ไม่บังคับ)">`;
        fileContainer.appendChild(div);
    }
});
</script>

<?php include '../../Inc/footer.php'; ?>
