<?php
require_once '../DB/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = "";

// =======================
// ดึง email / password จาก cookie (ถ้ามี)
// =======================
$saved_email = $_COOKIE['remember_email'] ?? '';
$saved_pass  = $_COOKIE['remember_pass'] ?? '';

// =======================
// login ปกติ
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        $error = "กรุณากรอก Email และรหัสผ่านให้ครบถ้วน";
    } else {
        $stmt = $conn->prepare("
            SELECT user_id, full_name, email, password, role, department, profile_img
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user'] = [
                'user_id'    => $user['user_id'],
                'full_name'  => $user['full_name'],
                'email'      => $user['email'],
                'role'       => $user['role'],
                'department' => $user['department'],
                'profile_img'=> $user['profile_img'] ?? 'default.png'
            ];

            // =======================
            // จำ email + password ไว้ในฟอร์ม
            // =======================
            if ($remember) {
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                setcookie('remember_pass',  $password, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            } else {
                // ถ้าไม่ติ๊ก remember ให้ล้าง cookie
                setcookie('remember_email', '', time() - 3600, '/');
                setcookie('remember_pass',  '', time() - 3600, '/');
            }

            header("Location: index.php");
            exit;
        } else {
            $error = "Email หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
}
?>

<!-- ======= Wrapper Login ======= -->
<div class="login-wrapper">
    <form method="POST" class="login-form">
        <h2>เข้าสู่ระบบ</h2>

        <?php if($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <label>Email:</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? $saved_email) ?>"
               required>

        <label>รหัสผ่าน:</label>
        <input type="password" name="password"
               value="<?= htmlspecialchars($saved_pass) ?>"
               required>

        <div class="remember">
            <input type="checkbox" name="remember" id="remember"
                <?= ($saved_email && $saved_pass) ? 'checked' : '' ?>>
            <label for="remember">จำฉันไว้</label>
        </div>

        <button type="submit">เข้าสู่ระบบ</button>
    </form>
</div>

<style>
.login-wrapper {
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    background:#f0f2f5;
    font-family:Sarabun,sans-serif;
}

.login-form {
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    width:100%;
    max-width:400px;
    display:flex;
    flex-direction:column;
    gap:5px;
}

.login-form h2 {
    text-align:center;
    margin-bottom:15px;
}

.login-form label {
    font-weight:600;
}

.login-form input[type="email"],
.login-form input[type="password"] {
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
    font-size:16px;
}

.login-form .remember {
    display:flex;
    align-items:center;
    gap:5px;
    font-size:14px;
}

.login-form button {
    padding:10px;
    background:#b40000;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
}

.login-form button:hover {
    background:#d54545ff;
}

.error {
    color:red;
    font-weight:600;
    text-align:center;
}
</style>
