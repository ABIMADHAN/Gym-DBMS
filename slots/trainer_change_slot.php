<?php
require_once("../includes/header.php");
require_once("../config/db.php");

if (!isTrainer()) {
    die("Unauthorized access.");
}

$customers = $conn->query("SELECT id, name FROM users WHERE role = 'customer'");
$selected_customer = $_POST['customer_id'] ?? null;

// Handle slot change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_slot_id'], $_POST['customer_id'])) {
    $new_slot_id = intval($_POST['new_slot_id']);
    $cust_id = intval($_POST['customer_id']);

    // Book new
    $stmt = $conn->prepare("UPDATE slots SET user_id = ?, status = 'booked' WHERE id = ?");
    $stmt->bind_param("ii", $cust_id, $new_slot_id);
    $stmt->execute();

    // Free old
    $stmt = $conn->prepare("UPDATE slots SET user_id = NULL, status = 'available' WHERE user_id = ? AND id != ?");
    $stmt->bind_param("ii", $cust_id, $new_slot_id);
    $stmt->execute();

    header("Location: ../dashboard/trainer.php");
    exit;
}

$available = $conn->query("SELECT * FROM slots WHERE status = 'available'");
?>

<h2>Change Slot for Customer</h2>
<form method="POST">
    <label>Select Customer:</label>
    <select name="customer_id" onchange="this.form.submit()" required>
        <option value="">--Choose--</option>
        <?php while ($c = $customers->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= ($selected_customer == $c['id']) ? 'selected' : '' ?>>
                <?= $c['name'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php if ($selected_customer): ?>
<form method="POST">
    <input type="hidden" name="customer_id" value="<?= $selected_customer ?>">
    <label>Select New Slot:</label>
    <select name="new_slot_id" required>
        <?php while ($row = $available->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['time'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Change</button>
</form>
<?php endif; ?>
