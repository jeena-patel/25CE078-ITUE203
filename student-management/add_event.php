
<?php
require "admin_check.php";
require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $posterName = null;
    $uploadedPath = null;

    if ($title === "" || strlen($title) > 150 ||
        $description === "") {
        $error = "Enter a valid event title and description.";
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date) ||
        !checkdate(
            (int)substr($event_date, 5, 2),
            (int)substr($event_date, 8, 2),
            (int)substr($event_date, 0, 4)
        )
    ) {
        $error = "Please select a valid event date.";
    } else {
        $file = $_FILES["poster"] ?? null;

        if (!$file || $file["error"] !== UPLOAD_ERR_OK) {
            $error = "Please select a poster image.";
        } elseif ($file["size"] > 2 * 1024 * 1024) {
            $error = "Poster size must not exceed 2 MB.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file["tmp_name"]);

            $allowed = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            if (!isset($allowed[$mime])) {
                $error = "Only JPG, PNG, and WebP posters are allowed.";
            } elseif (getimagesize($file["tmp_name"]) === false) {
                $error = "The uploaded file is not a valid image.";
            } else {
                $folder = __DIR__ . "/uploads";

                if (!is_dir($folder) &&
                    !mkdir($folder, 0755, true)) {
                    $error = "Could not create the uploads folder.";
                } elseif (!is_writable($folder)) {
                    $error = "The uploads folder is not writable.";
                } else {
                    $posterName = bin2hex(random_bytes(16))
                        . "." . $allowed[$mime];

                    $uploadedPath = $folder . "/" . $posterName;

                    if (!move_uploaded_file(
                        $file["tmp_name"], $uploadedPath
                    )) {
                        $error = "Poster upload failed.";
                    }
                }
            }
        }
    }

    if ($error === "") {
        $stmt = $conn->prepare(
            "INSERT INTO admin_events
             (title, description, event_date, poster)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss", $title, $description, $event_date, $posterName
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: events.php?message=" .
                   urlencode("Event added successfully!"));
            exit;
        }

        if ($uploadedPath && is_file($uploadedPath)) {
            unlink($uploadedPath);
        }

        $error = "Failed to save the event. Please try again.";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
</head>
<body>
<h1>Add New Event</h1>

<?php if ($error !== ""): ?>
    <p style="color:red">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Event Title</label><br>
    <input type="text" name="title" maxlength="150" required><br><br>

    <label>Description</label><br>
    <textarea name="description" required></textarea><br><br>

    <label>Event Date</label><br>
    <input type="date" name="event_date" required><br><br>

    <label>Event Poster (JPG, PNG, WebP; maximum 2 MB)</label><br>
    <input type="file" name="poster"
           accept=".jpg,.jpeg,.png,.webp" required><br><br>

    <button type="submit">Add Event</button>
</form>

<p><a href="events.php">Back to Events</a></p>
</body>
</html>
