<?php
require_once("../config/db.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $plan_id = intval($_POST['plan_id']);
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];
    $discount_code = isset($_POST['discount_code']) ? $_POST['discount_code'] : '';
    
    // Set default transaction ID if not provided
    $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : 'TRANS-' . time();
    
    // Different payment methods have different status defaults
    $status = 'pending';
    if ($payment_method == 'credit_card' || $payment_method == 'paypal') {
        $status = 'completed'; // Assume immediate completion for card and PayPal
    } elseif ($payment_method == 'cash') {
        $status = 'pending'; // Cash payments need verification
    } elseif ($payment_method == 'bank_transfer') {
        $status = 'pending'; // Bank transfers need verification
    }
    
    // Collect additional fields based on payment method
    $payment_details = [];
    if ($payment_method == 'credit_card' && isset($_POST['card_number'])) {
        // Store only last 4 digits for security
        $card_number = $_POST['card_number'];
        $last_four = substr(str_replace(' ', '', $card_number), -4);
        $payment_details['card_last_four'] = $last_four;
        $payment_details['card_holder'] = $_POST['card_holder'] ?? '';
    } elseif ($payment_method == 'paypal' && isset($_POST['paypal_email'])) {
        $payment_details['paypal_email'] = $_POST['paypal_email'];
    }
    
    // Generate a receipt number
    $receipt_number = 'GYYM-' . date('Ymd') . '-' . rand(1000, 9999);
    
    try {
        // If discount code was used, record it if the discount_codes table exists
        $discount_id = null;
        if (!empty($discount_code)) {
            try {
                $discountStmt = $pdo->prepare("SELECT id, discount_percent FROM discount_codes WHERE code = ?");
                $discountStmt->execute([$discount_code]);
                $discount = $discountStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($discount) {
                    $discount_id = $discount['id'];
                    // Record discount usage if table exists
                    try {
                        $usageStmt = $pdo->prepare("INSERT INTO discount_usage (discount_id, customer_id, used_at) VALUES (?, ?, NOW())");
                        $usageStmt->execute([$discount_id, $customer_id]);
                    } catch (PDOException $e) {
                        // Table doesn't exist, continue without recording usage
                    }
                }
            } catch (PDOException $e) {
                // Table doesn't exist, continue without discount
            }
        }
        
        // Check if the fee_payments table has the necessary columns
        $hasRequiredColumns = false;
        try {
            // Check if status column exists
            $checkStmt = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'status'");
            $hasRequiredColumns = ($checkStmt->rowCount() > 0);
        } catch (PDOException $e) {
            // Column doesn't exist
            $hasRequiredColumns = false;
        }
        
        if ($hasRequiredColumns) {
            // Insert payment with all new columns
            $stmt = $pdo->prepare("
                INSERT INTO fee_payments (
                    customer_id, plan_id, amount_paid, payment_date, 
                    payment_method, transaction_id, status, 
                    payment_details, receipt_number, discount_id
                )
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $customer_id, 
                $plan_id, 
                $amount_paid, 
                $payment_method, 
                $transaction_id, 
                $status,
                json_encode($payment_details),
                $receipt_number,
                $discount_id
            ]);
        } else {
            // Insert payment with only the original columns
            $stmt = $pdo->prepare("
                INSERT INTO fee_payments (
                    customer_id, plan_id, amount_paid, payment_date, payment_method
                )
                VALUES (?, ?, ?, NOW(), ?)
            ");
            
            $stmt->execute([
                $customer_id, 
                $plan_id, 
                $amount_paid, 
                $payment_method
            ]);
        }
        
        $payment_id = $pdo->lastInsertId();
        
        // Update the customer's status to active
        try {
            $updateStmt = $pdo->prepare("UPDATE customers SET status = 'active' WHERE customer_id = ?");
            $updateStmt->execute([$customer_id]);
        } catch (PDOException $e) {
            // Continue even if update fails
        }
        
        $success = true;
        
        // Redirect to the receipt page if it exists, otherwise show success message here
        if (file_exists(__DIR__ . '/view_receipt.php')) {
            header("Location: view_receipt.php?payment_id=" . $payment_id);
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Error processing payment: " . $e->getMessage();
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #4a00e0;
        }

        .success {
            color: #4caf50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            animation: bounce 1.5s infinite;
        }

        .error {
            color: #f44336;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .emoji {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
        }

        .success-emoji {
            animation: bounce 2s infinite;
        }

        .error-emoji {
            animation: shake 2s infinite;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #4a00e0;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #4a00e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background-color: #4a00e0;
            color: #fff;
        }

        .receipt-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin: 20px 0;
            border-left: 5px solid #4caf50;
        }

        .receipt-details p {
            margin: 8px 0;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 5px;
        }

        .status-completed {
            background: #4caf50;
            color: white;
        }

        .status-pending {
            background: #ff9800;
            color: white;
        }

        .status-failed {
            background: #f44336;
            color: white;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-10px);
            }
            50% {
                transform: translateX(0);
            }
            75% {
                transform: translateX(10px);
            }
        }

        /* Confetti Animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f44336;
            opacity: 0.7;
            top: 0;
            animation: confetti-fall linear forwards;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success) && $success): ?>
            <span class="emoji success-emoji">🎉</span>
            <h1 class="success">Payment Successful!</h1>
            
            <div class="receipt-details">
                <p><strong>Receipt Number:</strong> <?php echo $receipt_number; ?></p>
                <p><strong>Amount:</strong> $<?php echo number_format($amount_paid, 2); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment_method)); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a'); ?></p>
                <p><strong>Status:</strong> <span class="payment-status status-<?php echo $status; ?>"><?php echo ucfirst($status); ?></span></p>
                <?php if ($status === 'pending'): ?>
                    <p><i class="fas fa-info-circle"></i> Your payment is being processed. We'll update you once it's confirmed.</p>
                <?php endif; ?>
            </div>
            
            <p>Your membership status has been updated to <strong>Active</strong>.</p>
            
            <?php if (file_exists(__DIR__ . '/view_receipt.php')): ?>
                <a href="view_receipt.php?payment_id=<?php echo $payment_id; ?>" class="back-link"><i class="fas fa-receipt"></i> View Receipt</a>
            <?php endif; ?>
            
            <a href="customer.php" class="back-link"><i class="fas fa-home"></i> Back to Dashboard</a>
        <?php else: ?>
            <span class="emoji error-emoji">😞</span>
            <h1 class="error">Payment Failed</h1>
            <p><?php echo htmlspecialchars($error_message); ?></p>
            <a href="fee_details.php?customer_id=<?php echo $customer_id; ?>" class="back-link">Try Again</a>
            <a href="customer.php" class="back-link">Back to Dashboard</a>
        <?php endif; ?>
    </div>

    <script>
        // Add a confetti effect for successful payments
        <?php if (isset($success) && $success): ?>
        function createConfetti() {
            const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'];
            
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                confetti.style.animationDelay = Math.random() + 's';
                document.body.appendChild(confetti);
                
                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }
        
        createConfetti();
        <?php endif; ?>
    </script>
</body>
</html>