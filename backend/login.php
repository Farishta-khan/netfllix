<?php
include "db.php";
include "jwt.php";

header("Content-Type: application/json");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "not_found"]);
    exit;
}

$user = $result->fetch_assoc();

// verify hashed password
if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Wrong password"]);
    exit;
}

// prepare response: remove password & attach token
$uid = $user['id'];
unset($user['password']);
$token = jwt_encode(["id" => $uid, "email" => $user['email']], 604800); // 7 days

echo json_encode([
    "status" => "success",
    "user" => $user,
    "token" => $token
]);
?>