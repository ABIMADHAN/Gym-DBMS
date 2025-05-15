<?php
require_once("../includes/header.php");
require_once("../includes/db.php");

if (!isTrainer()) exit();

if (!isset($_GET['customer_id'])) {
    echo "No customer selected.";
    exit;
}

$customer_id = intval($_GET['customer_id']);

// Fetch customer info
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch progress data
$stmt = $pdo->prepare("SELECT * FROM progress WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customer_id]);
$progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$progress) {
    echo "<div style='margin:20px 0;padding:15px;background:#263238;border-radius:10px;'>No progress data found for this customer.</div>";
    exit;
}

$latest = $progress[0];
$earliest = end($progress);

$weight_change = $latest['weight'] - $earliest['weight'];
$body_fat_change = $latest['body_fat'] - $earliest['body_fat'];
$muscle_mass_change = $latest['muscle_mass'] - $earliest['muscle_mass'];
$waist_change = $latest['waist'] - $earliest['waist'];

// Prepare data for the chart
$dates = [];
$weights = [];
foreach (array_reverse($progress) as $row) { // oldest to newest
    $dates[] = date('M d', strtotime($row['created_at']));
    $weights[] = $row['weight'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Analysis Report</title>
    <style>
        body { background: #181d23; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .report-container {
            max-width: 600px;
            margin: 40px auto;
            background: #222831;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2, h3 { color: #4dd0e1; text-align: center; }
        .section { margin-bottom: 25px; }
        .back-link { color: #4dd0e1; display: block; text-align: center; margin-top: 30px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="report-container">
    <h2>Advanced Analysis Report</h2>
    <h3><?= htmlspecialchars($customer['name']) ?></h3>
    <div class="section">
        <strong>Latest Weight:</strong> <?= htmlspecialchars($latest['weight']) ?> kg<br>
        <strong>Weight Change:</strong> <?= ($weight_change >= 0 ? "+" : "") . htmlspecialchars($weight_change) ?> kg<br>
        <strong>Latest Body Fat:</strong> <?= htmlspecialchars($latest['body_fat']) ?> %<br>
        <strong>Body Fat Change:</strong> <?= ($body_fat_change >= 0 ? "+" : "") . htmlspecialchars($body_fat_change) ?> %<br>
        <strong>Latest Muscle Mass:</strong> <?= htmlspecialchars($latest['muscle_mass']) ?> kg<br>
        <strong>Muscle Mass Change:</strong> <?= ($muscle_mass_change >= 0 ? "+" : "") . htmlspecialchars($muscle_mass_change) ?> kg<br>
        <strong>Latest Waist:</strong> <?= htmlspecialchars($latest['waist']) ?> cm<br>
        <strong>Waist Change:</strong> <?= ($waist_change >= 0 ? "+" : "") . htmlspecialchars($waist_change) ?> cm<br>
        <strong>Latest Hip:</strong> <?= htmlspecialchars($latest['hip']) ?> cm<br>
        <strong>Latest Chest:</strong> <?= htmlspecialchars($latest['chest']) ?> cm<br>
        <strong>Latest Heart Rate:</strong> <?= htmlspecialchars($latest['heart_rate']) ?> bpm<br>
        <strong>Latest Workout Frequency:</strong> <?= htmlspecialchars($latest['workout_freq']) ?> sessions/week<br>
        <strong>Latest Energy Level:</strong> <?= htmlspecialchars($latest['energy']) ?><br>
        <strong>Latest Sleep Hours:</strong> <?= htmlspecialchars($latest['sleep']) ?> hrs/night<br>
        <strong>Latest Water Intake:</strong> <?= htmlspecialchars($latest['water']) ?> L/day<br>
        <strong>Latest Mood/Stress Level:</strong> <?= htmlspecialchars($latest['mood']) ?><br>
        <strong>Latest Note:</strong> <?= nl2br(htmlspecialchars($latest['note'])) ?><br>
        <strong>Last Updated:</strong> <?= htmlspecialchars($latest['created_at']) ?><br>
    </div>
    <hr>
    <div class="section">
        <h4 style="text-align:center;">Weight Progress Chart</h4>
        <canvas id="weightChart" height="100"></canvas>
    </div>
    <script>
        const ctx = document.getElementById('weightChart').getContext('2d');
        const weightChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Weight (kg)',
                    data: <?= json_encode($weights) ?>,
                    borderColor: '#4dd0e1',
                    backgroundColor: 'rgba(77,208,225,0.1)',
                    tension: 0.3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4dd0e1',
                    pointRadius: 5,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { color: '#fff' },
                        grid: { color: '#444' }
                    },
                    x: {
                        ticks: { color: '#fff' },
                        grid: { color: '#444' }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#fff' } }
                }
            }
        });
    </script>
    <div class="section">
        <h4>Advanced Analysis & Suggestions</h4>
        <?php
        // Weight trend
        if ($weight_change < 0) {
            echo "✅ <strong>Good job!</strong> You've lost weight since your first record.<br>";
        } elseif ($weight_change > 0) {
            echo "⚠️ <strong>Notice:</strong> Your weight has increased. Review your diet and workout plan.<br>";
        } else {
            echo "ℹ️ <strong>Stable:</strong> Your weight is stable.<br>";
        }

        // Body fat trend
        if ($body_fat_change < 0) {
            echo "✅ <strong>Body fat percentage has decreased.</strong> Keep up the good work!<br>";
        } elseif ($body_fat_change > 0) {
            echo "⚠️ <strong>Body fat percentage increased.</strong> Consider more cardio or adjusting nutrition.<br>";
        } else {
            echo "ℹ️ <strong>Body fat is stable.</strong><br>";
        }

        // Muscle mass trend
        if ($muscle_mass_change > 0) {
            echo "✅ <strong>Muscle mass increased.</strong> Great progress in strength training!<br>";
        } elseif ($muscle_mass_change < 0) {
            echo "⚠️ <strong>Muscle mass decreased.</strong> Consider increasing protein intake and resistance training.<br>";
        } else {
            echo "ℹ️ <strong>Muscle mass is stable.</strong><br>";
        }

        // Waist trend
        if ($waist_change < 0) {
            echo "✅ <strong>Waist circumference decreased.</strong> Indicates fat loss.<br>";
        } elseif ($waist_change > 0) {
            echo "⚠️ <strong>Waist circumference increased.</strong> Monitor your nutrition and activity.<br>";
        } else {
            echo "ℹ️ <strong>Waist circumference is stable.</strong><br>";
        }

        // Sleep and energy
        if ($latest['sleep'] < 7) {
            echo "💤 <strong>Try to get at least 7 hours of sleep for optimal recovery.</strong><br>";
        }
        if ($latest['energy'] < 5) {
            echo "⚡ <strong>Low energy reported.</strong> Review your nutrition, sleep, and stress levels.<br>";
        }

        // Mood/Stress
        if ($latest['mood'] < 5) {
            echo "🧘 <strong>High stress or low mood detected.</strong> Consider relaxation techniques or talking to a coach.<br>";
        }

        // Trend analysis over time (simple example)
        if (count($progress) > 2) {
            $mid = $progress[intval(count($progress)/2)];
            $mid_weight = $mid['weight'];
            $mid_date = $mid['created_at'];
            echo "<br><strong>Midpoint Weight (" . htmlspecialchars($mid_date) . "):</strong> " . htmlspecialchars($mid_weight) . " kg<br>";

            if ($latest['weight'] < $mid_weight && $mid_weight < $earliest['weight']) {
                echo "📉 <strong>Consistent weight loss trend detected.</strong><br>";
            } elseif ($latest['weight'] > $mid_weight && $mid_weight > $earliest['weight']) {
                echo "📈 <strong>Consistent weight gain trend detected.</strong><br>";
            } else {
                echo "🔄 <strong>Fluctuating weight trend detected.</strong><br>";
            }
        }

        // Suggest seeing a trainer if multiple negative indicators
        $negative = 0;
        if ($weight_change > 0) $negative++;
        if ($body_fat_change > 0) $negative++;
        if ($latest['energy'] < 5) $negative++;
        if ($latest['mood'] < 5) $negative++;
        if ($negative >= 3) {
            echo "<br>❗ <strong>Multiple negative trends detected. Consider scheduling a personal consultation.</strong><br>";
        }
        ?>
    </div>
    <a class="back-link" href="trainer.php?view_report_list=1">← Back to Reports</a>
</div>
</body>
</html>