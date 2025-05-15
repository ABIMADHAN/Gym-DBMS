<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Start session and redirect
        session_start();
        $_SESSION['user'] = $user;
        header("Location: ../dashboard/customer.php");
        exit();
    } else {
        echo "Invalid email or password.";
    }
}
?>
