<?php
require_once("../includes/header.php");
if (!isTrainer()) exit();
require_once("../config/db.php");

$result = $conn->query("SELECT slot_time FROM slots WHERE status = 'free'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Free Slots</title>
    <style>
        body { background: #f3f3f3; font-family: Arial; padding: 40px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; }
        h2 { text-align: center; }
        ul { list-style: none; padding: 0; }
        li { padding: 10px; border-bottom: 1px solid #ddd; font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Free Slots Available</h2>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li><?= htmlspecialchars($row['slot_time']) ?></li>
        <?php endwhile; ?>
    </ul>
</div>
</body>
</html>
