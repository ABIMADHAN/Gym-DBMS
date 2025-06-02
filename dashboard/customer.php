<?php
require_once("../includes/header.php");
if (!isCustomer()) exit();
require_once("../config/db.php"); // Update path to db.php

$customer_id = $_SESSION['user']['id'];

// Option 2: Get diet plan from diet_plans table (recommended)
$stmt = $pdo->prepare("SELECT diet_plan FROM diet_plans WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$customer_id]);
$dietResult = $stmt->fetch(PDO::FETCH_ASSOC);
$dietPlan = $dietResult ? $dietResult['diet_plan'] : 'No diet plan available yet.';

// Fetch customers data
$stmt = $pdo->prepare("SELECT customer_id, name, email, phone, created_at FROM customers");
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <style>
        /* Background and visual enhancements */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            /* Enhanced background image with lighter overlay */
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('../images/customer-dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            position: relative;
            overflow-x: hidden;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        
        /* Reduced opacity and added more vibrant colors to gradient overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg, 
                rgba(33, 150, 243, 0.2) 0%, 
                rgba(0, 0, 0, 0) 50%, 
                rgba(156, 39, 176, 0.2) 100%
            );
            z-index: -1;
            animation: gradientShift 15s ease infinite;
            backdrop-filter: none;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .customer-profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 60px;
            object-fit: cover;
            border: 3px solid #4dd0e1;
            box-shadow: 0 0 15px rgba(77, 208, 225, 0.5);
            margin: 0 auto 20px auto;
            display: block;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .customer-profile-pic:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(77, 208, 225, 0.8);
        }
        
        /* Dashboard accent elements */
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
            background: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            /* Remove backdrop-filter to avoid blur affecting content */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .customer-welcome {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .customer-welcome h2 {
            color: #4dd0e1;
            font-size: 2.2em;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .customer-welcome p {
            color: #f5f5f5;
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        h2 {
            text-align: center;
            color: #ffc107;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        /* Card styling enhancement for better contrast with background */
        .card {
            background-color: rgba(255, 87, 34, 0.85);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card:hover {
            transform: translateY(-5px) scale(1.05);
            background-color: rgba(255, 87, 34, 1);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }

        a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Background accent elements -->
    <div class="dashboard-bg-accent" style="top: 10%; left: 5%;"></div>
    <div class="dashboard-bg-accent" style="top: 60%; right: 5%; animation-delay: 2s;"></div>
    
    <div class="dashboard-container">
        <div class="customer-welcome">
            <img src="../images/customer-profile.jpg" alt="Customer Profile" class="customer-profile-pic">
            <h2>Welcome, <?php echo isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : 'Customer'; ?>!</h2>
            <p>Manage your fitness journey, track progress, and book sessions all in one place.</p>
        </div>
        
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></h2>
        <div class="cards">
            <div class="card"><a href="../workout/view_plan.php">Workout Week Plan</a></div>
            <div class="card"><a href="../slots/book_slot.php">Book a Slot</a></div>
            <div class="card"><a href="../progress/add_progress.php">Add Progress</a></div>
            <div class="card">
                <a href="view_diet_plan.php?customer_id=<?php echo $customer_id; ?>">Diet Plan</a>
            </div>
            <div class="card">
                <a href="fee_details.php?customer_id=<?php echo $customer_id; ?>">PayFee</a>
            </div>
            <div class="card">
                <a href="submit_feedback.php?customer_id=<?php echo $customer_id; ?>">Submit Feedback</a>
            </div>
        </div>
    </div>
    
    <script>
        // Any additional JavaScript if needed
    </script>
</body>
</html>