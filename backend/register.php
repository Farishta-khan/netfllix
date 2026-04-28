<?php
include "db.php";

header("Content-Type: application/json");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

// check user exists
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "User already exists"]);
    exit;
}

// insert
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $password);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>