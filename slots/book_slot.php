<?php
require_once("../includes/header.php");
require_once("../includes/db.php");
if (!isCustomer()) exit();

$user_id = $_SESSION['user']['id'];

// Fetch available slots (not booked)
$stmt = $pdo->prepare("SELECT id, slot_time FROM slots WHERE status = 'available'");
$stmt->execute();
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']);

    // Get customer name
    $user_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mark slot as booked and assign to user
        $update = $pdo->prepare("UPDATE slots SET status = 'booked', user_id = ? WHERE id = ?");
        $update->execute([$user_id, $slot_id]);

        // Insert booking record
        $slot_stmt = $pdo->prepare("SELECT slot_time FROM slots WHERE id = ?");
        $slot_stmt->execute([$slot_id]);
        $slot = $slot_stmt->fetch(PDO::FETCH_ASSOC);

        $insert = $pdo->prepare("INSERT INTO bookings (customer_name, slot_id, status, slot_time) VALUES (?, ?, 'pending', ?)");
        $insert->execute([$user['name'], $slot_id, $slot['slot_time']]);

        echo "<script>alert('🎉 Slot booked successfully!');window.location.href='book_slot.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Slot</title>
    <style>
        body { 
            background: linear-gradient(135deg, #f7f7f7 0%, #4dd0e1 100%);
            font-family: Arial, sans-serif; 
            padding: 40px; 
            min-height: 100vh;
        }
        .form-container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            padding: 30px 30px 40px 30px;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            position: relative;
            overflow: hidden;
            animation: popIn 1s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes popIn {
            0% { transform: scale(0.7) rotate(-10deg); opacity: 0; }
            80% { transform: scale(1.05) rotate(2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0); }
        }
        h2 {
            text-align: center; 
            margin-bottom: 20px;
            color: #1976d2;
            font-size: 2em;
            letter-spacing: 1px;
        }
        label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #1976d2;
            font-size: 1.1em;
        }
        select {
            padding: 12px;
            border: 1px solid #4dd0e1;
            border-radius: 10px;
            font-size: 1.1em;
            margin-bottom: 20px;
            background: #e0f7fa;
            transition: border 0.3s;
        }
        select:focus {
            border: 2px solid #1976d2;
        }
        button {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            color: #222;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1.15em;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            box-shadow: 0 2px 8px #1976d233;
            transition: background 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        button:hover {
            background: linear-gradient(90deg, #38f9d7 0%, #43e97b 100%);
            transform: scale(1.04) rotate(-2deg);
        }
        .slot-emoji {
            font-size: 1.4em;
            margin-right: 8px;
            animation: crazySpin 2.5s infinite linear;
        }
        @keyframes crazySpin {
            0% { transform: rotate(0deg) scale(1);}
            20% { transform: rotate(20deg) scale(1.1);}
            40% { transform: rotate(-20deg) scale(1.2);}
            60% { transform: rotate(10deg) scale(1.1);}
            80% { transform: rotate(-10deg) scale(1);}
            100% { transform: rotate(0deg) scale(1);}
        }
        /* Confetti effect */
        .confetti {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            opacity: 0.7;
            pointer-events: none;
            z-index: 1;
        }
    </style>
</head>
<body>
<div class="form-container" id="confetti-container">
    <h2>🎟️ Book a Workout Slot</h2>
    <form method="POST">
        <label><span class="slot-emoji">⏰</span>Select Available Slot:</label>
        <select name="slot_id" required>
            <option value="">-- Select Slot --</option>
            <?php foreach ($slots as $slot): ?>
                <option value="<?= $slot['id'] ?>">🗓️ <?= htmlspecialchars($slot['slot_time']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><span class="slot-emoji">✅</span>Book Slot</button>
    </form>
</div>
<script>
    // Confetti animation for fun!
    function randomColor() {
        const colors = ['#4dd0e1', '#1976d2', '#ffeb3b', '#ff4081', '#69f0ae', '#ffd740', '#ff5252'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    function createConfettiPiece(container) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.background = randomColor();
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.top = '-20px';
        confetti.style.opacity = Math.random() * 0.5 + 0.5;
        confetti.style.width = confetti.style.height = (Math.random() * 8 + 8) + 'px';
        confetti.style.transition = 'top 2.5s linear, left 2.5s linear, opacity 2.5s linear';
        container.appendChild(confetti);
        setTimeout(() => {
            confetti.style.top = (Math.random() * 80 + 20) + '%';
            confetti.style.left = (Math.random() * 100) + '%';
            confetti.style.opacity = 0;
        }, 10);
        setTimeout(() => {
            confetti.remove();
        }, 2600);
    }
    // Burst confetti on load and on booking
    const container = document.getElementById('confetti-container');
    for (let i = 0; i < 25; i++) setTimeout(() => createConfettiPiece(container), i * 60);
    container.querySelector('form').addEventListener('submit', () => {
        for (let i = 0; i < 20; i++) setTimeout(() => createConfettiPiece(container), i * 30);
    });
</script>
</body>
</html>
