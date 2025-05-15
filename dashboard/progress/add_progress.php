<?php
require_once("../config/db.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $activity_level = $_POST['activity_level']; // e.g., 'low', 'moderate', 'high'
    $goal = $_POST['goal']; // e.g., 'weight_loss', 'muscle_gain', 'maintenance'

    // Debugging: Check the values being passed
    var_dump($customer_id, $weight, $height, $activity_level, $goal);

    // Insert progress into the progress table
    try {
        $stmt = $pdo->prepare("
            INSERT INTO progress (
                customer_id, weight, body_fat, muscle_mass, waist, hip, chest, heart_rate, workout_freq, energy, sleep, water, mood, note, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $customer_id, 
            $weight, 
            null, // body_fat
            null, // muscle_mass
            null, // waist
            null, // hip
            null, // chest
            null, // heart_rate
            null, // workout_freq
            null, // energy
            null, // sleep
            null, // water
            null, // mood
            null  // note
        ]);
        echo "Progress added successfully.<br>";
    } catch (PDOException $e) {
        echo "Error inserting into progress table: " . $e->getMessage();
        exit();
    }

    // Update or insert into the customer_reports table
    try {
        $stmt = $pdo->prepare("
            INSERT INTO customer_reports (customer_id, weight, height, activity_level, goal, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            weight = VALUES(weight),
            height = VALUES(height),
            activity_level = VALUES(activity_level),
            goal = VALUES(goal),
            created_at = NOW()
        ");
        $stmt->execute([$customer_id, $weight, $height, $activity_level, $goal]);
        echo "Customer report updated successfully.<br>";
    } catch (PDOException $e) {
        echo "Error updating customer_reports: " . $e->getMessage();
        exit();
    }

    // Analyze the report and generate a diet plan
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
    $dietPlan .= "🍳 Breakfast: Scrambled eggs with spinach and whole-grain toast\n";
    $dietPlan .= "🍌 Morning Snack: A handful of almonds and a banana\n";
    $dietPlan .= "🍗 Lunch: Grilled chicken breast with quinoa and steamed broccoli\n";
    $dietPlan .= "🍓 Afternoon Snack: Greek yogurt with honey and berries\n";
    $dietPlan .= "🍣 Dinner: Baked salmon with sweet potatoes and asparagus\n";
    $dietPlan .= "🥒 Evening Snack: Cottage cheese with cucumber slices\n";

    // Output the diet plan
    echo nl2br($dietPlan);
}
?>