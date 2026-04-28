<?php
include "db.php";

header("Content-Type: application/json");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "not_found"]);
    exit;
}

$user = $result->fetch_assoc();

if ($password !== $user['password']) {
    echo json_encode(["status" => "error", "message" => "Wrong password"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "user" => $user
]);
?>