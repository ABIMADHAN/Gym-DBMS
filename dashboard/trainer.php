<?php
require_once("../includes/header.php");
if (!isTrainer()) exit();
require_once("../includes/db.php");

$trainer_id = $_SESSION['user']['id'];

// Handle logout
if (isset($_GET['logout'])) {
    session_start();
    session_destroy(); // Destroy the session
    header("Location: ../login.php"); // Redirect to the login page
    exit;
}

// Handle Approve/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
    $update = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $update->execute([$new_status, $booking_id]);
    // Optional: reload the page to reflect changes
    header("Location: trainer.php?view_booking_list=1");
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        bookings.id AS booking_id,
        users.name AS customer_name,
        bookings.status,
        slots.slot_time
    FROM bookings
    JOIN slots ON bookings.slot_id = slots.id
    JOIN users ON bookings.customer_name = users.name
    WHERE slots.trainer_id = ?
    ORDER BY bookings.created_at DESC
");
$stmt->execute([$trainer_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

function viewBooking($booking) {
    echo "<div style='margin:30px 0;padding:20px;background:#263238;border-radius:10px;'>";
    echo "<h3>Booking Details</h3>";
    echo "<strong>Customer:</strong> " . htmlspecialchars($booking['customer_name']) . "<br>";
    echo "<strong>Slot Time:</strong> " . htmlspecialchars($booking['slot_time']) . "<br>";
    echo "<strong>Status:</strong> " . htmlspecialchars($booking['status']) . "<br>";
    echo "</div>";
}

function showProgressReport($pdo, $customer_id) {
    // Fetch all progress records for this customer
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$customer_id]);
    $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$progress) {
        echo "<div style='margin:20px 0;padding:15px;background:#263238;border-radius:10px;'>No progress data found for this customer.</div>";
        return;
    }

    $latest = $progress[0];
    $earliest = end($progress);

    $weight_change = $latest['weight'] - $earliest['weight'];
    $body_fat_change = $latest['body_fat'] - $earliest['body_fat'];
    $muscle_mass_change = $latest['muscle_mass'] - $earliest['muscle_mass'];
    $waist_change = $latest['waist'] - $earliest['waist'];

    echo "<div style='margin:20px 0;padding:20px;background:#263238;border-radius:10px;'>";
    echo "<h4>Progress Report for Customer ID: " . htmlspecialchars($customer_id) . "</h4>";
    echo "<strong>Latest Weight:</strong> " . htmlspecialchars($latest['weight']) . " kg<br>";
    echo "<strong>Weight Change:</strong> " . ($weight_change >= 0 ? "+" : "") . htmlspecialchars($weight_change) . " kg<br>";
    echo "<strong>Latest Body Fat:</strong> " . htmlspecialchars($latest['body_fat']) . " %<br>";
    echo "<strong>Body Fat Change:</strong> " . ($body_fat_change >= 0 ? "+" : "") . htmlspecialchars($body_fat_change) . " %<br>";
    echo "<strong>Latest Muscle Mass:</strong> " . htmlspecialchars($latest['muscle_mass']) . " kg<br>";
    echo "<strong>Muscle Mass Change:</strong> " . ($muscle_mass_change >= 0 ? "+" : "") . htmlspecialchars($muscle_mass_change) . " kg<br>";
    echo "<strong>Latest Waist:</strong> " . htmlspecialchars($latest['waist']) . " cm<br>";
    echo "<strong>Waist Change:</strong> " . ($waist_change >= 0 ? "+" : "") . htmlspecialchars($waist_change) . " cm<br>";
    echo "<strong>Latest Hip:</strong> " . htmlspecialchars($latest['hip']) . " cm<br>";
    echo "<strong>Latest Chest:</strong> " . htmlspecialchars($latest['chest']) . " cm<br>";
    echo "<strong>Latest Heart Rate:</strong> " . htmlspecialchars($latest['heart_rate']) . " bpm<br>";
    echo "<strong>Latest Workout Frequency:</strong> " . htmlspecialchars($latest['workout_freq']) . " sessions/week<br>";
    echo "<strong>Latest Energy Level:</strong> " . htmlspecialchars($latest['energy']) . "<br>";
    echo "<strong>Latest Sleep Hours:</strong> " . htmlspecialchars($latest['sleep']) . " hrs/night<br>";
    echo "<strong>Latest Water Intake:</strong> " . htmlspecialchars($latest['water']) . " L/day<br>";
    echo "<strong>Latest Mood/Stress Level:</strong> " . htmlspecialchars($latest['mood']) . "<br>";
    echo "<strong>Latest Note:</strong> " . nl2br(htmlspecialchars($latest['note'])) . "<br>";
    echo "<strong>Last Updated:</strong> " . htmlspecialchars($latest['created_at']) . "<br>";

    // --- Advanced Analysis & Suggestions ---
    echo "<hr style='border:1px solid #444;'>";
    echo "<h4>Advanced Analysis & Suggestions</h4>";

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

    echo "</div>";
}

