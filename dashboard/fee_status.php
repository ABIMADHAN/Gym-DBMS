<?php
require_once("../config/db.php");

// Fetch customer fee statuses
$stmt = $pdo->prepare("
    SELECT c.customer_id, c.name, c.status, f.plan_name, f.fee_amount
    FROM customers c
    LEFT JOIN fee_payments p ON c.customer_id = p.customer_id
    LEFT JOIN fee_structure f ON p.plan_id = f.id
    GROUP BY c.customer_id
");
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Status</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            color: #ff5722;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #ff5722;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #ffe0b2;
            transform: scale(1.02);
            transition: all 0.3s ease;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Fee Status</h1>
        <table>
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Plan Name</th>
                    <th>Fee Amount (USD)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td style="color: <?php echo $customer['status'] === 'active' ? 'green' : 'red'; ?>;">
                            <?php echo ucfirst($customer['status']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($customer['plan_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($customer['fee_amount'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="trainer.php" class="back-link">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>