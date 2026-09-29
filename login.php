<?php
session_start();
include "db.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $email = $_POST['email'];
    $password = $_POST['password'];

    // VALIDATION
    if(empty($email) || empty($password)){
        header("Location: login.html?error=empty");
        exit();
    }

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

if($user['role'] == 'admin'){
    header("Location: admin_dashboard.php");
}
elseif($user['role'] == 'staff'){
    header("Location: staff_dashboard.php");
}
else{
    header("Location: dashboard.php");
}
exit();

    } else {
        header("Location: login.html?error=invalid");
        exit();
    }
}
?>