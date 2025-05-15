<?php
require_once("../config/db.php");

// Fetch customer and plan details
$customer_id = intval($_GET['customer_id']);
$plan_id = intval($_GET['plan_id']);
$amount = floatval($_GET['amount']);

$stmt = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$customer_name = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT plan_name FROM fee_structure WHERE id = ?");
$stmt->execute([$plan_id]);
$plan_name = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
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
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            text-align: center;
            color: #ff5722;
            font-size: 2rem;
            margin-bottom: 20px;
            animation: bounce 1.5s infinite;
        }

        .details {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: #555;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #388e3c;
            transform: scale(1.1);
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

        .emoji {
            font-size: 2rem;
            margin-right: 10px;
        }

        .tooltip {
            font-size: 0.9rem;
            color: #888;
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
    </style>
    <script>
        function validateForm() {
            const transactionId = document.getElementById('transaction_id').value;
            const confirmation = document.getElementById('confirmation').checked;

            if (!transactionId) {
                alert('Please enter a valid Transaction ID.');
                return false;
            }

            if (!confirmation) {
                alert('Please confirm the payment details.');
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>💳 Payment Form</h1>
        <div class="details">
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
            <p><strong>Plan Name:</strong> <?php echo htmlspecialchars($plan_name); ?></p>
            <p><strong>Amount to Pay:</strong> $<?php echo number_format($amount, 2); ?></p>
        </div>
        <form action="process_payment.php" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
            <input type="hidden" name="amount_paid" value="<?php echo $amount; ?>">

            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="credit_card">💳 Credit Card</option>
                <option value="debit_card">💳 Debit Card</option>
                <option value="paypal">🅿️ PayPal</option>
                <option value="bank_transfer">🏦 Bank Transfer</option>
            </select>

            <label for="transaction_id">Transaction ID:</label>
            <input type="text" name="transaction_id" id="transaction_id" placeholder="Enter Transaction ID" required>
            <span class="tooltip">* Transaction ID is provided by your payment provider.</span>

            <label>
                <input type="checkbox" id="confirmation" required>
                I confirm that the payment details are correct.
            </label>

            <button type="submit">✅ Submit Payment</button>
        </form>
        <a href="fee_details.php?customer_id=<?php echo $customer_id; ?>" class="back-link">⬅️ Back to Fee Details</a>
    </div>
</body>
</html>