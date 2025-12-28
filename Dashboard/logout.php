<?php
// เริ่ม session
session_start();

// =======================
// ล้างเฉพาะ password ที่จำไว้
// (คง email เอาไว้)
// =======================
if (isset($_COOKIE['remember_pass'])) {
    setcookie('remember_pass', '', time() - 3600, '/');
}

// ❗ intentionally ไม่ลบ remember_email

// =======================
// ลบ session ทั้งหมด
// =======================
$_SESSION = array();

// ถ้ามี cookie session ให้ลบด้วย
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// ทำลาย session
session_destroy();

// ไปยังหน้า login หลังจาก logout
header("Location: index.php");
exit;
?>
