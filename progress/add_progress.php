<?php

require_once("../includes/header.php");
require_once("../includes/db.php");

if (!isCustomer()) exit();

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = $_POST['weight'];
    $body_fat = $_POST['body_fat'];
    $muscle_mass = $_POST['muscle_mass'];
    $waist = $_POST['waist'];
    $hip = $_POST['hip'];
    $chest = $_POST['chest'];
    $heart_rate = $_POST['heart_rate'];
    $workout_freq = $_POST['workout_freq'];
    $energy = $_POST['energy'];
    $sleep = $_POST['sleep'];
    $water = $_POST['water'];
    $mood = $_POST['mood'];
    $note = $_POST['note'];

    $stmt = $pdo->prepare("INSERT INTO progress (customer_id, weight, body_fat, muscle_mass, waist, hip, chest, heart_rate, workout_freq, energy, sleep, water, mood, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $weight, $body_fat, $muscle_mass, $waist, $hip, $chest, $heart_rate, $workout_freq, $energy, $sleep, $water, $mood, $note]);

    echo "<script>alert('🎉 Progress added!');window.location.href='../dashboard/customer.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Progress</title>
    <style>
        body { background: linear-gradient(135deg, #f7f7f7 0%, #4dd0e1 100%); font-family: Arial, sans-serif; padding: 40px; min-height: 100vh; }
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
        h2 { text-align: center; margin-bottom: 20px; color: #1976d2; font-size: 2em; letter-spacing: 1px;}
        label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #1976d2;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        input, textarea {
            padding: 10px;
            border: 1px solid #4dd0e1;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 1.08em;
            background: #e0f7fa;
            transition: border 0.3s;
        }
        input:focus, textarea:focus {
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
        .icon-emoji {
            font-size: 1.3em;
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
    <h2>📈 Add Your Progress</h2>
    <form method="POST">
        <label><span class="icon-emoji">⚖️</span>Weight (kg):</label>
        <input type="number" name="weight" step="0.1" required>
        <label><span class="icon-emoji">💧</span>Body Fat (%):</label>
        <input type="number" name="body_fat" step="0.1" required>
        <label><span class="icon-emoji">💪</span>Muscle Mass (kg):</label>
        <input type="number" name="muscle_mass" step="0.1">
        <label><span class="icon-emoji">📏</span>Waist Circumference (cm):</label>
        <input type="number" name="waist" step="0.1">
        <label><span class="icon-emoji">🍑</span>Hip Circumference (cm):</label>
        <input type="number" name="hip" step="0.1">
        <label><span class="icon-emoji">🏋️‍♂️</span>Chest Circumference (cm):</label>
        <input type="number" name="chest" step="0.1">
        <label><span class="icon-emoji">❤️</span>Resting Heart Rate (bpm):</label>
        <input type="number" name="heart_rate" step="1">
        <label><span class="icon-emoji">📅</span>Workout Frequency (sessions/week):</label>
        <input type="number" name="workout_freq" step="1">
        <label><span class="icon-emoji">⚡</span>Energy Level (1-10):</label>
        <input type="number" name="energy" min="1" max="10">
        <label><span class="icon-emoji">😴</span>Sleep Hours (per night):</label>
        <input type="number" name="sleep" step="0.1">
        <label><span class="icon-emoji">🚰</span>Water Intake (liters/day):</label>
        <input type="number" name="water" step="0.1">
        <label><span class="icon-emoji">😊</span>Mood/Stress Level (1-10):</label>
        <input type="number" name="mood" min="1" max="10">
        <label><span class="icon-emoji">📝</span>Note:</label>
        <textarea name="note" rows="3"></textarea>
        <button type="submit"><span class="icon-emoji">✅</span>Submit Progress</button>
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
    // Burst confetti on load and on submit
    const container = document.getElementById('confetti-container');
    for (let i = 0; i < 25; i++) setTimeout(() => createConfettiPiece(container), i * 60);
    container.querySelector('form').addEventListener('submit', () => {
        for (let i = 0; i < 20; i++) setTimeout(() => createConfettiPiece(container), i * 30);
    });
</script>
</body>
</html>