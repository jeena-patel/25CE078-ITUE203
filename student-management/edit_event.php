
<?php
require "admin_check.php";
require "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid event ID.");
}

$stmt = $conn->prepare("SELECT * FROM admin_events WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    exit("Event not found.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $poster = $event["poster"];
    $newPath = null;

    if ($title === "" || strlen($title) > 150 ||
        $description === "") {
        $error = "Enter a valid title and description.";
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
        !checkdate(
            (int)substr($date, 5, 2),
            (int)substr($date, 8, 2),
            (int)substr($date, 0, 4)
        )
    ) {
        $error = "Please select a valid date.";
    } else {
        $file = $_FILES["poster"] ?? null;

        if ($file && $file["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($file["error"] !== UPLOAD_ERR_OK) {
                $error = "Poster upload failed.";
            } elseif ($file["size"] > 2 * 1024 * 1024) {
                $error = "Poster must not exceed 2 MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file["tmp_name"]);

                $allowed = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($allowed[$mime]) ||
                    getimagesize($file["tmp_name"]) === false) {
                    $error = "Only valid JPG, PNG, or WebP images are allowed.";
                } else {
                    $folder = __DIR__ . "/uploads";

                    if (!is_dir($folder) &&
                        !mkdir($folder, 0755, true)) {
                        $error = "Cannot create uploads folder.";
                    } elseif (!is_writable($folder)) {
                        $error = "Uploads folder is not writable.";
                    } else {
                        $poster = bin2hex(random_bytes(16))
                            . "." . $allowed[$mime];
                        $newPath = $folder . "/" . $poster;

                        if (!move_uploaded_file(
                            $file["tmp_name"], $newPath
                        )) {
                            $error = "Could not save new poster.";
                        }
                    }
                }
            }
        }
    }

    if ($error === "") {
        $stmt = $conn->prepare(
            "UPDATE admin_events
             SET title=?, description=?, event_date=?, poster=?
             WHERE id=?"
        );
        $stmt->bind_param(
            "ssssi", $title, $description, $date, $poster, $id
        );

        if ($stmt->execute()) {
            $stmt->close();

            if ($newPath && $event["poster"]) {
                $oldPath = __DIR__ . "/uploads/" . basename($event["poster"]);
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $conn->close();
            header("Location: events.php?message=" .
                   urlencode("Event updated successfully!"));
            exit;
        }

        if ($newPath && is_file($newPath)) {
            unlink($newPath);
        }
        $error = "Failed to update event.";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
</head>
<body>
<h1>Edit Event</h1>

<?php if ($error !== ""): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Event Title</label><br>
    <input type="text" name="title" maxlength="150" required
           value="<?= htmlspecialchars($event["title"]) ?>"><br><br>

    <label>Description</label><br>
    <textarea name="description" required><?= htmlspecialchars($event["description"]) ?></textarea><br><br>

    <label>Event Date</label><br>
    <input type="date" name="event_date" required
           value="<?= htmlspecialchars($event["event_date"]) ?>"><br><br>

    <?php if ($event["poster"]): ?>
        <p>Current poster:</p>
        <img width="150"
             src="uploads/<?= rawurlencode($event["poster"]) ?>"
             alt="Current poster"><br><br>
    <?php endif; ?>

    <label>Replace poster (optional; JPG, PNG, WebP; maximum 2 MB)</label><br>
    <input type="file" name="poster"
           accept=".jpg,.jpeg,.png,.webp"><br><br>

    <button type="submit">Update Event</button>
</form>

<p><a href="events.php">Back to Events</a></p>
</body>
</html>
