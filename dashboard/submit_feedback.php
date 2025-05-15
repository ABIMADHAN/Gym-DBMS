<?php
require_once("../config/db.php");

$customer_id = intval($_GET['customer_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = trim($_POST['feedback']);
    $rating = intval($_POST['rating']);

    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (customer_id, feedback, rating, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$customer_id, $feedback, $rating]);
        $success_message = "Thank you for your feedback! 😊";
    } catch (PDOException $e) {
        $error_message = "Error submitting feedback: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fbc2eb, #a18cd1);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            color: #333;
            overflow-x: hidden;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            text-align: center;
            color: #ff5722;
            margin-bottom: 20px;
            font-size: 2.5rem;
            animation: bounce 1.5s infinite;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        textarea, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        textarea:focus, select:focus, button:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(255, 87, 34, 0.5);
        }

        button {
            background: linear-gradient(135deg, #ff5722, #ff9800);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #ff9800, #ff5722);
            transform: scale(1.1);
        }

        .message {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .success {
            color: #4caf50;
            animation: fadeIn 1s ease-in-out;
        }

        .error {
            color: #f44336;
            animation: shake 0.5s ease-in-out;
        }

        .emoji {
            font-size: 2rem;
            margin-right: 10px;
        }

        /* Background Animation */
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px);
            }
            75% {
                transform: translateX(5px);
            }
        }
    </style>
    <script>
        function validateForm() {
            const feedback = document.getElementById('feedback').value.trim();
            const rating = document.getElementById('rating').value;

            if (!feedback) {
                alert('Please provide your feedback. 😊');
                return false;
            }

            if (!rating) {
                alert('Please select a rating. ⭐');
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>📝 Submit Feedback</h1>
        <?php if (isset($success_message)): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="" method="POST" onsubmit="return validateForm()">
            <label for="feedback">Your Feedback:</label>
            <textarea name="feedback" id="feedback" rows="5" placeholder="Write your feedback here... 😊" required></textarea>

            <label for="rating">Rating:</label>
            <select name="rating" id="rating" required>
                <option value="">Select a rating</option>
                <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                <option value="4">⭐⭐⭐⭐ - Good</option>
                <option value="3">⭐⭐⭐ - Average</option>
                <option value="2">⭐⭐ - Poor</option>
                <option value="1">⭐ - Very Poor</option>
            </select>

            <button type="submit">✅ Submit Feedback</button>
        </form>
    </div>
</body>
</html>