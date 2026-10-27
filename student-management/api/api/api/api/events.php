<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../db.php";

$conn->set_charset("utf8mb4");

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $result = $conn->query(
        "SELECT id, title, description, event_date
         FROM api_events ORDER BY id DESC"
    );

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    respond($events);
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    respond(["error" => "Invalid JSON data"], 400);
}

if ($method === "POST") {
    $title = trim($input["title"] ?? "");
    $description = trim($input["description"] ?? "");
    $event_date = $input["event_date"] ?? "";

    if ($title === "" || $event_date === "") {
        respond(["error" => "Title and date are required"], 400);
    }

    $stmt = $conn->prepare(
        "INSERT INTO api_events (title, description, event_date)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $title, $description, $event_date);

    if ($stmt->execute()) {
        respond(["message" => "Event added successfully",
                 "id" => $conn->insert_id], 201);
    }

    respond(["error" => "Could not add event"], 500);
}

if ($method === "PUT") {
    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);
    $title = trim($input["title"] ?? "");
    $description = trim($input["description"] ?? "");
    $event_date = $input["event_date"] ?? "";

    if (!$id || $title === "" || $event_date === "") {
        respond(["error" => "Valid ID, title and date are required"], 400);
    }

    $stmt = $conn->prepare(
        "UPDATE api_events
         SET title = ?, description = ?, event_date = ?
         WHERE id = ?"
    );
    $stmt->bind_param("sssi", $title, $description, $event_date, $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $check = $conn->prepare("SELECT id FROM api_events WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;

        if (!$exists) {
            respond(["error" => "Event not found"], 404);
        }
    }

    respond(["message" => "Event updated successfully"]);
}

if ($method === "DELETE") {
    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

    if (!$id) {
        respond(["error" => "Valid event ID is required"], 400);
    }

    $stmt = $conn->prepare("DELETE FROM api_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        respond(["error" => "Event not found"], 404);
    }

    respond(["message" => "Event deleted successfully"]);
}

header("Allow: GET, POST, PUT, DELETE");
respond(["error" => "Method not allowed"], 405);