<?php
require_once("../includes/header.php");
if (!isCustomer()) exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Slot</title>
    <style>
        body { background: #f2f2f2; font-family: Arial; padding: 40px; }
        .form-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        h2 { text-align: center; }

        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }

        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Cancel a Workout Slot</h2>
    <form method="POST">
        <label>Enter Slot Time to Cancel:</label>
        <input type="text" name="slot_time" required>
        <button type="submit">Cancel Slot</button>
    </form>
</div>
</body>
</html>
