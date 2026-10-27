
<?php
require "db.php";

$error = "";

$name = "";
$email = "";
$phone = "";
$course = "";
$semester = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $semester = filter_var(
        $_POST["semester"] ?? "",
        FILTER_VALIDATE_INT
    );

    if (
        $name === "" || strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 100 ||
        $phone === "" || strlen($phone) > 15 ||
        $course === "" || strlen($course) > 100 ||
        $semester === false || $semester < 1 || $semester > 12
    ) {
        $error = "Please enter valid details in all fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO students
            (name, email, phone, course, semester)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssi", $name, $email, $phone, $course, $semester
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: index.php?message=" .
                   urlencode("Student added successfully!"));
            exit;
        } else {
            $error = $stmt->errno === 1062
                ? "This email address already exists."
                : "Failed to add student. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 30px; }
        form { background: white; padding: 20px; max-width: 450px; }
        input, button { display: block; width: 95%; padding: 10px; margin: 10px 0; }
        button { background: #2457a7; color: white; border: 0; cursor: pointer; }
        .error { color: #c62828; }
    </style>
</head>
<body>
<h1>Add Student</h1>

<?php if ($error !== ""): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Full Name</label>
    <input type="text" name="name" maxlength="100" required
           value="<?= htmlspecialchars($name) ?>">

    <label>Email</label>
    <input type="email" name="email" maxlength="100" required
           value="<?= htmlspecialchars($email) ?>">

    <label>Phone</label>
    <input type="tel" name="phone" maxlength="15" required
           value="<?= htmlspecialchars($phone) ?>">

    <label>Course</label>
    <input type="text" name="course" maxlength="100" required
           placeholder="e.g. BTech"
           value="<?= htmlspecialchars($course) ?>">

    <label>Semester (1-12)</label>
    <input type="number" name="semester" min="1" max="12" required
           value="<?= htmlspecialchars((string)($_POST["semester"] ?? "")) ?>">

    <button type="submit">Add Student</button>
</form>

<p><a href="index.php">Back to Student List</a></p>
</body>
</html>
