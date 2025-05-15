<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config/db.php");

// Get the customer ID from the URL
if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    // Fetch the customer's report
    $stmt = $pdo->prepare("SELECT weight, height, activity_level, goal FROM customer_reports WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$customer_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($report) {
        // Extract report details
        $weight = $report['weight']; // in kg
        $height = $report['height']; // in cm
        $activity_level = $report['activity_level'];
        $goal = $report['goal'];

        // Calculate Basal Metabolic Rate (BMR) using the Mifflin-St Jeor Equation
        $bmr = 10 * $weight + 6.25 * $height - 5 * 25; // Assuming age = 25 for simplicity

        // Adjust BMR based on activity level
        if ($activity_level === 'low') {
            $calories = $bmr * 1.2; // Sedentary
        } elseif ($activity_level === 'moderate') {
            $calories = $bmr * 1.55; // Moderate activity
        } elseif ($activity_level === 'high') {
            $calories = $bmr * 1.9; // Very active
        } else {
            $calories = $bmr * 1.2; // Default to sedentary
        }

        // Adjust calories based on the goal
        if ($goal === 'weight_loss') {
            $calories -= 500; // Caloric deficit
        } elseif ($goal === 'muscle_gain') {
            $calories += 500; // Caloric surplus
        }

        // Generate a diet plan based on caloric needs
        $dietPlan = "Your daily caloric intake should be approximately " . round($calories) . " kcal.\n\n";
        $dietPlan .= "Here is your personalized diet plan:\n";

        // Adjust the diet plan based on the goal
        if ($goal === 'weight_loss') {
            $dietPlan .= "🍳 Breakfast: Egg white omelet with spinach and a slice of whole-grain toast\n";
            $dietPlan .= "🍌 Morning Snack: A small apple and a handful of walnuts\n";
            $dietPlan .= "🍗 Lunch: Grilled chicken salad with mixed greens, cucumbers, and olive oil dressing\n";
            $dietPlan .= "🥕 Afternoon Snack: Carrot sticks with hummus\n";
            $dietPlan .= "🍣 Dinner: Baked salmon with steamed broccoli and quinoa\n";
            $dietPlan .= "🥒 Evening Snack: Low-fat Greek yogurt with a sprinkle of chia seeds\n";
        } elseif ($goal === 'muscle_gain') {
            $dietPlan .= "🍳 Breakfast: Scrambled eggs with avocado and whole-grain toast\n";
            $dietPlan .= "🍌 Morning Snack: A protein shake with a banana\n";
            $dietPlan .= "🍗 Lunch: Grilled chicken breast with brown rice and roasted vegetables\n";
            $dietPlan .= "🥜 Afternoon Snack: Peanut butter on whole-grain crackers\n";
            $dietPlan .= "🍖 Dinner: Steak with sweet potatoes and asparagus\n";
            $dietPlan .= "🥛 Evening Snack: Cottage cheese with a handful of almonds\n";
        } else { // Maintenance
            $dietPlan .= "🍳 Breakfast: Oatmeal with fresh berries and a drizzle of honey\n";
            $dietPlan .= "🍌 Morning Snack: A boiled egg and a piece of fruit\n";
            $dietPlan .= "🍗 Lunch: Turkey sandwich on whole-grain bread with a side salad\n";
            $dietPlan .= "🥜 Afternoon Snack: A handful of mixed nuts\n";
            $dietPlan .= "🍣 Dinner: Grilled fish with roasted vegetables and quinoa\n";
            $dietPlan .= "🥛 Evening Snack: Low-fat yogurt with granola\n";
        }
    } else {
        $dietPlan = "No report available for this customer.";
    }

    // Fetch customer details
    $stmt = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        $customerName = "Unknown Customer";
    } else {
        $customerName = $customer['name'];
    }
} else {
    die("Customer ID is required.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Diet Plan</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
            position: relative;
        }

        h1 {
            text-align: center;
            color: #ff5722;
            animation: bounce 1.5s infinite;
        }

        .diet-plan {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            white-space: pre-wrap; /* Preserve line breaks */
            animation: slideIn 1s ease-in-out;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #ff5722;
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Confetti Animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #ff5722;
            animation: fall 3s linear infinite;
        }

        @keyframes fall {
            from {
                transform: translateY(-100px) rotate(0deg);
            }
            to {
                transform: translateY(800px) rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 Diet Plan for <?php echo htmlspecialchars($customerName); ?> 🎉</h1>
        <div class="diet-plan">
            <?php echo nl2br(htmlspecialchars($dietPlan)); ?>
        </div>
        <a href="customer.php" class="back-link">⬅️ Back to Dashboard</a>
    </div>

    <!-- Confetti Animation -->
    <script>
        const container = document.querySelector('.container');

        // Generate confetti
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.classList.add('confetti');
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.backgroundColor = getRandomColor();
            confetti.style.animationDelay = Math.random() * 3 + 's';
            container.appendChild(confetti);
        }

        // Random color generator
        function getRandomColor() {
            const colors = ['#ff5722', '#4caf50', '#2196f3', '#ffc107', '#e91e63'];
            return colors[Math.floor(Math.random() * colors.length)];
        }
    </script>
</body>
</html>