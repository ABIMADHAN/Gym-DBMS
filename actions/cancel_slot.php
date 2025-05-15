<?php
require_once("../config/db.php");
session_start();
$slot_id = $_POST['slot_id'];
$customer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE slots SET status='cancelled' WHERE id=? AND customer_id=?");
$stmt->bind_param("ii", $slot_id, $customer_id);
$stmt->execute();

header("Location: ../dashboard/customer.php");
