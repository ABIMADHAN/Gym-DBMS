<?php
require_once("../includes/header.php");
// Add your authentication checks here
require_once("../includes/db.php");

// Add any PHP logic you need for the dashboard here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../images/user-dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Add animated gradient overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg, 
                rgba(76, 175, 80, 0.2) 0%, 
                rgba(0, 0, 0, 0) 50%, 
                rgba(255, 152, 0, 0.2) 100%
            );
            z-index: -1;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Dashboard image animation */
        .dashboard-bg-accent {
            position: fixed;
            width: 300px;
            height: 300px;
            background: url('../images/gym-icon.png') no-repeat center center;
            background-size: contain;
            opacity: 0.05;
            z-index: -1;
            filter: blur(2px);
            animation: float 10s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }
        
        .dashboard-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-bg-accent" style="top: 10%; left: 5%;"></div>
    <div class="dashboard-bg-accent" style="top: 60%; right: 5%; animation-delay: 2s;"></div>
    
    <div class="dashboard-container">
        <h2>👋 Welcome to Your Dashboard!</h2>
        <!-- Your dashboard content goes here -->
    </div>
    
    <script>
        // Add any JavaScript you need
    </script>
</body>
</html>
