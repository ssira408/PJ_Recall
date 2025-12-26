<?php
if (session_status() == PHP_SESSION_NONE) session_start();

/**
 * ฟังก์ชันตรวจสอบว่า user login แล้วหรือไม่
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * ฟังก์ชันตรวจสอบ role ของผู้ใช้
 * @param string $role
 * @return bool
 */
function hasRole($role){
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
}

/**
 * Redirect ไปหน้าอื่น
 * @param string $url
 */
function redirect($url){
    header("Location: $url");
    exit;
}

/**
 * ฟังก์ชัน sanitize input
 */
function e($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * แปลงวันที่เป็น dd/mm/yyyy
 */
function formatDateThai($date){
    $timestamp = strtotime($date);
    return date("d/m/Y", $timestamp);
}

/**
 * สร้าง password จากวันเกิด (yyyy-mm-dd) เช่น 2000-05-12 => 12052000
 */
function passwordFromBirth($birthdate){
    $parts = explode('-', $birthdate);
    if(count($parts) === 3){
        return $parts[2] . $parts[1] . $parts[0];
    }
    return '';
}
