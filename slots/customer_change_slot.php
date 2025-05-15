<?php
require_once("../includes/header.php");

if (!isCustomer()) {
    echo "Unauthorized access.";
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in properly.";
    exit();
}

require_once("../includes/db.php");

$user_id = $_SESSION['user_id'];
$message = "";

// Handle slot change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_slot = trim($_POST['new_slot_time'] ?? '');

    if (!empty($new_slot)) {
        $stmt = $conn->prepare("UPDATE slots SET slot_time = ? WHERE customer_id = ?");
        $stmt->bind_param("si", $new_slot, $user_id);
        if ($stmt->execute()) {
            $message = "Slot changed successfully!";
        } else {
            $message = "Failed to change slot. Try again.";
        }
        $stmt->close();
    } else {
        $message = "Please enter a valid slot time.";
    }
}

// Fetch current slot
$current_slot = '';
$result = $conn->query("SELECT slot_time FROM slots WHERE customer_id = $user_id LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_slot = $row['slot_time'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Slot</title>
    <style>
        body {
            background: url('../assets/images/customer_bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #fff;
        }
        .container {
            max-width: 600px;
            margin: 60px auto;
            background: rgba(0,0,0,0.75);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(255,255,255,0.2);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #ffc107;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: none;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #ffc107;
            color: #000;
            font-size: 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #e0a800;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: #00ffcc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Change Your Slot</h2>

        <?php if ($current_slot): ?>
            <p><strong>Current Slot:</strong> <?= htmlspecialchars($current_slot) ?></p>
        <?php else: ?>
            <p><strong>No slot currently booked.</strong></p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="customer_change_slot.php">
            <label for="new_slot_time">New Slot Time:</label>
            <input type="text" id="new_slot_time" name="new_slot_time" required>

            <button type="submit" class="btn">Update Slot</button>
        </form>
    </div>
</body>
</html>
