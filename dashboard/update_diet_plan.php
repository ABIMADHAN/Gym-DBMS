<?php

require_once("../config/db.php");

// Check if the customer ID is provided
if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    // Fetch customer details
    $stmt = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        die("Customer not found.");
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dietPlan = $_POST['diet_plan'];

        // Update the diet plan in the database
        $stmt = $pdo->prepare("INSERT INTO diet_plans (customer_id, diet_plan, created_at) VALUES (?, ?, NOW())
                               ON DUPLICATE KEY UPDATE diet_plan = VALUES(diet_plan), created_at = NOW()");
        $stmt->execute([$customer_id, $dietPlan]);

        echo "Diet plan updated successfully!";
    }
} else {
    die("Customer ID is required.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Diet Plan</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #ff5722;
        }

        form {
            margin-top: 20px;
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #ff5722;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #e64a19;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Diet Plan for <?php echo htmlspecialchars($customer['name']); ?></h1>
        <form method="POST">
            <label for="diet_plan">Diet Plan:</label>
            <textarea name="diet_plan" id="diet_plan" required></textarea>
            <button type="submit">Update Diet Plan</button>
        </form>
        <a href="customer.php">Back to Dashboard</a>
    </div>
</body>
</html>