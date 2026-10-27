
<?php
require "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $conn->close();
    header("Location: index.php?message=" .
           urlencode("Invalid student ID."));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    $conn->close();
    header("Location: index.php?message=" .
           urlencode("Student record not found."));
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $semester = filter_var(
        $_POST["semester"] ?? "",
        FILTER_VALIDATE_INT
    );

    $student["name"] = $name;
    $student["email"] = $email;
    $student["phone"] = $phone;
    $student["course"] = $course;
    $student["semester"] = $_POST["semester"] ?? "";

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
            "UPDATE students
             SET name=?, email=?, phone=?, course=?, semester=?
             WHERE id=?"
        );

        $stmt->bind_param(
            "ssssii", $name, $email, $phone,
            $course, $semester, $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: index.php?message=" .
                   urlencode("Student updated successfully!"));
            exit;
        } else {
            $error = $stmt->errno === 1062
                ? "This email address belongs to another student."
                : "Failed to update student. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 30px; }
        form { background: white; padding: 20px; max-width: 450px; }
        input, button { display: block; width: 95%; padding: 10px; margin: 10px 0; }
        button { background: #2457a7; color: white; border: 0; cursor: pointer; }
        .error { color: #c62828; }
    </style>
</head>
<body>
<h1>Edit Student</h1>

<?php if ($error !== ""): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Full Name</label>
    <input type="text" name="name" maxlength="100" required
           value="<?= htmlspecialchars($student["name"]) ?>">

    <label>Email</label>
    <input type="email" name="email" maxlength="100" required
           value="<?= htmlspecialchars($student["email"]) ?>">

    <label>Phone</label>
    <input type="tel" name="phone" maxlength="15" required
           value="<?= htmlspecialchars($student["phone"]) ?>">

    <label>Course</label>
    <input type="text" name="course" maxlength="100" required
           value="<?= htmlspecialchars($student["course"]) ?>">

    <label>Semester (1-12)</label>
    <input type="number" name="semester" min="1" max="12" required
           value="<?= htmlspecialchars((string)$student["semester"]) ?>">

    <button type="submit">Update Student</button>
</form>

<p><a href="index.php">Back to Student List</a></p>
</body>
</html>
