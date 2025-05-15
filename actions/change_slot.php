<?php
require_once("../config/db.php");
session_start();

$slot_id = $_POST['slot_id'];
$new_time = $_POST['new_time'];

$stmt = $conn->prepare("UPDATE slots SET time_slot=? WHERE id=?");
$stmt->bind_param("si", $new_time, $slot_id);
$stmt->execute();

header("Location: ../dashboard/customer.php");
