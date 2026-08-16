<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Simple movies CRUD API.
// - GET /movies.php         -> list all movies
// - GET /movies.php?id=5    -> get one movie
// - POST /movies.php        -> create (admin token required)
// - PUT /movies.php?id=5    -> update (admin token required)
// - DELETE /movies.php?id=5 -> delete (admin token required)

$method = $_SERVER['REQUEST_METHOD'];

// Load config if present
$dbHost = 'localhost'; $dbUser = 'root'; $dbPass = ''; $dbName = 'netflixx';
$adminToken = null;
if (file_exists(__DIR__ . '/config.php')) {
    include_once __DIR__ . '/config.php';
    if (defined('DB_HOST')) $dbHost = DB_HOST;
    if (defined('DB_USER')) $dbUser = DB_USER;
    if (defined('DB_PASS')) $dbPass = DB_PASS;
    if (defined('DB_NAME')) $dbName = DB_NAME;
    if (defined('ADMIN_TOKEN')) $adminToken = ADMIN_TOKEN;
}

// Simple admin check using X-Admin-Token header
function isAdmin($provided, $configured) {
    if (!$configured) return false; // no admin configured
    if (!$provided) return false;
    return hash_equals($configured, $provided);
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

// Helper to read input
$input = null;
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
}

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $mysqli->prepare("SELECT id, title, description, image_url, video_url, trailer_url, genre, release_year, rating, duration, cast, IFNULL(featured,0) AS featured FROM movies WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $movie = $res->fetch_assoc();
            $stmt->close();
            if ($movie) echo json_encode(["status"=>"success","data"=>$movie]);
            else echo json_encode(["status"=>"error","message"=>"Not found"]);
            exit;
        }

        $res = $mysqli->query("SELECT id, title, description, image_url, video_url, trailer_url, genre, release_year, rating, duration, cast, IFNULL(featured,0) AS featured FROM movies ORDER BY id ASC");
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
        echo json_encode(["status"=>"success","data"=>$out]);
        exit;
    }

    // Admin operations
    $provided = null;
    foreach (getallheaders() as $k => $v) { if (strtolower($k) === 'x-admin-token') $provided = $v; }
    if (!isAdmin($provided, $adminToken)) {
        http_response_code(403);
        echo json_encode(["status"=>"error","message"=>"Admin token required"]);
        exit;
    }

    if ($method === 'POST') {
        // create
        $title = $mysqli->real_escape_string($input['title'] ?? 'Untitled');
        $description = $mysqli->real_escape_string($input['description'] ?? '');
        $image_url = $mysqli->real_escape_string($input['image_url'] ?? '');
        $video_url = $mysqli->real_escape_string($input['video_url'] ?? '');
        $trailer_url = $mysqli->real_escape_string($input['trailer_url'] ?? '');
        $genre = $mysqli->real_escape_string($input['genre'] ?? '');
        $release_year = intval($input['release_year'] ?? 0) ?: null;
        $rating = floatval($input['rating'] ?? 0);
        $duration = $mysqli->real_escape_string($input['duration'] ?? '');
        $cast = $mysqli->real_escape_string($input['cast'] ?? '');
        $featured = intval($input['featured'] ?? 0);

        $stmt = $mysqli->prepare("INSERT INTO movies (title, description, image_url, video_url, trailer_url, genre, release_year, rating, duration, cast, featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssidssi', $title, $description, $image_url, $video_url, $trailer_url, $genre, $release_year, $rating, $duration, $cast, $featured);
        if ($stmt->execute()) {
            echo json_encode(["status"=>"success","id"=>$stmt->insert_id]);
        } else {
            echo json_encode(["status"=>"error","message"=>$stmt->error]);
        }
        $stmt->close();
        exit;
    }

    if ($method === 'PUT') {
        if (!isset($_GET['id'])) { echo json_encode(["status"=>"error","message"=>"id required"]); exit; }
        $id = intval($_GET['id']);
        $title = $mysqli->real_escape_string($input['title'] ?? null);
        $description = $mysqli->real_escape_string($input['description'] ?? null);
        $image_url = $mysqli->real_escape_string($input['image_url'] ?? null);
        $video_url = $mysqli->real_escape_string($input['video_url'] ?? null);
        $trailer_url = $mysqli->real_escape_string($input['trailer_url'] ?? null);
        $genre = $mysqli->real_escape_string($input['genre'] ?? null);
        $release_year = intval($input['release_year'] ?? 0) ?: null;
        $rating = floatval($input['rating'] ?? 0);
        $duration = $mysqli->real_escape_string($input['duration'] ?? null);
        $cast = $mysqli->real_escape_string($input['cast'] ?? null);
        $featured = intval($input['featured'] ?? 0);

        $stmt = $mysqli->prepare("UPDATE movies SET title=?, description=?, image_url=?, video_url=?, trailer_url=?, genre=?, release_year=?, rating=?, duration=?, cast=?, featured=? WHERE id=?");
                $stmt->bind_param('ssssssidssii', $title, $description, $image_url, $video_url, $trailer_url, $genre, $release_year, $rating, $duration, $cast, $featured, $id);
        if ($stmt->execute()) echo json_encode(["status"=>"success"]);
        else echo json_encode(["status"=>"error","message"=>$stmt->error]);
        $stmt->close();
        exit;
    }

    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) { echo json_encode(["status"=>"error","message"=>"id required"]); exit; }
        $id = intval($_GET['id']);
        $stmt = $mysqli->prepare("DELETE FROM movies WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) echo json_encode(["status"=>"success"]);
        else echo json_encode(["status"=>"error","message"=>$stmt->error]);
        $stmt->close();
        exit;
    }

    echo json_encode(["status"=>"error","message"=>"Unsupported method"]);
} catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}

$mysqli->close();

?>