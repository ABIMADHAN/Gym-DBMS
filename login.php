<?php
require_once("config/db.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user details from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Store user details in the session
        $_SESSION['user'] = $user;

        // Redirect based on the user's role
        if ($user['role'] === 'trainer') {
            header("Location: dashboard/trainer.php");
        } elseif ($user['role'] === 'customer') {
            header("Location: dashboard/customer.php");
        } else {
            echo "Invalid role. Please contact the administrator.";
        }
        exit();
    } else {
        // Invalid credentials
        echo "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Gym Membership</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Optional -->
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                        url('images/gym-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: white;
        }

        .form-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 10px #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            font-size: 15px;
        }

        input {
            background: #f5f5f5;
            color: #333;
        }

        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #ffcc00;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <form class="form-container" action="" method="POST">
        <h2>Login</h2>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <div class="link">Don't have an account? <a href="register.php">Register</a></div>
    </form>

</body>
</html>
