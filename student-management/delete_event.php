
<?php
require "admin_check.php";
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: events.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: events.php?message=" .
           urlencode("Invalid event ID."));
    exit;
}

$stmt = $conn->prepare(
    "SELECT poster FROM admin_events WHERE id=?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $conn->close();
    header("Location: events.php?message=" .
           urlencode("Event not found."));
    exit;
}

$stmt = $conn->prepare("DELETE FROM admin_events WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    if ($event["poster"]) {
        $path = __DIR__ . "/uploads/" . basename($event["poster"]);

        if (is_file($path)) {
            unlink($path);
        }
    }

    $message = "Event deleted successfully!";
} else {
    $message = "Failed to delete event.";
}

$stmt->close();
$conn->close();

header("Location: events.php?message=" . urlencode($message));
exit;
?>
