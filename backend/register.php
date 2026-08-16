<?php
include "db.php";

header("Content-Type: application/json");

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email address"]);
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

// insert (store hashed password)
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hash);

if ($stmt->execute()) {
    // generate token and return user
    $newId = $conn->insert_id;
    include_once "jwt.php";
    $token = jwt_encode(["id" => $newId, "email" => $email], 604800);
    echo json_encode(["status" => "success", "user" => ["id" => $newId, "email" => $email], "token" => $token]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>