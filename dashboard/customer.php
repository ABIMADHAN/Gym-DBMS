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
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../images/dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        .dashboard-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.75); /* Transparent dark panel */
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
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

        .card {
            background-color: #ff5722;
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: scale(1.05);
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

<div class="dashboard-container">
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

</body>
</html>