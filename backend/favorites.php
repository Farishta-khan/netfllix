<?php
include_once "db.php";
include_once "jwt.php";
header("Content-Type: application/json");

// Read Authorization header
$headers = getallheaders();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
if (!$auth || strpos($auth, 'Bearer ') !== 0) {
    echo json_encode(["status" => "error", "message" => "Missing Authorization header"]);
    http_response_code(401);
    exit;
}
$token = trim(substr($auth, 7));
$payload = jwt_decode($token);
if (!$payload || !isset($payload['id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
    http_response_code(401);
    exit;
}
$user_id = intval($payload['id']);

$method = $_SERVER['REQUEST_METHOD'];

// Helper: get all movies from get_movies.php (decoding its JSON)
function get_all_movies_from_api() {
    // include the get_movies.php and capture its output
    $path = __DIR__ . '/get_movies.php';
    if (!file_exists($path)) return [];
    ob_start();
    include $path;
    $json = ob_get_clean();
    $data = json_decode($json, true);
    return $data['data'] ?? [];
}

if ($method === 'GET') {
    // return list of favorite movies for the user
    $stmt = $conn->prepare("SELECT movie_id FROM user_favorites WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $ids = [];
    while ($row = $res->fetch_assoc()) $ids[] = intval($row['movie_id']);

    $movies = get_all_movies_from_api();
    $filtered = array_values(array_filter($movies, function($m) use ($ids) {
        return in_array($m['id'], $ids);
    }));

    echo json_encode(["status" => "success", "data" => $filtered]);
    exit;
}

if ($method === 'POST') {
    // expected form-data or JSON with movie_id
    $movie_id = $_POST['movie_id'] ?? null;
    if (!$movie_id) {
        // try JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $movie_id = $input['movie_id'] ?? null;
    }
    if (!$movie_id) {
        echo json_encode(["status" => "error", "message" => "Missing movie_id"]);
        http_response_code(400);
        exit;
    }
    $movie_id = intval($movie_id);

    // insert ignoring duplicates
    $stmt = $conn->prepare("INSERT INTO user_favorites (user_id, movie_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE movie_id = movie_id");
    $stmt->bind_param('ii', $user_id, $movie_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
        http_response_code(500);
    }
    exit;
}

if ($method === 'DELETE') {
    // accept JSON body with movie_id
    $input = json_decode(file_get_contents('php://input'), true);
    $movie_id = $input['movie_id'] ?? null;
    if (!$movie_id) {
        echo json_encode(["status" => "error", "message" => "Missing movie_id"]);
        http_response_code(400);
        exit;
    }
    $movie_id = intval($movie_id);

    $stmt = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param('ii', $user_id, $movie_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
        http_response_code(500);
    }
    exit;
}

// unsupported
http_response_code(405);
echo json_encode(["status" => "error", "message" => "Method not allowed"]);
?>