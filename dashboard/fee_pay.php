<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $plan_id = intval($_POST['plan_id']);
    $amount_paid = floatval($_POST['amount_paid']);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO fee_payments (customer_id, plan_id, amount_paid, payment_date)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$customer_id, $plan_id, $amount_paid]);
        echo "Payment recorded successfully.";
    } catch (PDOException $e) {
        echo "Error recording payment: " . $e->getMessage();
    }
}
?>