
<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $conn->close();
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $conn->close();
    header("Location: index.php?message=" .
           urlencode("Invalid student ID."));
    exit;
}

$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $message = "Student deleted successfully!";
    } else {
        $message = "Student record not found.";
    }
} else {
    $message = "Failed to delete student. Please try again.";
}

$stmt->close();
$conn->close();

header("Location: index.php?message=" . urlencode($message));
exit;
?>