$showBooking = null;
if (isset($_GET['view_booking_id'])) {
    foreach ($bookings as $b) {
        if ($b['booking_id'] == $_GET['view_booking_id']) {
            $showBooking = $b;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trainer Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../images/dashboard-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }
        h2 {
            text-align: center;
            color: #4dd0e1;
            font-size: 2.5em;
            letter-spacing: 2px;
            margin-bottom: 10px;
            text-shadow: 0 2px 8px #1976d2aa;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background-color: #1976d2;
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease;
            font-size: 1.2em;
            box-shadow: 0 2px 8px #1976d2aa;
            position: relative;
            overflow: hidden;
            animation: popIn 1s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes popIn {
            0% { transform: scale(0.7) rotate(-10deg); opacity: 0; }
            80% { transform: scale(1.05) rotate(2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0); }
        }
        .card:hover {
            transform: scale(1.08) rotate(-2deg);
            box-shadow: 0 4px 16px #4dd0e1aa;
        }
        .card .emoji {
            font-size: 2em;
            margin-bottom: 8px;
            display: block;
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
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feedback-container {
            animation: slideIn 0.5s ease-in-out;
        }
        a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            color: white;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #444;
            text-align: left;
        }
        th {
            background: #1976d2;
            font-size: 1.1em;
        }
        tr:hover {
            background: #263238;
        }
        .view-btn {
            background: #4dd0e1;
            color: #222;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            font-size: 1em;
            margin-right: 5px;
            transition: background 0.2s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .view-btn:hover {
            background: #1976d2;
            color: #fff;
            transform: scale(1.08) rotate(-2deg);
        }
        .btn-emoji {
            font-size: 1.2em;
            animation: crazySpin 2.5s infinite linear;
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
    </style>
</head>
<body>
<div class="dashboard-container" id="confetti-container">
    <h2>👨‍🏫 Welcome, Trainer!</h2>
    <a href="trainer.php" class="back-link">⬅️ Back to Dashboard</a>
    <div class="cards">
        <?php if (isset($_GET['view_booking_list'])): ?>
            <div class="card"><span class="emoji">📅</span>
                <a href="trainer.php" style="background:#e53935;">Hide Booking</a>
            </div>
        <?php else: ?>
            <div class="card"><span class="emoji">📅</span>
                <a href="trainer.php?view_booking_list=1">View Booking</a>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['view_report_list'])): ?>
            <div class="card"><span class="emoji">📊</span>
                <a href="trainer.php" style="background:#e53935;">Hide Reports</a>
            </div>
        <?php else: ?>
            <div class="card"><span class="emoji">📊</span>
                <a href="trainer.php?view_report_list=1">View Reports</a>
            </div>
        <?php endif; ?>
        <div class="card">
            <span class="emoji">💰</span>
            <a href="fee_status.php" class="fee-status-button">Fee Status</a>
        </div>
        <div class="card">
            <span class="emoji">💬</span>
            <a href="trainer.php?view_feedback_list=1">View Feedback</a>
        </div>
        <div class="card">
            <span class="emoji">🚪</span>
            <a href="trainer.php?logout=1">Logout</a>
        </div>
    </div>

    <?php if (isset($_GET['view_booking_list'])): ?>
        <table>
            <tr>
                <th>👤 Customer</th>
                <th>⏰ Slot Time</th>
                <th>📋 Status</th>
                <th>🔍 View Booking</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
            <tr>
                <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                <td><?= htmlspecialchars($booking['slot_time']) ?></td>
                <td>
                    <?php if (
                        isset($_GET['action_booking_id']) && 
                        $_GET['action_booking_id'] == $booking['booking_id'] && 
                        $booking['status'] === 'pending'
                    ): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                            <button type="submit" name="action" value="approve" class="view-btn" style="background:#43a047;color:#fff;">
                                <span class="btn-emoji">✅</span>Approve
                            </button>
                            <button type="submit" name="action" value="reject" class="view-btn" style="background:#e53935;color:#fff;">
                                <span class="btn-emoji">❌</span>Reject
                            </button>
                        </form>
                    <?php else: ?>
                        <?php if ($booking['status'] === 'pending'): ?>
                            <a class="view-btn" href="?view_booking_list=1&action_booking_id=<?= $booking['booking_id'] ?>">
                                <span class="btn-emoji">⏳</span><?= htmlspecialchars($booking['status']) ?>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($booking['status']) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a class="view-btn" href="?view_booking_id=<?= $booking['booking_id'] ?>&view_booking_list=1">
                        <span class="btn-emoji">🔍</span>View Booking
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($showBooking) { viewBooking($showBooking); } ?>
    <?php endif; ?>

    <?php if (isset($_GET['view_report_list'])): ?>
        <?php
        // Fetch all customers who have progress records
        $stmt = $pdo->prepare("
            SELECT DISTINCT users.id, users.name
            FROM progress
            JOIN users ON progress.customer_id = users.id
            WHERE users.role = 'customer'
        ");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <h3 style="margin-top:30px;">📈 Customer Progress Reports</h3>
        <table>
            <tr>
                <th>👤 Customer</th>
                <th>⚡ Action</th>
            </tr>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= htmlspecialchars($customer['name']) ?></td>
                <td>
                    <a class="view-btn" href="trainer.php?view_report_list=1&customer_id=<?= $customer['id'] ?>">
                        <span class="btn-emoji">👁️</span>View Report
                    </a>
                    <a class="view-btn" style="background:#43a047;color:#fff;margin-left:10px;" href="analyze_report.php?customer_id=<?= $customer['id'] ?>">
                        <span class="btn-emoji">🧠</span>Analyze
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        // If a customer is selected, show their report
        if (isset($_GET['customer_id'])) {
            showProgressReport($pdo, intval($_GET['customer_id']));
        }
        // If "Analyze" is clicked, show the analyzed report
        if (isset($_GET['analyze_id'])) {
            echo "<div style='margin-top:30px;'>";
            echo "<h3>🧠 Analyzed Progress Report</h3>";
            showProgressReport($pdo, intval($_GET['analyze_id']));
            echo "</div>";
        }
        ?>
    <?php endif; ?>

    <?php if (isset($_GET['view_feedback_list'])): ?>
        <?php
        // Fetch feedback from the database
        $stmt = $pdo->prepare("
            SELECT f.feedback, f.rating, f.created_at, u.name AS customer_name
            FROM feedback f
            JOIN users u ON f.customer_id = u.id
            ORDER BY f.created_at DESC
        ");
        $stmt->execute();
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="feedback-section">
            <h3 style="margin-top:30px;">💬 Customer Feedback</h3>
            <table>
                <tr>
                    <th>👤 Customer</th>
                    <th>📝 Feedback</th>
                    <th>⭐ Rating</th>
                    <th>📅 Submitted On</th>
                </tr>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td><?= htmlspecialchars($feedback['customer_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($feedback['feedback'])) ?></td>
                    <td><?= str_repeat('⭐', $feedback['rating']) ?></td>
                    <td><?= htmlspecialchars($feedback['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
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
    // Burst confetti on load and on clicking any card
    const container = document.getElementById('confetti-container');
    for (let i = 0; i < 30; i++) setTimeout(() => createConfettiPiece(container), i * 60);
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', () => {
            for (let i = 0; i < 20; i++) setTimeout(() => createConfettiPiece(container), i * 30);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const viewFeedbackButton = document.querySelector('a[href*="view_feedback_list"]');
        if (viewFeedbackButton) {
            viewFeedbackButton.addEventListener('click', () => {
                const feedbackSection = document.querySelector('.feedback-section');
                if (feedbackSection) {
                    feedbackSection.classList.add('feedback-container');
                }
            });
        }
    });
</script>
</body>
</html>
