
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TEMPORARY: Set admin role for local testing only.
$_SESSION["role"] = "admin";

if (
    !isset($_SESSION["role"]) ||
    strtolower($_SESSION["role"]) !== "admin"
) {
    http_response_code(403);
    exit("Access denied. Only administrators can manage events.");
}
?>
