<?php
include "db.php";

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

if(empty($name) || empty($email) || empty($password)){
    echo "
    <script>
        alert('All fields are required. Please fill in all fields.');
        window.history.back();
    </script>
    ";
    exit();
}

// Check if email already exists (using prepared statement to prevent SQL injection)
$check = "SELECT * FROM users WHERE email=?";
$stmt = mysqli_prepare($conn, $check);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) > 0){
    echo "
    <script>
        alert('Email already exists. Please use a different email address.');
        window.history.back();
    </script>
    ";
    exit();
}

// Insert new user (using prepared statement)
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $password);

if(mysqli_stmt_execute($stmt)){
    echo "
    <script>
        alert('User account created successfully!');
        window.location.href='login.html';
    </script>
    ";
} else {
    echo "
    <script>
        alert('Registration failed. Please try again later.');
        window.history.back();
    </script>
    ";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>