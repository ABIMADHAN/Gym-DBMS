<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $plan_id = intval($_POST['plan_id']);
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];
    $transaction_id = $_POST['transaction_id'];

    try {
        // Insert payment into the fee_payments table
        $stmt = $pdo->prepare("
            INSERT INTO fee_payments (customer_id, plan_id, amount_paid, payment_date, payment_method, transaction_id)
            VALUES (?, ?, ?, NOW(), ?, ?)
        ");
        $stmt->execute([$customer_id, $plan_id, $amount_paid, $payment_method, $transaction_id]);

        // Update the customer's status to active
        $updateStmt = $pdo->prepare("UPDATE customers SET status = 'active' WHERE customer_id = ?");
        $updateStmt->execute([$customer_id]);

        $success = true;
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
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
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
            font-size: 3rem;
            margin-bottom: 20px;
            animation: spin 2s linear infinite;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #ff5722;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #ff5722;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background-color: #ff5722;
            color: #fff;
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
                transform: translateY(-10px);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success) && $success): ?>
            <div class="emoji">🎉</div>
            <h1 class="success">Payment Successful!</h1>
            <p>Your membership status has been updated to <strong>Active</strong>.</p>
        <?php else: ?>
            <div class="emoji">😞</div>
            <h1 class="error">Payment Failed</h1>
            <p><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <a href="customer.php" class="back-link">⬅️ Back to Dashboard</a>
    </div>

    <script>
        // Add a confetti effect for successful payments
        <?php if (isset($success) && $success): ?>
        const container = document.querySelector('.container');
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'absolute';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = getRandomColor();
            confetti.style.top = Math.random() * 100 + '%';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear infinite`;
            container.appendChild(confetti);
        }

        function getRandomColor() {
            const colors = ['#ff5722', '#4caf50', '#2196f3', '#ffc107', '#e91e63'];
            return colors[Math.floor(Math.random() * colors.length)];
        }
        <?php endif; ?>
    </script>
</body>
</html>