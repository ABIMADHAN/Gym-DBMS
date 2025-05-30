<?php
require_once("../includes/header.php");
require_once("../includes/db.php");

if (!isset($_GET['payment_id'])) {
    header("Location: payment_dashboard.php");
    exit;
}

$payment_id = intval($_GET['payment_id']);

// Check if receipt_number column exists
$has_receipt_column = false;
try {
    $check = $pdo->query("SHOW COLUMNS FROM fee_payments LIKE 'receipt_number'");
    $has_receipt_column = ($check->rowCount() > 0);
} catch (PDOException $e) {
    // Column doesn't exist
}

// Fetch payment details
if ($has_receipt_column) {
    $stmt = $pdo->prepare("
        SELECT fp.*, c.name as customer_name, fs.plan_name
        FROM fee_payments fp
        JOIN customers c ON fp.customer_id = c.customer_id
        JOIN fee_structure fs ON fp.plan_id = fs.id
        WHERE fp.id = ?
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT fp.*, c.name as customer_name, fs.plan_name, 
               'GYYM-' || fp.id AS receipt_number,
               'completed' as status
        FROM fee_payments fp
        JOIN customers c ON fp.customer_id = c.customer_id
        JOIN fee_structure fs ON fp.plan_id = fs.id
        WHERE fp.id = ?
    ");
}

$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header("Location: payment_dashboard.php");
    exit;
}

// Generate receipt number if not available
$receipt_number = $payment['receipt_number'] ?? 'GYYM-' . date('Ymd') . '-' . $payment_id;

// Get gym info
$gym_name = "GYYM Fitness Center";
$gym_address = "123 Fitness Street, Health City";
$gym_phone = "+1 (555) 123-4567";
$gym_email = "info@gyym.com";
$gym_website = "www.gyym.com";

// Format payment date
$payment_date = date('F j, Y', strtotime($payment['payment_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #<?php echo $receipt_number; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-only {
                display: block;
            }
        }
        
        .receipt-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .gym-info {
            text-align: left;
        }
        
        .gym-name {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
            margin: 0 0 5px 0;
        }
        
        .gym-details {
            font-size: 14px;
            color: #666;
            margin: 3px 0;
        }
        
        .receipt-title {
            text-align: right;
        }
        
        .receipt-label {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .receipt-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }
        
        .customer-section, .payment-section {
            margin: 20px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-weight: bold;
            color: #333;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .payment-table th, .payment-table td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
        }
        
        .payment-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .payment-table .amount {
            text-align: right;
        }
        
        .payment-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .status-completed {
            background: #e8f5e9;
            color: #43a047;
        }
        
        .status-pending {
            background: #fff8e1;
            color: #ffa000;
        }
        
        .status-failed {
            background: #ffebee;
            color: #e53935;
        }
        
        .status-refunded {
            background: #f5f5f5;
            color: #757575;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #1976d2;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #1565c0;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="gym-info">
                <h1 class="gym-name"><?php echo $gym_name; ?></h1>
                <p class="gym-details"><?php echo $gym_address; ?></p>
                <p class="gym-details">Phone: <?php echo $gym_phone; ?></p>
                <p class="gym-details">Email: <?php echo $gym_email; ?></p>
                <p class="gym-details">Website: <?php echo $gym_website; ?></p>
            </div>
            <div class="receipt-title">
                <p class="receipt-label">Receipt Number</p>
                <p class="receipt-number"><?php echo $receipt_number; ?></p>
                <p class="receipt-label">Date</p>
                <p class="receipt-number"><?php echo $payment_date; ?></p>
            </div>
        </div>
        
        <div class="customer-section">
            <h2 class="section-title">Customer Information</h2>
            <div class="details-grid">
                <div class="detail-item">
                    <p class="detail-label">Customer Name</p>
                    <p class="detail-value"><?php echo htmlspecialchars($payment['customer_name']); ?></p>
                </div>
                <div class="detail-item">
                    <p class="detail-label">Customer ID</p>
                    <p class="detail-value"><?php echo $payment['customer_id']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="payment-section">
            <h2 class="section-title">Payment Details</h2>
            
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Plan</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Membership Fee</td>
                        <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                        <td class="amount">$<?php echo number_format($payment['amount_paid'], 2); ?></td>
                    </tr>
                    <?php if (isset($payment['discount_id']) && $payment['discount_id']): ?>
                    <tr>
                        <td>Discount Applied</td>
                        <td>Promo Code</td>
                        <td class="amount">-$<?php echo number_format($payment['discount_amount'] ?? 0, 2); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align: right;"><strong>Total Paid</strong></td>
                        <td class="amount"><strong>$<?php echo number_format($payment['amount_paid'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="details-grid">
                <div class="detail-item">
                    <p class="detail-label">Payment Method</p>
                    <p class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></p>
                </div>
                <div class="detail-item">
                    <p class="detail-label">Transaction ID</p>
                    <p class="detail-value"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-item">
                    <p class="detail-label">Status</p>
                    <p class="detail-value">
                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for your payment. We appreciate your business!</p>
            <p class="print-only">This is a computer-generated receipt and does not require a signature.</p>
        </div>
        
        <div class="action-buttons no-print">
            <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
            <a href="payment_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
