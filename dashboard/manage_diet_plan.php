<?php
require_once("../includes/header.php");
if (!isTrainer()) exit();
require_once("../config/db.php");

if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    function analyzeProgressForDiet($pdo, $customer_id) {
        $stmt = $pdo->prepare("SELECT * FROM progress WHERE customer_id = ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$customer_id]);
        $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($progress) < 2) {
            return "<div class='suggestion-container'><p class='suggestion-line idea'><i class='fas fa-exclamation-triangle crazy-alert'></i> Not enough progress data to suggest a diet plan. Encourage the customer to add more entries.</p></div>";
        }

        $latest = $progress[0];
        $previous = $progress[1];
        $suggestions = "<div class='suggestion-container'>";
        $suggestions .= "<p class='suggestion-line idea'><i class='fas fa-lightbulb crazy-bulb'></i> Based on recent progress:</p>";

        if ($latest['weight'] > $previous['weight']) {
            $weightChange = $latest['weight'] - $previous['weight'];
            $suggestions .= "<p class='suggestion-line weight-up'><i class='fas fa-arrow-up crazy-arrow-up'></i> ⬆️ Your weight has increased by <span class='emphasis'>" . round($weightChange, 2) . " kg</span> recently. <span class='advice'>Consider reducing calorie intake slightly or increasing physical activity</span> 💪.</p>";
        } elseif ($latest['weight'] < $previous['weight']) {
            $weightChange = $previous['weight'] - $latest['weight'];
            $suggestions .= "<p class='suggestion-line weight-down'><i class='fas fa-arrow-down crazy-arrow-down'></i> ⬇️ Great job! Your weight has decreased by <span class='emphasis'>" . round($weightChange, 2) . " kg</span>. <span class='advice'>Continue with your current approach, ensuring a balanced diet</span> 👍.</p>";
        } else {
            $suggestions .= "<p class='suggestion-line weight-stable'><i class='fas fa-balance-scale crazy-balance'></i> Your weight appears stable. <span class='advice'>Maintain your current diet and exercise routine</span> 😊.</p>";
        }

        if (isset($latest['body_fat']) && isset($previous['body_fat'])) {
            if ($latest['body_fat'] > $previous['body_fat']) {
                $fatChange = $latest['body_fat'] - $previous['body_fat'];
                $suggestions .= "<p class='suggestion-line fat-up'><i class='fas fa-tint crazy-tint'></i> 脂肪 ⬆️ Your body fat percentage has increased by <span class='emphasis'>" . round($fatChange, 2) . "%</span>. <span class='advice'>Focus on whole, unprocessed foods and consider cardiovascular exercises</span> 🏃‍♀️.</p>";
            } elseif ($latest['body_fat'] < $previous['body_fat']) {
                $fatChange = $previous['body_fat'] - $latest['body_fat'];
                $suggestions .= "<p class='suggestion-line fat-down'><i class='fas fa-check-double crazy-check-double'></i> ✅ Your body fat percentage has decreased by <span class='emphasis'>" . round($fatChange, 2) . "%</span>. <span class='advice'>Excellent progress! Continue your healthy habits</span> 🎉.</p>";
            } else {
                $suggestions .= "<p class='suggestion-line fat-stable'><i class='fas fa-equals crazy-equals'></i> Your body fat percentage is stable.</p>";
            }
        } else {
            $suggestions .= "<p class='suggestion-line fat-na'><i class='fas fa-question-circle crazy-question'></i> Body fat data is not consistently available for specific recommendations.</p>";
        }

        if (isset($latest['water']) && $latest['water'] < 2.5) {
            $suggestions .= "<p class='suggestion-line water-low'><i class='fas fa-glass-whiskey crazy-water'></i> 💧 Your water intake seems low. <span class='advice'>Aim for at least 2.5-3 liters of water per day</span> 💦.</p>";
        }

        $suggestions .= "<p class='suggestion-line general-tips'><i class='fas fa-utensils crazy-utensils'></i> <span class='emphasis'>**General Dietary Tips:**</span></p>";
        $suggestions .= "<p class='suggestion-line balanced'><i class='fas fa-yin-yang crazy-yin-yang'></i> Focus on a balanced intake of protein, carbohydrates, and healthy fats.</p>";
        $suggestions .= "<p class='suggestion-line fruits'><i class='fas fa-carrot crazy-carrot'></i> Include plenty of fruits and vegetables in your diet 🥕🥦.</p>";
        $suggestions .= "<p class='suggestion-line limit'><i class='fas fa-pizza-slice crazy-pizza'></i> Limit processed foods, sugary drinks, and excessive unhealthy fats 🍕🥤.</p>";
        $suggestions .= "<p class='suggestion-line listen'><i class='fas fa-heartbeat crazy-heartbeat'></i> Listen to your body's hunger and fullness cues ❤️.</p>";
        $suggestions .= "<p class='suggestion-line consult'><i class='fas fa-stethoscope crazy-stethoscope'></i> Consider consulting a registered dietitian for personalized guidance 🍎.</p>";
        $suggestions .= "</div>";

        return $suggestions;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diet_plan_content'])) {
        $diet_plan = $_POST['diet_plan_content'];

        $existingPlanStmt = $pdo->prepare("SELECT id FROM diet_plans WHERE customer_id = ?");
        $existingPlanStmt->execute([$customer_id]);
        $existingPlan = $existingPlanStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingPlan) {
            $updateStmt = $pdo->prepare("UPDATE diet_plans SET plan_content = ?, updated_at = NOW() WHERE customer_id = ?");
            $updateStmt->execute([$diet_plan, $customer_id]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO diet_plans (customer_id, plan_content) VALUES (?, ?)");
            $insertStmt->execute([$customer_id, $diet_plan]);
        }

        header("Location: trainer.php?view_report_list=1&customer_id=" . $customer_id . "&diet_updated=1");
        exit();
    }

    $planStmt = $pdo->prepare("SELECT plan_content FROM diet_plans WHERE customer_id = ?");
    $planStmt->execute([$customer_id]);
    $planResult = $planStmt->fetch(PDO::FETCH_ASSOC);
    $currentDietPlan = $planResult ? $planResult['plan_content'] : '';

    $suggestedDiet = analyzeProgressForDiet($pdo, $customer_id);

    $userStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $userStmt->execute([$customer_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $customerName = $user['name'];

    ?>
    <div class='dashboard-container'>
        <h2>🍎 Manage Diet Plan for <?php echo htmlspecialchars($customerName); ?></h2>
        <?php if (isset($_GET['diet_updated'])): ?>
            <p style='color: green;'>Diet plan updated successfully!</p>
        <?php endif; ?>
        <form method='post'>
            <label for='diet_plan_content' style='color:#fff;display:block;margin-bottom:10px;'>Diet Plan:</label>
            <div style="background:#222; border-radius:5px; padding:10px; margin-bottom:20px; color:#eee; white-space: pre-wrap;">
                <?php echo $suggestedDiet; ?>
            </div>
            <label for='trainer_notes' style='color:#fff;display:block;margin-bottom:10px;'>Trainer's Notes (Optional):</label>
            <textarea id='trainer_notes' name='diet_plan_content' rows='5' style='width:100%;padding:10px;margin-bottom:20px;border-radius:5px;border:1px solid #555;background-color:#333;color:#eee;box-sizing:border-box;' placeholder="Add any specific instructions or modifications here."></textarea>
            <button type='submit' class='view-btn' style='background:#ffa726;color:#222;'>Save Diet Plan</button>
        </form>
        <p><a href='trainer.php?view_report_list=1&customer_id=<?php echo $customer_id; ?>' style='color:#4dd0e1;'>Back to Report</a></p>
    </div>
    <?php

} else {
    echo "<div class='dashboard-container'><p style='color:red;'>Error: Customer ID not provided.</p></div>";
}

require_once("../includes/footer.php");
?>

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
    }
    h2 {
        text-align: center;
        color: #ffa726;
        margin-bottom: 20px;
    }
    label {
        font-weight: bold;
    }
    textarea {
        display: block;
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #555;
        border-radius: 5px;
        background-color: #333;
        color: #eee;
        box-sizing: border-box;
        white-space: pre-wrap;
    }
    .view-btn {
        background: #ffa726;
        color: #222;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    .view-btn:hover {
        background-color: #ffc107;
    }
    p a {
        color: #4dd0e1;
        text-decoration: none;
    }
    p a:hover {
        text-decoration: underline;
    }

    .suggestion-container {
        color: #eee;
        font-size: 1.1em;
        line-height: 1.8;
    }

    .suggestion-line {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease-out forwards var(--delay);
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        color: #eee;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .emphasis {
        font-weight: bold;
        color: #ffca28;
    }

    .advice {
        color: #8fbc8f;
    }

    i {
        margin-right: 15px;
        font-size: 1.5em;
        color: #fdd835;
    }

    /* Crazy Animations */
    .crazy-alert { animation: shake 0.8s infinite; }
    .crazy-bulb { animation: pulseRotate 2s infinite alternate; }
    .crazy-arrow-up { animation: bounceUp 1s infinite alternate; color: #f44336; }
    .crazy-arrow-down { animation: bounceDown 1s infinite alternate; color: #4caf50; }
    .crazy-balance { animation: swing 1.5s infinite alternate; color: #2196f3; }
    .crazy-tint { animation: wobble 1.5s infinite alternate; color: #2196f3; }
    .crazy-check-double { animation: tada 1s infinite; color: #4caf50; }
    .crazy-equals { animation: rubberBand 1s infinite; color: #9e9e9e; }
    .crazy-question { animation: jello 1s infinite; color: #ffc107; }
    .crazy-water { animation: bubble 1.2s infinite alternate; color: #00bcd4; }
    .crazy-utensils { animation: spinAround 2.5s linear infinite; color: #ff9800; }
    .crazy-yin-yang { animation: rotateScale 2s linear infinite alternate; color: #fff; }
    .crazy-carrot { animation: wiggle 1s infinite alternate; color: #ff7043; }
    .crazy-pizza { animation: rotateOutDownLeft 1.5s infinite alternate; color: #ffea00; }
    .crazy-heartbeat { animation: heartbeat 1.3s infinite; color: #e91e63; }
    .crazy-stethoscope { animation: slideInRight 1s ease-out infinite alternate; color: #4db6ac; }

    @keyframes pulseRotate {
        0% { transform: scale(1) rotate(0deg); opacity: 0.8; }
        100% { transform: scale(1.2) rotate(360deg); opacity: 1; }
    }

    @keyframes bounceUp {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-15px); }
        60% { transform: translateY(-8px); }
    }

    @keyframes bounceDown {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(15px); }
        60% { transform: translateY(8px); }
    }

    @keyframes wobble {
        0%, 100% { transform: translateX(0); }
        15% { transform: translateX(-10px) rotate(-5deg); }
        30% { transform: translateX(8px) rotate(3deg); }
        45% { transform: translateX(-6px) rotate(-3deg); }
        60% { transform: translateX(4px) rotate(2deg); }
        75% { transform: translateX(-2px) rotate(-1deg); }
    }

    @keyframes spinAround {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes scaleIn {
        0% { transform: scale(0.8); opacity: 0.7; }
        100% { transform: scale(1.1); opacity: 1; }
    }

    @keyframes rotateShake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }

    @keyframes slideLeftRight {
        0%, 100% { transform: translateX(0); }
        50% { transform: translateX(10px); }
    }

    @keyframes tiltShake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(5deg); }
        75% { transform: rotate(-5deg); }
    }

    @keyframes jump {
        0%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    @keyframes rubberBand {
        from { transform: scaleX(1); }
        30% { transform: scale3d(1.25, 0.75, 1, 1, 1, 1); }
        40% { transform: scale3d(0.75, 1.25,