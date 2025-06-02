<?php
require_once("../includes/header.php");
require_once("../includes/db.php");

// Get customer ID from URL
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Fetch customer information
$customer = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

// Make sure customer status is defined
$customerStatus = 'inactive'; // Default to inactive
if ($customer && isset($customer['status'])) {
    $customerStatus = $customer['status'];
}

// Fetch customer payment history
$payments = [];
try {
    $stmt = $pdo->prepare("
        SELECT fp.*, fs.plan_name 
        FROM fee_payments fp 
        JOIN fee_structure fs ON fp.plan_id = fs.id 
        WHERE fp.customer_id = ? 
        ORDER BY fp.payment_date DESC
    ");
    $stmt->execute([$customer_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

// Special pricing for customer ID 4
$isSpecialCustomer = ($customer_id == 4);

// Fetch fee structure from database
$plans = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM fee_structure");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Payment Details</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../images/customer-dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            position: relative;
            overflow-x: hidden;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg, 
                rgba(33, 150, 243, 0.15) 0%, 
                rgba(0, 0, 0, 0) 50%, 
                rgba(156, 39, 176, 0.15) 100%
            );
            z-index: -1;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .dashboard-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        h1, h2, h3 {
            color: #4dd0e1;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .status-container {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.3);
        }
        
        .status-active {
            color: #4caf50;
        }
        
        .status-inactive {
            color: #f44336;
        }
        
        .plans-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .plan-card {
            background: rgba(25, 118, 210, 0.8);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }
        
        .plan-card h3 {
            color: white;
            font-size: 1.8em;
            margin-top: 0;
        }
        
        .plan-price {
            font-size: 2em;
            font-weight: bold;
            color: #ffd54f;
            margin: 10px 0;
        }
        
        .plan-duration {
            color: #e1f5fe;
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        
        .monthly-price {
            color: #b3e5fc;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .select-plan-btn {
            background-color: #ff5722;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: 15px;
        }
        
        .select-plan-btn:hover {
            background-color: #ff7043;
            transform: scale(1.05);
            text-decoration: none;
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
        
        .payment-history {
            margin-top: 40px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        th {
            background-color: #1976d2;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .special-price-notice {
            background-color: rgba(76, 175, 80, 0.2);
            border-left: 4px solid #4caf50;
            padding: 10px 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .promo-code-container {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        
        .promo-code-input {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            gap: 10px;
        }
        
        .promo-code-input input {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.9);
            width: 200px;
            font-size: 16px;
        }
        
        .promo-code-input button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: #ff5722;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .promo-code-input button:hover {
            background: #f4511e;
        }
        
        .discount-badge {
            display: inline-block;
            background: #ff5722;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 8px;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #aaa;
            font-size: 1.2em;
            margin-right: 8px;
        }
        
        .promo-result {
            margin-top: 10px;
            font-weight: bold;
        }
        
        .promo-success {
            color: #4caf50;
        }
        
        .promo-error {
            color: #f44336;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Membership Fee Details</h1>
        
        <a href="customer.php" class="back-link">⬅️ Back to Dashboard</a>
        
        <!-- Customer Status -->
        <div class="status-container">
            <?php if ($customerStatus === 'active'): ?>
                <h3>✅ Membership Status: <span class="status-active">Active</span></h3>
            <?php else: ?>
                <h3>❌ Membership Status: <span class="status-inactive">Inactive</span></h3>
            <?php endif; ?>
            
            <?php if ($customer && isset($customer['name'])): ?>
                <p>Customer: <?php echo htmlspecialchars($customer['name']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if ($isSpecialCustomer): ?>
            <div class="special-price-notice">
                <p>🎉 <strong>Special Discount:</strong> You are eligible for special pricing! Monthly memberships are ₹500, and yearly memberships are ₹5000.</p>
            </div>
        <?php endif; ?>
        
        <!-- Promo Code Section -->
        <div class="promo-code-container">
            <h3>Have a Promo Code?</h3>
            <div class="promo-code-input">
                <input type="text" id="promo-code" placeholder="Enter promo code">
                <button id="apply-promo-btn">Apply</button>
            </div>
            <div id="promo-result" class="promo-result"></div>
        </div>
        
        <!-- Fee Structure -->
        <h2>Available Membership Plans</h2>
        
        <div class="plans-container">
            <?php foreach ($plans as $plan): ?>
                <?php
                // Set default values to prevent undefined array key errors
                $planName = isset($plan['plan_name']) ? $plan['plan_name'] : 'Plan';
                $planPrice = isset($plan['price']) ? $plan['price'] : 0;
                $planDescription = isset($plan['description']) ? $plan['description'] : '';
                $planDuration = isset($plan['duration']) && intval($plan['duration']) > 0 ? intval($plan['duration']) : 1;
                
                // Store original price for display if discounted
                $originalPrice = $planPrice;
                
                // Calculate monthly price safely
                $monthlyPrice = $planPrice / $planDuration;
                
                // Apply special pricing for customer ID 4
                if ($isSpecialCustomer) {
                    // Check if this is a yearly plan (either by duration or name)
                    if ((isset($plan['duration']) && $plan['duration'] >= 12) || 
                        (isset($planName) && stripos($planName, 'year') !== false)) {
                        $planPrice = 5000; // 5000rs for yearly membership
                        
                        // Also ensure that yearly plans show correct duration
                        if ($planDuration < 12) {
                            $planDuration = 12; // Set to 12 months for correct monthly calculation
                        }
                    } else {
                        $planPrice = 500; // 500rs for all monthly plans
                    }
                    
                    $monthlyPrice = $planPrice / $planDuration;
                    $originalPrice = $planPrice; // Update original price for special customer
                }
                ?>
                <div class="plan-card" data-plan-id="<?php echo $plan['id']; ?>" 
                     data-original-price="<?php echo $originalPrice; ?>" 
                     data-duration="<?php echo $planDuration; ?>">
                    <div>
                        <h3><?php echo htmlspecialchars($planName); ?></h3>
                        <div class="plan-price">
                            <span class="current-price">₹<?php echo number_format($planPrice, 2); ?></span>
                        </div>
                        <div class="plan-duration"><?php echo htmlspecialchars($planDuration); ?> months</div>
                        <div class="monthly-price">₹<?php echo number_format($monthlyPrice, 2); ?> / month</div>
                        <?php if (!empty($planDescription)): ?>
                            <p><?php echo htmlspecialchars($planDescription); ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="payment_form.php?customer_id=<?php echo $customer_id; ?>&plan_id=<?php echo $plan['id']; ?>&amount=<?php echo $planPrice; ?>" 
                       class="select-plan-btn plan-link">Select Plan</a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Payment History -->
        <?php if (!empty($payments)): ?>
            <div class="payment-history">
                <h2>Payment History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['plan_name'] ?? 'N/A'); ?></td>
                                <td>₹<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'N/A')); ?></td>
                                <td><?php echo ucfirst($payment['status'] ?? 'completed'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Simple, direct implementation of promo code functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Basic promo codes
            const PROMO_CODES = {
                'WELCOME10': 10,
                'SUMMER20': 20,
                'FLASH50': 50,
                'GYM25': 25
            };
            
            // Get elements once
            const promoBtn = document.getElementById('apply-promo-btn');
            const promoInput = document.getElementById('promo-code');
            const promoResult = document.getElementById('promo-result');
            
            // Add click handler to button
            promoBtn.addEventListener('click', handlePromoCode);
            
            // Also allow Enter key in the input field
            promoInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handlePromoCode();
                }
            });
            
            // Main function to handle promo code application
            function handlePromoCode() {
                // Get entered code and convert to uppercase
                const code = promoInput.value.trim().toUpperCase();
                
                // Show visual feedback that button was clicked
                promoBtn.style.backgroundColor = '#4CAF50';
                setTimeout(() => promoBtn.style.backgroundColor = '', 300);
                
                // Validate code
                if (!code) {
                    showResult('Please enter a promo code', false);
                    return;
                }
                
                // Check if valid code
                if (!PROMO_CODES[code]) {
                    showResult('Invalid promo code', false);
                    return;
                }
                
                // Valid code! Get discount percentage
                const discountPercent = PROMO_CODES[code];
                
                // Apply to all plan cards
                document.querySelectorAll('.plan-card').forEach(card => {
                    // Get data attributes
                    const originalPrice = parseFloat(card.getAttribute('data-original-price'));
                    const duration = parseInt(card.getAttribute('data-duration'));
                    const planId = card.getAttribute('data-plan-id');
                    
                    // Calculate discount
                    const discountAmount = originalPrice * (discountPercent / 100);
                    const finalPrice = originalPrice - discountAmount;
                    
                    // Round to 2 decimal places
                    const roundedPrice = Math.round(finalPrice * 100) / 100;
                    const roundedMonthly = Math.round((finalPrice / duration) * 100) / 100;
                    
                    // Remove any existing discount elements
                    const oldBadge = card.querySelector('.discount-badge');
                    if (oldBadge) oldBadge.remove();
                    
                    const oldOriginal = card.querySelector('.original-price');
                    if (oldOriginal) oldOriginal.remove();
                    
                    // Get elements to update
                    const priceEl = card.querySelector('.current-price');
                    const monthlyEl = card.querySelector('.monthly-price');
                    const linkEl = card.querySelector('.plan-link');
                    
                    // Show original price with strikethrough
                    const originalEl = document.createElement('span');
                    originalEl.className = 'original-price';
                    originalEl.textContent = '₹' + originalPrice.toFixed(2);
                    priceEl.parentNode.insertBefore(originalEl, priceEl);
                    
                    // Add discount badge
                    const badgeEl = document.createElement('span');
                    badgeEl.className = 'discount-badge';
                    badgeEl.textContent = discountPercent + '% OFF';
                    priceEl.parentNode.appendChild(badgeEl);
                    
                    // Update displayed prices
                    priceEl.textContent = '₹' + roundedPrice.toFixed(2);
                    monthlyEl.textContent = '₹' + roundedMonthly.toFixed(2) + ' / month';
                    
                    // Update payment link with discount
                    linkEl.href = `payment_form.php?customer_id=<?php echo $customer_id; ?>&plan_id=${planId}&amount=${roundedPrice}&discount_code=${code}`;
                });
                
                // Show success message
                showResult(`${discountPercent}% discount applied!`, true);
            }
            
            // Helper to show result message
            function showResult(message, isSuccess) {
                promoResult.textContent = message;
                promoResult.className = 'promo-result ' + (isSuccess ? 'promo-success' : 'promo-error');
            }
        });
    </script>
</body>
</html>