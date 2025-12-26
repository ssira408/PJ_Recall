<?php
// auth.php - ตรวจสอบการเข้าสู่ระบบและกำหนดสิทธิ์ผู้ใช้
if (session_status() === PHP_SESSION_NONE) session_start();

// ถ้ายังไม่มี session ของผู้ใช้
if (!isset($_SESSION['user'])) {
    // ลองตรวจสอบ cookie "จำฉันไว้"
    if (isset($_COOKIE['remember_me'])) {
        require_once __DIR__ . '/../DB/db.php'; // เชื่อมต่อฐานข้อมูล
        $token = $_COOKIE['remember_me'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user'] = [
                'id'         => $user['user_id'],
                'email'      => $user['email'],
                'fullname'   => $user['full_name'],
                'role'       => $user['role'],
                'department' => $user['department'] ?? ''
            ];
        } else {
            // token ไม่ถูกต้อง, ลบ cookie
            setcookie('remember_me', '', time() - 3600, "/", "", false, true);
            header("Location: ../login.php");
            exit;
        }
    } else {
        // ยังไม่มี session และไม่มี cookie
        header("Location: ../login.php");
        exit;
    }
}

// กำหนดตัวแปรสะดวกสำหรับผู้ใช้
$user_role       = $_SESSION['user']['role'] ?? '';
$user_department = $_SESSION['user']['department'] ?? '';
$user_email      = $_SESSION['user']['email'] ?? '';
$user_fullname   = $_SESSION['user']['fullname'] ?? $user_email;

// ตรวจสอบสิทธิ์แต่ละระดับ
$is_admin        = ($user_role === 'admin' || $user_role === 'adminsupport');
$is_employee     = ($user_role === 'employee');
$is_user         = ($user_role === 'user');
?>
