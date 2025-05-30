<?php
require_once("../includes/header.php");
if (!isTrainer()) exit();
require_once("../includes/db.php");

// Handle payment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'], $_POST['status'])) {
    $payment_id = intval($_POST['payment_id']);
    $status = $_POST['status'];
    
    // Check if status column exists before attempting update
    try {
        $update = $pdo->prepare("UPDATE fee_payments SET status = ? WHERE id = ?");
        $update->execute([$status, $payment_id]);
        
        // Redirect to prevent form resubmission
        header("Location: payment_dashboard.php?updated=1");
        exit;
    } catch (PDOException $e) {
        // If status column doesn't exist, show message suggesting to run the update script
        $error_message = "Database needs to be updated. Please run the database_update.php script first.";
    }
}

// Handle date range filtering
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Check if fee_payments table has status column
$has_status_column = false;
try {
    $check = $pdo->query("SELECT `status` FROM fee_payments LIMIT 1");
    $has_status_column = true;
} catch (PDOException $e) {
    // Status column doesn't exist
    $has_status_column = false;
}

// Build the query with filters - adjust based on column existence
if ($has_status_column) {
    $query = "
        SELECT fp.id, fp.customer_id, c.name as customer_name, fs.plan_name, 
               fp.amount_paid, fp.payment_date, fp.payment_method, fp.status, fp.transaction_id
        FROM fee_payments fp
        JOIN customers c ON fp.customer_id = c.customer_id
        JOIN fee_structure fs ON fp.plan_id = fs.id
        WHERE fp.payment_date BETWEEN ? AND ?
    ";
} else {
    // Query without status column
    $query = "
        SELECT fp.id, fp.customer_id, c.name as customer_name, fs.plan_name, 
               fp.amount_paid, fp.payment_date, fp.payment_method, 'completed' as status, 
               COALESCE(fp.transaction_id, CONCAT('TRANS-', fp.id)) as transaction_id
        FROM fee_payments fp
        JOIN customers c ON fp.customer_id = c.customer_id
        JOIN fee_structure fs ON fp.plan_id = fs.id
        WHERE fp.payment_date BETWEEN ? AND ?
    ";
}

$params = [$start_date, $end_date];

if (!empty($payment_method)) {
    $query .= " AND fp.payment_method = ?";
    $params[] = $payment_method;
}

if (!empty($status_filter) && $has_status_column) {
    $query .= " AND fp.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY fp.payment_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_revenue = 0;
$payment_methods = [];
$statuses = [];

foreach ($payments as $payment) {
    $total_revenue += $payment['amount_paid'];
    
    if (!isset($payment_methods[$payment['payment_method']])) {
        $payment_methods[$payment['payment_method']] = 0;
    }
    $payment_methods[$payment['payment_method']]++;
    
    if (!isset($statuses[$payment['status']])) {
        $statuses[$payment['status']] = 0;
    }
    $statuses[$payment['status']]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../images/dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .dashboard-container {
            max-width: 1200px;
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
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #1976d2, #64b5f6);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
        .filters {
            background: #263238;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(38, 50, 56, 0.8);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #37474f;
        }
        th {
            background: #1976d2;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
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
        .status-refunded {
            background: #9e9e9e;
            color: white;
        }
        .action-btn {
            background: #4dd0e1;
            color: #222;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
            font-weight: bold;
            transition: all 0.2s;
        }
        .action-btn:hover {
            background: #26c6da;
            transform: scale(1.05);
        }
        input[type="date"], select, button {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #546e7a;
            background: #37474f;
            color: white;
            margin-right: 10px;
        }
        button {
            background: #4dd0e1;
            color: #222;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }
        button:hover {
            background: #26c6da;
        }
        .notification {
            background: #4caf50;
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
        <h2>💰 Payment Dashboard</h2>
        <a href="trainer.php" class="back-link">⬅️ Back to Dashboard</a>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="notification">Payment status updated successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="notification" style="background:#f44336;"><?php echo $error_message; ?></div>
            <div style="text-align:center;margin:20px 0;">
                <a href="database_update.php" class="action-btn" style="padding:10px 20px;background:#4caf50;">
                    Run Database Update
                </a>
            </div>
        <?php endif; ?>

        <?php if (!$has_status_column): ?>
            <div class="notification" style="background:#ff9800;">
                <strong>Notice:</strong> Your database needs to be updated to enable all payment features. 
                <a href="database_update.php" style="color:white;text-decoration:underline;">Click here</a> to update the database.
            </div>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                <div><?php echo count($payments); ?> payments</div>
            </div>
            
            <?php foreach ($payment_methods as $method => $count): ?>
                <div class="stat-card">
                    <h3><?php echo ucfirst(str_replace('_', ' ', $method)); ?></h3>
                    <div class="stat-value"><?php echo $count; ?></div>
                    <div>payments</div>
                </div>
            <?php endforeach; ?>
            
            <?php foreach ($statuses as $status => $count): ?>
                <div class="stat-card">
                    <h3><?php echo ucfirst($status); ?></h3>
                    <div class="stat-value"><?php echo $count; ?></div>
                    <div>payments</div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="filters">
            <form method="GET" action="">
                <label>Date Range:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                
                <label>Payment Method:</label>
                <select name="payment_method">
                    <option value="">All Methods</option>
                    <option value="credit_card" <?php echo $payment_method === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                    <option value="debit_card" <?php echo $payment_method === 'debit_card' ? 'selected' : ''; ?>>Debit Card</option>
                    <option value="paypal" <?php echo $payment_method === 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                    <option value="bank_transfer" <?php echo $payment_method === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="cash" <?php echo $payment_method === 'cash' ? 'selected' : ''; ?>>Cash</option>
                </select>
                
                <?php if ($has_status_column): ?>
                <label>Status:</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
                <?php endif; ?>
                
                <button type="submit">Filter</button>
                <button type="button" onclick="window.location.href='payment_dashboard.php'">Reset</button>
                <button type="button" onclick="window.location.href='manual_payment.php'" style="background: #ff9800;">Add Manual Payment</button>
                <button type="button" onclick="printReport()" style="background: #4caf50;">Print Report</button>
            </form>
        </div>
        
        <table id="payments-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Transaction ID</th>
                    <th>Status</th>
                    <?php if ($has_status_column): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo $payment['id']; ?></td>
                    <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                    <td>$<?php echo number_format($payment['amount_paid'], 2); ?></td>
                    <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                    <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                    <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </td>
                    <?php if ($has_status_column): ?>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="">Change Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </form>
                        <a href="view_receipt.php?payment_id=<?php echo $payment['id']; ?>" class="action-btn">Receipt</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        function printReport() {
            // Create a printable version of the data
            const startDate = '<?php echo $start_date; ?>';
            const endDate = '<?php echo $end_date; ?>';
            
            // Open a new window for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Payment Report (${startDate} to ${endDate})</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        h1, h2 { text-align: center; }
                        .total { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h1>GYYM Payment Report</h1>
                    <h2>${startDate} to ${endDate}</h2>
                    
                    <h3>Summary</h3>
                    <p>Total Revenue: $<?php echo number_format($total_revenue, 2); ?></p>
                    <p>Total Payments: <?php echo count($payments); ?></p>
                    
                    <h3>Payment Details</h3>
                    ${document.getElementById('payments-table').outerHTML}
                    
                    <p>Report generated on: ${new Date().toLocaleString()}</p>
                </body>
                </html>
            `);
            
            // Print the window
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
    </script>
</body>
</html>
