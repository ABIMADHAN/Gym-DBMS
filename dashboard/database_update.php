<?php
require_once("../includes/header.php");
if (!isTrainer()) exit(); // Only trainers/admins should be able to update the database
require_once("../includes/db.php");

$updates = [];
$errors = [];

// Update fee_payments table with new columns
try {
    // Check if status column exists
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'status'");
    if ($check->rowCount() == 0) {
        // Status column doesn't exist, so add it
        $pdo->exec("ALTER TABLE fee_payments ADD COLUMN status VARCHAR(20) DEFAULT 'completed' AFTER payment_method");
        $updates[] = "Added 'status' column to fee_payments table";
    }
    
    // Check if transaction_id column exists
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'transaction_id'");
    if ($check->rowCount() == 0) {
        // Transaction_id column doesn't exist, so add it
        $pdo->exec("ALTER TABLE fee_payments ADD COLUMN transaction_id VARCHAR(50) AFTER status");
        $updates[] = "Added 'transaction_id' column to fee_payments table";
    }
    
    // Check if payment_details column exists
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'payment_details'");
    if ($check->rowCount() == 0) {
        // Payment_details column doesn't exist, so add it
        $pdo->exec("ALTER TABLE fee_payments ADD COLUMN payment_details TEXT AFTER transaction_id");
        $updates[] = "Added 'payment_details' column to fee_payments table";
    }
    
    // Check if receipt_number column exists
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'receipt_number'");
    if ($check->rowCount() == 0) {
        // Receipt_number column doesn't exist, so add it
        $pdo->exec("ALTER TABLE fee_payments ADD COLUMN receipt_number VARCHAR(50) AFTER payment_details");
        $updates[] = "Added 'receipt_number' column to fee_payments table";
    }
    
    // Check if discount_id column exists
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'discount_id'");
    if ($check->rowCount() == 0) {
        // Discount_id column doesn't exist, so add it
        $pdo->exec("ALTER TABLE fee_payments ADD COLUMN discount_id INT AFTER receipt_number");
        $updates[] = "Added 'discount_id' column to fee_payments table";
    }
    
    // Check if notes column exists
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'notes'");
    if ($check->rowCount() == 0) {
        // Notes column doesn't exist, so add it
        $pdo->exec("ALTER TABLE fee_payments ADD COLUMN notes TEXT AFTER discount_id");
        $updates[] = "Added 'notes' column to fee_payments table";
    }
    
    // Check if payment_gateways table exists and create if it doesn't
    $tablesResult = $pdo->query("SHOW TABLES LIKE 'payment_gateways'");
    if ($tablesResult->rowCount() == 0) {
        // Create payment_gateways table
        $pdo->exec("
            CREATE TABLE payment_gateways (
                id INT AUTO_INCREMENT PRIMARY KEY,
                gateway_name VARCHAR(50) NOT NULL,
                display_name VARCHAR(50) NOT NULL,
                api_key VARCHAR(100),
                api_secret VARCHAR(100),
                active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $updates[] = "Created 'payment_gateways' table";
        
        // Insert default payment gateways
        $pdo->exec("
            INSERT INTO payment_gateways (gateway_name, display_name, active) VALUES
            ('credit_card', 'Credit Card', 1),
            ('paypal', 'PayPal', 1),
            ('bank_transfer', 'Bank Transfer', 1),
            ('cash', 'Pay at Gym', 1)
        ");
        $updates[] = "Added default payment gateways";
    }
    
    // Check if discount_codes table exists and create if it doesn't
    $tablesResult = $pdo->query("SHOW TABLES LIKE 'discount_codes'");
    if ($tablesResult->rowCount() == 0) {
        // Create discount_codes table
        $pdo->exec("
            CREATE TABLE discount_codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(20) NOT NULL UNIQUE,
                discount_percent DECIMAL(5,2) NOT NULL,
                description TEXT,
                active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expiry_date DATE,
                max_uses INT DEFAULT NULL
            )
        ");
        $updates[] = "Created 'discount_codes' table";
        
        // Insert sample discount codes
        $pdo->exec("
            INSERT INTO discount_codes (code, discount_percent, description, active, expiry_date, max_uses) VALUES
            ('WELCOME10', 10.00, 'Welcome discount for new members', 1, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 100),
            ('SUMMER20', 20.00, 'Summer special discount', 1, DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY), 50),
            ('LOYALTY15', 15.00, 'Discount for loyal customers', 1, DATE_ADD(CURRENT_DATE, INTERVAL 90 DAY), NULL)
        ");
        $updates[] = "Added sample discount codes";
    }
    
    // Check if discount_usage table exists and create if it doesn't
    $tablesResult = $pdo->query("SHOW TABLES LIKE 'discount_usage'");
    if ($tablesResult->rowCount() == 0) {
        // Create discount_usage table
        $pdo->exec("
            CREATE TABLE discount_usage (
                id INT AUTO_INCREMENT PRIMARY KEY,
                discount_id INT NOT NULL,
                customer_id INT NOT NULL,
                used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (discount_id) REFERENCES discount_codes(id)
            )
        ");
        $updates[] = "Created 'discount_usage' table";
    }
    
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../images/dashboard-bg.jpg') no-repeat center center fixed;
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
            text-align: center;
            color: #4dd0e1;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .result-box {
            background: #263238;
            border-radius: 8px;
            padding: 20px;
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
        ul {
            padding-left: 25px;
        }
        li {
            margin-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #4dd0e1;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Update Results</h1>
        
        <?php if (count($updates) > 0): ?>
            <div class="result-box success">
                <h3>✅ Updates Completed Successfully</h3>
                <ul>
                    <?php foreach ($updates as $update): ?>
                        <li><?php echo htmlspecialchars($update); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($errors) > 0): ?>
            <div class="result-box error">
                <h3>❌ Errors Occurred</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($updates) > 0 && count($errors) == 0): ?>
            <div class="result-box">
                <h3>🎉 All updates completed successfully!</h3>
                <p>Your database has been updated with all the necessary changes for the payment system to work properly.</p>
            </div>
        <?php endif; ?>
        
        <a href="payment_dashboard.php" class="back-link">⬅️ Back to Payment Dashboard</a>
    </div>
</body>
</html>
