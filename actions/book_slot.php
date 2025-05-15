<?php
require_once("../config/db.php");
session_start();

$slot_time = $_POST['slot_time'];
$customer_id = $_SESSION['user_id'];

// Assign a random trainer for simplicity
$trainer_query = $conn->query("SELECT id FROM users WHERE role='trainer' LIMIT 1");
$trainer = $trainer_query->fetch_assoc();

$stmt = $conn->prepare("INSERT INTO slots (time_slot, status, customer_id, trainer_id) VALUES (?, 'booked', ?, ?)");
$stmt->bind_param("sii", $slot_time, $customer_id, $trainer['id']);
$stmt->execute();

header("Location: ../dashboard/customer.php");
