<?php
require_once("../includes/header.php");
if (!isTrainer()) exit();
require_once("../config/db.php");

$result = $conn->query("SELECT users.name, slot_time FROM slots JOIN users ON slots.user_id = users.id WHERE status = 'cancelled'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancelled Slots</title>
    <style>
        body { font-family: Arial; background: #fcfcfc; padding: 40px; }
        .container { max-width: 700px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; }
        h2 { text-align: center; color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background: #dc3545; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h2>Cancelled Slots</h2>
    <table>
        <tr>
            <th>Customer</th>
            <th>Cancelled Slot</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['slot_time']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
