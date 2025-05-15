<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Gym Membership</title>
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

        input, select, button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            font-size: 15px;
        }

        input, select {
            background: #f5f5f5;
            color: #333;
        }

        button {
            background-color: #ff5722;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #e64a19;
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

    <form class="form-container" action="actions/register.php" method="POST">
        <h2>Create Account</h2>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="customer">Customer</option>
            <option value="trainer">Trainer</option>
        </select>
        <button type="submit">Register</button>
        <div class="link">Already have an account? <a href="login.php">Login</a></div>
    </form>

</body>
</html>
