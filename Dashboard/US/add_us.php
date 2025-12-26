<?php
require_once '../../DB/db.php';
require_once '../../Inc/header.php';

$departments = [
    "เทคโนโลยีสารสนเทศ","การบัญชี","คหกรรมศาสตร์","ช่างยนต์",
    "ช่างไฟฟ้ากำลัง","ช่างโยธา","ช่างอิเล็กทรอนิกส์",
    "ช่างเชื่อมโลหะ","ช่างกลโรงงาน","สามัญสัมพันธ์"
];

$roles = ['user','employee','admin','adminsupport'];

$error = $success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name       = trim($_POST['full_name'] ?? '');
    $student_id      = trim($_POST['student_id'] ?? '');
    $id_card         = trim($_POST['id_card'] ?? '');
    $birth_date      = $_POST['birth_date'] ?? '';
    $department      = $_POST['department'] ?? '';
    $education_level = $_POST['education_level'] ?? '';
    $role            = $_POST['role'] ?? '';
    $college         = trim($_POST['college'] ?? '');
    $email           = trim($_POST['email'] ?? '');

    if(!$full_name || !$birth_date || !$department || !$education_level || !$role){
        $error = "กรุณากรอกข้อมูลให้ครบ";
    }

    if(!$error){
        if($role === 'user'){
            if(!$student_id){
                $error = "กรุณากรอกรหัสนักศึกษา";
            } else {
                $email = strtolower($student_id).'@swdtcmail.com';
                $id_card = null;
            }
        } else {
            if(!$id_card || !$email){
                $error = "กรุณากรอกเลขบัตรประชาชนและ Email";
            }
            $student_id = null;
        }
    }

    if(!$error){
        $d = explode('-', $birth_date);
        if(count($d) === 3){
            $password_raw = $d[2].$d[1].$d[0]; // ddmmyyyy
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
        } else {
            $error = "รูปแบบวันเกิดไม่ถูกต้อง";
        }
    }

    if(!$error && $role !== 'user'){
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if($stmt->rowCount() > 0){
            $error = "Email นี้มีอยู่ในระบบแล้ว";
        }
    }

    if(!$error){
        $stmt = $conn->prepare("
            INSERT INTO users
            (full_name, student_id, id_card, birth_date, department,
             education_level, email, password, college, role)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $full_name,
            $student_id,
            $id_card,
            $birth_date,
            $department,
            $education_level,
            $email,
            $password,
            $college,
            $role
        ]);

        $success = "เพิ่มผู้ใช้สำเร็จ | Email: $email | รหัสผ่านเริ่มต้น: $password_raw";
    }
}
?>

<h2 style="margin:20px;">เพิ่มผู้ใช้</h2>

<?php if($error): ?><p style="color:red;"><?=$error?></p><?php endif; ?>
<?php if($success): ?><p style="color:green;"><?=$success?></p><?php endif; ?>

<form method="post" class="user-form">

    <!-- ชื่อ-นามสกุล -->
    <div class="form-row">
        <div class="form-field">
            <label>ชื่อ-นามสกุล :</label>
            <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <!-- รหัสนักศึกษา / เลขบัตรประชาชน -->
        <div class="form-field" id="student-field">
            <label>รหัสนักศึกษา / เลขบัตรประชาชน :</label>
            <input type="text" name="student_id" id="student_input" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
            <input type="text" name="id_card" id="idcard_input" value="<?= htmlspecialchars($_POST['id_card'] ?? '') ?>">
        </div>
    </div>

    <!-- วันเดือนปีเกิด / ระดับการศึกษา -->
    <div class="form-row">
        <div class="form-field">
            <label>วันเดือนปีเกิด :</label>
            <input type="date" name="birth_date" required value="<?= htmlspecialchars($_POST['birth_date'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label>ระดับการศึกษา :</label>
            <select name="education_level" required>
                <option value="ประกาศนียบัตรวิชาชีพ" <?= (($_POST['education_level'] ?? '')==='ประกาศนียบัตรวิชาชีพ') ? 'selected' : '' ?>>ปวช.</option>
                <option value="ประกาศนียบัตรวิชาชีพชั้นสูง" <?= (($_POST['education_level'] ?? '')==='ประกาศนียบัตรวิชาชีพชั้นสูง') ? 'selected' : '' ?>>ปวส.</option>
                <option value="อาจารย์" <?= (($_POST['education_level'] ?? '')==='อาจารย์') ? 'selected' : '' ?>>อาจารย์</option>
            </select>
        </div>
    </div>

    <!-- แผนก / ประเภทผู้ใช้ -->
    <div class="form-row">
        <div class="form-field">
            <label>แผนกวิชา :</label>
            <select name="department" required>
                <?php foreach($departments as $d): ?>
                    <option value="<?=$d?>" <?= (($_POST['department'] ?? '') === $d) ? 'selected' : '' ?>><?=$d?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label>ประเภทผู้ใช้ :</label>
            <select name="role" id="role" required>
                <?php foreach($roles as $r): ?>
                    <option value="<?=$r?>" <?= (($_POST['role'] ?? '') === $r) ? 'selected' : '' ?>><?=$r?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <!-- วิทยาลัย -->
        <div class="form-field">
            <label>วิทยาลัย :</label>
            <input type="text" name="college" value="<?= htmlspecialchars($_POST['college'] ?? 'วิทยาลัยเทคนิคสว่างแดนดิน') ?>" readonly>
        </div>

        <!-- Email -->
        <div class="form-field" id="email-field">
            <label>Email :</label><br>
            <input style="width: 98%; margin:3px 0 0;" type="email" name="email" id="email_input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
    </div>


    <button type="submit">เพิ่มผู้ใช้</button>
</form>

<style>
.user-form { max-width:700px; margin:20px auto; padding:20px; border:1px solid #ffc400; border-radius:10px; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.1); font-family:Sarabun,sans-serif; }
.form-row { display:flex; gap:20px; margin-bottom: 15px; flex-wrap:wrap; }
.form-field { display:flex; flex-direction:column; flex:1; min-width:150px; margin-bottom:-5px; }
.form-field label { margin-bottom:3px; font-weight:600; }
.form-field input, .form-field select { 
    padding:4px; 
    font-size:16px; 
    border-radius:5px; 
    border:1px solid #ccc; 
}
button { background:#ffc400; border:none; padding:8px 15px; margin-top: 14px; border-radius:8px; cursor:pointer; font-size:16px; }
</style>

<script>
const roleSelect = document.getElementById('role');
const studentInput = document.getElementById('student_input');
const idcardInput  = document.getElementById('idcard_input');
const emailField   = document.getElementById('email-field');

function toggleFields() {
    if(roleSelect.value === 'user'){
        studentInput.style.display = 'block';
        idcardInput.style.display  = 'none';
        emailField.style.display   = 'none';
    } else {
        studentInput.style.display = 'none';
        idcardInput.style.display  = 'block';
        emailField.style.display   = 'block';
    }
}

roleSelect.addEventListener('change', toggleFields);
window.addEventListener('load', toggleFields);
</script>

<?php include '../../Inc/footer.php'; ?>