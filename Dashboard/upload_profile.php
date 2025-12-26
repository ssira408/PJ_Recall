<?php
require '../DB/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) exit;

$user_id = $_SESSION['user']['user_id'];

if($_SERVER['CONTENT_TYPE']==='application/json'){
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($data['image'])){
        $img = str_replace('data:image/png;base64,','',$data['image']);
        $img = base64_decode($img);

        $name = 'user_'.$user_id.'_'.time().'.png';
        file_put_contents('../uploads/'.$name,$img);

        $conn->prepare(
            "UPDATE users SET profile_img=? WHERE user_id=?"
        )->execute([$name,$user_id]);

        $_SESSION['user']['profile_img']=$name;
        exit;
    }
}

if (!empty($_FILES['profile_img']['name'])) {

    $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
    $new_name = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $upload_dir = '../uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['profile_img']['tmp_name'],
        $upload_dir . $new_name)) {

        // ลบรูปเก่า
        if (!empty($_SESSION['user']['profile_img']) &&
            $_SESSION['user']['profile_img'] !== 'default.png') {

            @unlink($upload_dir . $_SESSION['user']['profile_img']);
        }

        $stmt = $conn->prepare(
            "UPDATE users SET profile_img=? WHERE user_id=?"
        );
        $stmt->execute([$new_name, $user_id]);

        $_SESSION['user']['profile_img'] = $new_name;
    }
}

header("Location: ".$_SERVER['HTTP_REFERER']);
