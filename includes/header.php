<?php
session_start();
require_once("functions.php");  // ✅ Required to use isCustomer(), isTrainer(), etc.

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}
?>
