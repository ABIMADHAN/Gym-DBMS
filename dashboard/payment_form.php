<?php
require_once("../config/db.php");

// Fetch customer and plan details
$customer_id = intval($_GET['customer_id']);
$plan_id = intval($_GET['plan_id']);
$amount = floatval($_GET['amount']);
$discount_code = isset($_GET['discount_code']) ? $_GET['discount_code'] : '';

// Apply discount if valid code is provided
$discount_amount = 0;
$original_amount = $amount;
if (!empty($discount_code)) {
    // Check if discount_codes table exists
    try {
        $stmt = $pdo->prepare("SELECT discount_percent FROM discount_codes WHERE code = ? AND active = 1 AND expiry_date >= CURDATE()");
        $stmt->execute([$discount_code]);
        $discount = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($discount) {
            $discount_amount = $amount * ($discount['discount_percent'] / 100);
            $amount = $amount - $discount_amount;
        }
    } catch (PDOException $e) {
        // Table doesn't exist yet, ignore discount
    }
}

$stmt = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$customer_name = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT plan_name FROM fee_structure WHERE id = ?");
$stmt->execute([$plan_id]);
$plan_name = $stmt->fetchColumn();

// Get available payment gateways if table exists
$gateways = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM payment_gateways WHERE active = 1");
    $stmt->execute();
    $gateways = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist yet, continue without gateways
}

