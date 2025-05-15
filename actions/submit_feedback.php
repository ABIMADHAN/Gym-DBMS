<?php
require_once("../config/db.php");
session_start();
$customer_id = $_SESSION['user_id'];
$message = $_POST['message'];

$trainer_result = $conn->query("SELECT id FROM users WHERE role='trainer' LIMIT 1");
$trainer = $trainer_result->fetch_assoc();

$stmt = $conn->prepare("INSERT INTO feedback (customer_id, trainer_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $customer_id, $trainer['id'], $message);
$stmt->execute();

header("Location: ../dashboard/customer.php");
