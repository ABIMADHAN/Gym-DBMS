<?php
require_once("../includes/header.php");
if (!isTrainer() && !isAdmin()) exit(); // Only allow trainers or admins
require_once("../includes/db.php");

// Check if customer_id was provided
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

if ($customer_id == 4) {
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // First check if custom fees already exist for this customer
        $check = $pdo->prepare("SELECT id FROM customer_special_fees WHERE customer_id = ?");
        $check->execute([$customer_id]);
        
        if ($check->rowCount() > 0) {
            // Update existing custom fees
            $pdo->prepare("UPDATE customer_special_fees SET 
                basic_plan_fee = 5,
                standard_plan_fee = 5,
                premium_plan_fee = 10,
                updated_at = NOW()
                WHERE customer_id = ?")->execute([$customer_id]);
            $message = "Updated special fees for customer ID 4";
        } else {
            // Create new custom fees entry
            $pdo->prepare("INSERT INTO customer_special_fees 
                (customer_id, basic_plan_fee, standard_plan_fee, premium_plan_fee, created_at) 
                VALUES (?, 5, 5, 10, NOW())")->execute([$customer_id]);
            $message = "Created special fees for customer ID 4";
        }
        
        $pdo->commit();
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Customer Fees</title>
    <style>
        /* ...add styling as needed... */
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Customer Fees</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <p>Use this page to update special pricing for customers.</p>
        
        <a href="fee_details.php?customer_id=<?php echo $customer_id; ?>" class="btn">View Fee Details</a>
        <a href="trainer.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
