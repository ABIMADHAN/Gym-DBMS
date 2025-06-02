<?php
require_once("../includes/header.php");
if (!isTrainer() && !isAdmin()) exit(); // Only allow trainers or admins
require_once("../includes/db.php");

$success = false;
$message = "";

try {
    // Check if table already exists
    $tableExists = false;
    try {
        $check = $pdo->query("SELECT 1 FROM customer_special_fees LIMIT 1");
        $tableExists = true;
        $message = "Table customer_special_fees already exists.";
    } catch (PDOException $e) {
        $tableExists = false;
    }

    if (!$tableExists) {
        // Create the table
        $pdo->exec("
            CREATE TABLE customer_special_fees (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                basic_plan_fee DECIMAL(10,2),
                standard_plan_fee DECIMAL(10,2),
                premium_plan_fee DECIMAL(10,2),
                yearly_plan_fee DECIMAL(10,2),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY (customer_id)
            )
        ");
        
        // Add special pricing for customer ID 4
        $stmt = $pdo->prepare("
            INSERT INTO customer_special_fees 
            (customer_id, basic_plan_fee, standard_plan_fee, premium_plan_fee, yearly_plan_fee)
            VALUES (?, 5, 5, 10, 99)
        ");
        $stmt->execute([4]);
        
        $message = "Table customer_special_fees created successfully and special pricing added for customer ID 4.";
        $success = true;
    } else {
        // Table exists, try to add yearly_plan_fee column if it doesn't exist
        try {
            $columnCheck = $pdo->query("SHOW COLUMNS FROM customer_special_fees LIKE 'yearly_plan_fee'");
            if ($columnCheck->rowCount() == 0) {
                $pdo->exec("ALTER TABLE customer_special_fees ADD COLUMN yearly_plan_fee DECIMAL(10,2) AFTER premium_plan_fee");
                
                // Update existing special fee for customer ID 4
                $stmt = $pdo->prepare("
                    UPDATE customer_special_fees 
                    SET yearly_plan_fee = 99
                    WHERE customer_id = ?
                ");
                $stmt->execute([4]);
                
                $message .= " Added yearly plan fee of 99rs.";
                $success = true;
            }
        } catch (PDOException $e) {
            $message .= " Error adding yearly plan fee: " . $e->getMessage();
        }
    }
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Special Fees Table</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../images/trainer-dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .container {
            max-width: 800px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h1 {
            color: #4dd0e1;
            text-align: center;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background: rgba(76, 175, 80, 0.3);
            border-left: 5px solid #4caf50;
        }
        .error {
            background: rgba(244, 67, 54, 0.3);
            border-left: 5px solid #f44336;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #1565c0;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Special Fees Table</h1>
        
        <div class="message <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <p><strong>Current Fee Structure:</strong></p>
        <ul>
            <li>Basic Plan: 5rs</li>
            <li>Standard Plan: 5rs</li>
            <li>Premium Plan: 10rs</li>
            <li>Yearly Membership: 99rs</li>
        </ul>
        
        <a href="trainer.php" class="btn">Back to Dashboard</a>
        <a href="fee_details.php?customer_id=4" class="btn">View Customer 4 Fees</a>
    </div>
</body>
</html>
