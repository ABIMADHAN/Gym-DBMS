<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gym Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('images/gym-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            text-align: center;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: rgba(0,0,0,0.5);
            border-radius: 12px;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 15px;
            background-color: #ff5722;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #e64a19;
        }

        .logo {
            width: 120px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="images/gym-logo.png" alt="Gym Logo" class="logo">
        <h1>Welcome to the Gym Membership System</h1>
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </div>
</body>
</html>

