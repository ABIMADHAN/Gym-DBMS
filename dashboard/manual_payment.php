<?php
require_once("../includes/header.php");
if (!isTrainer()) exit();
require_once("../includes/db.php");

$success_message = '';
$error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $plan_id = intval($_POST['plan_id']);
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];
    $transaction_id = $_POST['transaction_id'] ?? 'MANUAL-' . time();
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'completed';
    $notes = $_POST['notes'] ?? '';

    try {
        // Insert payment record
        $stmt = $pdo->prepare("
            INSERT INTO fee_payments (
                customer_id, plan_id, amount_paid, payment_date, 
                payment_method, transaction_id, status, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $customer_id, $plan_id, $amount_paid, $payment_date,
            $payment_method, $transaction_id, $status, $notes
        ]);
        
        // Update customer status if payment is completed
        if ($status === 'completed') {
            $updateStmt = $pdo->prepare("UPDATE customers SET status = 'active' WHERE customer_id = ?");
            $updateStmt->execute([$customer_id]);
        }
        
        $success_message = "Payment recorded successfully!";
    } catch (PDOException $e) {
        $error_message = "Error recording payment: " . $e->getMessage();
    }
}

// Get all customers
$customerStmt = $pdo->query("SELECT customer_id, name FROM customers ORDER BY name");
$customers = $customerStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all plans
$planStmt = $pdo->query("SELECT id, plan_name, fee_amount FROM fee_structure ORDER BY fee_amount");
$plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Payment Entry</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../images/dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .dashboard-container {
            max-width: 800px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2 {
            text-align: center;
            color: #4dd0e1;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #4dd0e1;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #546e7a;
            background: #37474f;
            color: white;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button {
            background: #4dd0e1;
            color: #222;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background: #26c6da;
        }
        .success-message {
            background: #4caf50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            background: #f44336;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
    <div class="dashboard-container">
        <h2>💰 Manual Payment Entry</h2>
        <a href="payment_dashboard.php" class="back-link">⬅️ Back to Payment Dashboard</a>
        
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="customer_id">Customer:</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['customer_id']; ?>">
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="plan_id">Membership Plan:</label>
                <select id="plan_id" name="plan_id" required onchange="updateAmount()">
                    <option value="">Select Plan</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>" data-amount="<?php echo $plan['fee_amount']; ?>">
                            <?php echo htmlspecialchars($plan['plan_name']); ?> - $<?php echo $plan['fee_amount']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount_paid">Amount Paid:</label>
                <input type="number" id="amount_paid" name="amount_paid" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="payment_date">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="paypal">PayPal</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="transaction_id">Transaction ID (Optional):</label>
                <input type="text" id="transaction_id" name="transaction_id" placeholder="MANUAL-<?php echo time(); ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Payment Status:</label>
                <select id="status" name="status" required>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes" placeholder="Enter any additional notes about this payment"></textarea>
            </div>
            
            <button type="submit">Record Payment</button>
        </form>
    </div>
    
    <script>
        function updateAmount() {
            const planSelect = document.getElementById('plan_id');
            const amountInput = document.getElementById('amount_paid');
            
            if (planSelect.selectedIndex > 0) {
                const selectedOption = planSelect.options[planSelect.selectedIndex];
                const amount = selectedOption.getAttribute('data-amount');
                amountInput.value = amount;
            } else {
                amountInput.value = '';
            }
        }
        
        // Set initial amount when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateAmount();
        });
    </script>
</body>
</html>
