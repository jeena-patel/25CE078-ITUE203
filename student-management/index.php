
<?php
require "db.php";

$message = $_GET["message"] ?? "";
$search = trim($_GET["search"] ?? "");
$course = $_GET["course"] ?? "";

$courses = [];
$courseResult = $conn->query(
    "SELECT DISTINCT course FROM students ORDER BY course"
);

if ($courseResult) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row["course"];
    }
}

$sql = "SELECT * FROM students WHERE
        (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
$params = ["%$search%", "%$search%", "%$search%"];
$types = "sss";

if ($course !== "") {
    $sql .= " AND course = ?";
    $params[] = $course;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f4f6f9;
        }
        h1 { color: #243b64; }
        .message {
            padding: 12px;
            background: #dff5e3;
            color: #17652b;
            margin: 15px 0;
            border-radius: 5px;
        }
        .search-box, .table-box {
            background: white;
            padding: 18px;
            border-radius: 8px;
            margin-top: 18px;
        }
        input, select, button, .btn {
            padding: 9px;
            margin: 4px;
        }
        button, .btn {
            border: 0;
            border-radius: 4px;
            background: #2457a7;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .delete { background: #c62828; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th { background: #243b64; color: white; }
        .actions { white-space: nowrap; }
    </style>
</head>
<body>

<h1>Student Management Module</h1>

<a class="btn" href="add.php">+ Add Student</a>

<?php if ($message !== ""): ?>
    <div class="message">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="search-box">
    <form method="GET" action="index.php">
        <input type="text" name="search"
               placeholder="Search name, email or phone"
               value="<?= htmlspecialchars($search) ?>">

        <select name="course">
            <option value="">All Courses</option>
            <?php foreach ($courses as $item): ?>
                <option value="<?= htmlspecialchars($item) ?>"
                    <?= $course === $item ? "selected" : "" ?>>
                    <?= htmlspecialchars($item) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search / Filter</button>
        <a class="btn" href="index.php">Reset</a>
    </form>
</div>

<div class="table-box">
    <h2>Student Records</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Actions</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($student = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $student["id"] ?></td>
                    <td><?= htmlspecialchars($student["name"]) ?></td>
                    <td><?= htmlspecialchars($student["email"]) ?></td>
                    <td><?= htmlspecialchars($student["phone"]) ?></td>
                    <td><?= htmlspecialchars($student["course"]) ?></td>
                    <td><?= $student["semester"] ?></td>
                    <td class="actions">
                        <a class="btn"
                           href="edit.php?id=<?= $student["id"] ?>">
                            Edit
                        </a>

                        <form method="POST" action="delete.php"
                              style="display:inline"
                              onsubmit="return confirm('Delete this student?');">
                            <input type="hidden" name="id"
                                   value="<?= $student["id"] ?>">
                            <button class="delete" type="submit">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No student records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
