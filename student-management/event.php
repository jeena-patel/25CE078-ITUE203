
<?php
require "admin_check.php";
require "db.php";

$message = $_GET["message"] ?? "";

$result = $conn->query(
    "SELECT * FROM admin_events ORDER BY event_date DESC"
);

if (!$result) {
    die("Failed to load events.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Event Management</title>
    <style>
        body { font-family: Arial; margin: 30px; background: #f4f6f9; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #243b64; color: white; }
        img { width: 100px; height: 70px; object-fit: cover; }
        a, button { padding: 8px; margin: 3px; }
        .message { color: green; }
    </style>
</head>
<body>

<h1>Admin Event Management</h1>

<?php if ($message !== ""): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<p><a href="add_event.php">+ Add New Event</a></p>

<table>
<tr>
    <th>ID</th>
    <th>Poster</th>
    <th>Title</th>
    <th>Description</th>
    <th>Date</th>
    <th>Actions</th>
</tr>

<?php if ($result->num_rows > 0): ?>
    <?php while ($event = $result->fetch_assoc()): ?>
    <tr>
        <td><?= (int)$event["id"] ?></td>
        <td>
            <?php if ($event["poster"]): ?>
                <img src="uploads/<?= rawurlencode($event["poster"]) ?>"
                     alt="Event poster">
            <?php else: ?>
                No poster
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($event["title"]) ?></td>
        <td><?= nl2br(htmlspecialchars($event["description"])) ?></td>
        <td><?= htmlspecialchars($event["event_date"]) ?></td>
        <td>
            <a href="edit_event.php?id=<?= (int)$event["id"] ?>">
                Edit
            </a>

            <form action="delete_event.php" method="POST"
                  style="display:inline"
                  onsubmit="return confirm('Delete this event?')">
                <input type="hidden" name="id"
                       value="<?= (int)$event["id"] ?>">
                <button type="submit">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="6">No events available.</td></tr>
<?php endif; ?>
</table>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
