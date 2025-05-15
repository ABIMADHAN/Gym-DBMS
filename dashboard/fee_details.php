<?php
require_once("../config/db.php");

$customer_id = intval($_GET['customer_id']);

// Fetch membership prices from the fee_structure table
$stmt = $pdo->prepare("SELECT id, plan_name, fee_amount, description FROM fee_structure WHERE plan_name IN ('Monthly Membership', 'Yearly Membership')");
$stmt->execute();
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the customer has an active membership
$statusStmt = $pdo->prepare("SELECT status FROM customers WHERE customer_id = ?");
$statusStmt->execute([$customer_id]);
$customerStatus = $statusStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Fee Details</title>
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
            max-width: 900px;
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
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: bounce 1.5s infinite;
        }

        .status {
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
            color: <?php echo $customerStatus === 'active' ? 'green' : 'red'; ?>;
            text-align: center;
            animation: pulse 2s infinite;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            animation: slideIn 1s ease-in-out;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }

        table th {
            background-color: #ff5722;
            color: white;
            font-size: 1.2rem;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #ffe0b2;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        .pay-button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .pay-button:hover {
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
            animation: fadeIn 2s ease-in-out;
        }

        .back-link:hover {
            background-color: #ff5722;
            color: #fff;
        }

        .emoji {
            font-size: 2.5rem;
            margin-right: 10px;
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

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Membership Fee Details</h1>
        <p class="status">
            <?php if ($customerStatus === 'active'): ?>
                <span class="emoji">✅</span> Membership Status: Active
            <?php else: ?>
                <span class="emoji">❌</span> Membership Status: Inactive
            <?php endif; ?>
        </p>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Fee Amount (USD)</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                            <td><?php echo htmlspecialchars($plan['fee_amount']); ?></td>
                            <td><?php echo htmlspecialchars($plan['description']); ?></td>
                            <td>
                                <form action="payment_form.php" method="GET">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $plan['fee_amount']; ?>">
                                    <button type="submit" class="pay-button">💳 Pay Now</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="customer.php" class="back-link">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>