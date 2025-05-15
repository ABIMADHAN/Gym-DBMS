<?php
// filepath: c:\Xampp\htdocs\GYYM\dashboard\customer.php
$host = "localhost";
$user = "root";          // Default XAMPP username
$password = "";          // Default XAMPP password is empty
$dbname = "gym_management_system";  // Replace with your database name

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Now $pdo is available for use
$stmt = $pdo->prepare("SELECT * FROM customers"); // Example query
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
