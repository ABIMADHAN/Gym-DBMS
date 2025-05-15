<?php

require_once("../includes/header.php");
require_once("../includes/db.php");

if (!isTrainer()) exit();

if (!isset($_GET['id'])) {
    echo "No booking selected.";
    exit;
}

$booking_id = intval($_GET['id']);

// Handle Approve/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $new_status = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
    $update = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $update->execute([$new_status, $booking_id]);
    echo "<script>alert('Booking $new_status!');window.location.href='trainer.php';</script>";
    exit;
}

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT 
        bookings.id AS booking_id,
        users.name AS customer_name,
        bookings.status,
        slots.slot_time
    FROM bookings
    JOIN slots ON bookings.slot_id = slots.id
    JOIN users ON bookings.customer_name = users.name
    WHERE bookings.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details</title>
    <style>
        body { background: #181d23; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .details-container {
            max-width: 400px;
            margin: 60px auto;
            background: #222831;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2 { color: #4dd0e1; text-align: center; }
        .info { margin: 20px 0; }
        .actions { text-align: center; margin-top: 30px; }
        .actions button {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .approve { background: #43a047; color: #fff; }
        .reject { background: #e53935; color: #fff; }
        a { color: #4dd0e1; display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="details-container">
    <h2>Booking Details</h2>
    <div class="info"><strong>Customer:</strong> <?= htmlspecialchars($booking['customer_name']) ?></div>
    <div class="info"><strong>Slot Time:</strong> <?= htmlspecialchars($booking['slot_time']) ?></div>
    <div class="info"><strong>Status:</strong> <?= htmlspecialchars($booking['status']) ?></div>
    <?php if ($booking['status'] === 'pending'): ?>
    <form method="post" class="actions">
        <button type="submit" name="action" value="approve" class="approve">Approve</button>
        <button type="submit" name="action" value="reject" class="reject">Reject</button>
    </form>
    <?php else: ?>
        <div class="info"><em>This booking has been <?= htmlspecialchars($booking['status']) ?>.</em></div>
    <?php endif; ?>
    <a href="trainer.php">Back to Dashboard</a>
</div>
</body>
</html>