// If payment_gateways table doesn't exist, use default payment methods
if (empty($gateways)) {
    $default_methods = [
        ['gateway_name' => 'credit_card', 'display_name' => 'Credit Card'],
        ['gateway_name' => 'paypal', 'display_name' => 'PayPal'],
        ['gateway_name' => 'bank_transfer', 'display_name' => 'Bank Transfer'],
        ['gateway_name' => 'cash', 'display_name' => 'Cash']
    ];
    $gateways = $default_methods;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #333;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            text-align: center;
            color: #4a00e0;
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #4a00e0;
            animation: slideIn 0.5s ease-in-out;
        }

        .details h2 {
            color: #4a00e0;
            margin-top: 0;
        }

        .price-details {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }

        .original-price {
            text-decoration: line-through;
            color: #888;
        }

        .discount-applied {
            color: #4caf50;
            font-weight: bold;
        }

        .final-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4a00e0;
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .payment-method {
            flex: 1 0 40%;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .payment-method:hover {
            border-color: #4a00e0;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .payment-method.selected {
            border-color: #4a00e0;
            background-color: #f0e7ff;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #4a00e0;
        }

        .payment-name {
            font-weight: bold;
        }

        .payment-description {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .form-section {
            display: none;
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #4a00e0;
            box-shadow: 0 0 0 3px rgba(74, 0, 224, 0.2);
            outline: none;
        }

        .card-inputs {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
        }

        button {
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.2);
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #4a00e0;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .promo-code {
            margin-bottom: 20px;
        }

        .promo-code-input {
            display: flex;
            gap: 10px;
        }

        .promo-code input {
            flex: 1;
        }

        .promo-code button {
            width: auto;
            padding: 12px 20px;
            margin-top: 0;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            color: #666;
        }

        .secure-badge i {
            color: #4caf50;
            margin-right: 10px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .payment-methods {
                flex-direction: column;
            }
            
            .payment-method {
                flex: 1 0 100%;
            }
            
            .card-inputs {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💳 Complete Your Payment</h1>
        
        <div class="details">
            <h2>Order Summary</h2>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
            <p><strong>Plan:</strong> <?php echo htmlspecialchars($plan_name); ?></p>
            
            <div class="price-details">
                <div>
                    <?php if ($discount_amount > 0): ?>
                        <div class="original-price">Original Price: $<?php echo number_format($original_amount, 2); ?></div>
                        <div class="discount-applied">Discount Applied: -$<?php echo number_format($discount_amount, 2); ?></div>
                    <?php endif; ?>
                    <div class="final-price">Total: $<?php echo number_format($amount, 2); ?></div>
                </div>
            </div>
        </div>
        
        <div class="promo-code">
            <label for="discount_code">Have a promo code?</label>
            <div class="promo-code-input">
                <input type="text" id="discount_code" placeholder="Enter promo code" value="<?php echo htmlspecialchars($discount_code); ?>">
                <button type="button" onclick="applyPromoCode()">Apply</button>
            </div>
        </div>
        
        <h2>Select Payment Method</h2>
        <div class="payment-methods">
            <?php foreach ($gateways as $gateway): ?>
                <div class="payment-method" onclick="selectPayment('<?php echo $gateway['gateway_name']; ?>')">
                    <input type="radio" name="payment_method" id="<?php echo $gateway['gateway_name']; ?>" value="<?php echo $gateway['gateway_name']; ?>">
                    <div class="payment-icon">
                        <?php if ($gateway['gateway_name'] == 'credit_card'): ?>
                            <i class="fas fa-credit-card"></i>
                        <?php elseif ($gateway['gateway_name'] == 'paypal'): ?>
                            <i class="fab fa-paypal"></i>
                        <?php elseif ($gateway['gateway_name'] == 'bank_transfer'): ?>
                            <i class="fas fa-university"></i>
                        <?php elseif ($gateway['gateway_name'] == 'cash'): ?>
                            <i class="fas fa-money-bill-wave"></i>
                        <?php else: ?>
                            <i class="fas fa-money-check"></i>
                        <?php endif; ?>
                    </div>
                    <div class="payment-name"><?php echo htmlspecialchars($gateway['display_name'] ?? ucfirst(str_replace('_', ' ', $gateway['gateway_name']))); ?></div>
                    <div class="payment-description">
                        <?php if ($gateway['gateway_name'] == 'credit_card'): ?>
                            Pay securely with your credit card
                        <?php elseif ($gateway['gateway_name'] == 'paypal'): ?>
                            Fast and secure payment with PayPal
                        <?php elseif ($gateway['gateway_name'] == 'bank_transfer'): ?>
                            Pay directly from your bank account
                        <?php elseif ($gateway['gateway_name'] == 'cash'): ?>
                            Pay with cash at the gym reception
                        <?php else: ?>
                            Make payment using <?php echo htmlspecialchars($gateway['display_name'] ?? ucfirst(str_replace('_', ' ', $gateway['gateway_name']))); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Credit Card Form -->
        <div id="credit_card_form" class="form-section">
            <form id="payment_form" action="process_payment.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                <input type="hidden" name="amount_paid" value="<?php echo $amount; ?>">
                <input type="hidden" name="payment_method" value="credit_card">
                <input type="hidden" name="discount_code" value="<?php echo htmlspecialchars($discount_code); ?>">
                
                <div class="form-group">
                    <label for="card_holder">Card Holder Name</label>
                    <input type="text" id="card_holder" name="card_holder" placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                </div>
                
                <div class="card-inputs">
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4" required>
                    </div>
                </div>
                
                <button type="submit">Pay Now $<?php echo number_format($amount, 2); ?></button>
            </form>
        </div>
        
        <!-- PayPal Form -->
        <div id="paypal_form" class="form-section">
            <form action="process_payment.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                <input type="hidden" name="amount_paid" value="<?php echo $amount; ?>">
                <input type="hidden" name="payment_method" value="paypal">
                <input type="hidden" name="discount_code" value="<?php echo htmlspecialchars($discount_code); ?>">
                
                <div class="form-group">
                    <label for="paypal_email">PayPal Email</label>
                    <input type="email" id="paypal_email" name="paypal_email" placeholder="your@email.com" required>
                </div>
                
                <button type="submit">Pay with PayPal $<?php echo number_format($amount, 2); ?></button>
            </form>
        </div>
        
        <!-- Bank Transfer Form -->
        <div id="bank_transfer_form" class="form-section">
            <form action="process_payment.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                <input type="hidden" name="amount_paid" value="<?php echo $amount; ?>">
                <input type="hidden" name="payment_method" value="bank_transfer">
                <input type="hidden" name="discount_code" value="<?php echo htmlspecialchars($discount_code); ?>">
                
                <div class="form-group">
                    <p>Please make a transfer to the following bank account:</p>
                    <p><strong>Bank Name:</strong> GYYM Bank</p>
                    <p><strong>Account Number:</strong> 123456789</p>
                    <p><strong>Routing Number:</strong> 987654321</p>
                    <p><strong>Amount:</strong> $<?php echo number_format($amount, 2); ?></p>
                    <p><strong>Reference:</strong> GYYM-<?php echo $customer_id; ?>-<?php echo time(); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="transaction_id">Transaction Reference</label>
                    <input type="text" id="transaction_id" name="transaction_id" placeholder="Enter your bank transaction reference" required>
                </div>
                
                <button type="submit">Confirm Bank Transfer</button>
            </form>
        </div>
        
        <!-- Cash Payment Form -->
        <div id="cash_form" class="form-section">
            <form action="process_payment.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                <input type="hidden" name="amount_paid" value="<?php echo $amount; ?>">
                <input type="hidden" name="payment_method" value="cash">
                <input type="hidden" name="discount_code" value="<?php echo htmlspecialchars($discount_code); ?>">
                
                <div class="form-group">
                    <p>Please visit the gym reception to make your payment.</p>
                    <p><strong>Amount Due:</strong> $<?php echo number_format($amount, 2); ?></p>
                    <p><strong>Reference Code:</strong> GYYM-<?php echo $customer_id; ?>-<?php echo substr(time(), -6); ?></p>
                    <p>Please show this code to the receptionist when making your payment.</p>
                </div>
                
                <button type="submit">Generate Payment Slip</button>
            </form>
        </div>
        
        <div class="secure-badge">
            <i class="fas fa-lock"></i> All payments are secure and encrypted
        </div>
        
        <a href="fee_details.php?customer_id=<?php echo $customer_id; ?>" class="back-link">⬅️ Back to Fee Details</a>
    </div>
    
    <script>
        function selectPayment(method) {
            // Reset all payment methods
            document.querySelectorAll('.payment-method').forEach(element => {
                element.classList.remove('selected');
            });
            
            // Hide all form sections
            document.querySelectorAll('.form-section').forEach(element => {
                element.classList.remove('active');
            });
            
            // Select the clicked payment method
            document.getElementById(method).checked = true;
            document.getElementById(method).parentElement.classList.add('selected');
            
            // Show the corresponding form
            if (document.getElementById(method + '_form')) {
                document.getElementById(method + '_form').classList.add('active');
            } else {
                // Fallback to credit card form if specific form doesn't exist
                document.getElementById('credit_card_form').classList.add('active');
                document.getElementById('credit_card').checked = true;
                document.getElementById('credit_card').parentElement.classList.add('selected');
            }
        }
        
        function applyPromoCode() {
            const discountCode = document.getElementById('discount_code').value;
            if (discountCode) {
                window.location.href = `payment_form.php?customer_id=<?php echo $customer_id; ?>&plan_id=<?php echo $plan_id; ?>&amount=<?php echo $original_amount; ?>&discount_code=${discountCode}`;
            }
        }
        
        // Format credit card number with spaces
        document.getElementById('card_number')?.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            e.target.value = formattedValue;
        });
        
        // Format expiry date with slash
        document.getElementById('expiry_date')?.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            e.target.value = value;
        });
        
        // Select the first payment method by default
        document.addEventListener('DOMContentLoaded', function() {
            // Get first payment method
            const firstMethod = document.querySelector('input[name="payment_method"]');
            if (firstMethod) {
                selectPayment(firstMethod.value);
            } else {
                // Fallback to credit card if no methods found
                selectPayment('credit_card');
            }
        });
    </script>
</body>
</html>