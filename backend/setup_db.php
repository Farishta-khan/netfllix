<?php
// Use config if available
$dbHost = 'localhost'; $dbUser = 'root'; $dbPass = ''; $dbName = 'netflixx';
if (file_exists(__DIR__ . '/config.php')) {
    include_once __DIR__ . '/config.php';
    if (defined('DB_HOST')) $dbHost = DB_HOST;
    if (defined('DB_USER')) $dbUser = DB_USER;
    if (defined('DB_PASS')) $dbPass = DB_PASS;
    if (defined('DB_NAME')) $dbName = DB_NAME;
}

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

$sql = "
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    image_url TEXT,
    video_url TEXT,
    trailer_url TEXT,
    genre VARCHAR(50),
    release_year INT,
    rating FLOAT,
    duration VARCHAR(50),
    cast TEXT,
    featured TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    UNIQUE KEY user_movie_unique (user_id, movie_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
";

if ($conn->multi_query($sql)) {
    // wait for multi_query to finish
    do { $conn->store_result(); } while ($conn->more_results() && $conn->next_result());
    echo "Tables created successfully!\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

// Seed sample movies only if movies table empty
$countRes = $conn->query("SELECT COUNT(*) AS c FROM movies");
$needSeed = true;
if ($countRes) {
    $row = $countRes->fetch_assoc();
    if ($row && intval($row['c']) > 0) $needSeed = false;
    $countRes->free();
}

if ($needSeed) {
    $movies = [
        ["title"=>"Stranger Things","description"=>"A group of kids uncover supernatural mysteries in their small town.","image_url"=>"https://image.tmdb.org/t/p/w500/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg","video_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4","trailer_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4","genre"=>"Drama","release_year"=>2016,"rating"=>8.7,"duration"=>"4 Seasons","cast"=>""],
        ["title"=>"Money Heist","description"=>"A mastermind leads a group to execute the biggest heist in history.","image_url"=>"https://image.tmdb.org/t/p/w500/reEMJA1uzscCbkpeRJeTT2bjqUp.jpg","video_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4","trailer_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4","genre"=>"Crime","release_year"=>2017,"rating"=>8.2,"duration"=>"5 Seasons","cast"=>""],
        ["title"=>"Squid Game","description"=>"Players risk their lives in deadly games for a massive cash prize.","image_url"=>"https://image.tmdb.org/t/p/w500/dDlEmu3EZ0Pgg93K2SVNLCjCSvE.jpg","video_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4","trailer_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4","genre"=>"Drama","release_year"=>2021,"rating"=>8.0,"duration"=>"1 Season","cast"=>""],
        ["title"=>"Inception","description"=>"A thief enters dreams to steal secrets and plant ideas.","image_url"=>"https://image.tmdb.org/t/p/w500/edv5CZvWj09upOsy2Y6IwDhK8bt.jpg","video_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4","trailer_url"=>"https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4","genre"=>"Sci-Fi","release_year"=>2010,"rating"=>8.8,"duration"=>"2h 28m","cast"=>""],
        ["title"=>"The Dark Knight","description"=>"Batman faces the Joker in a battle for Gotham's soul.","image_url"=>"https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg","genre"=>"Action","release_year"=>2008,"rating"=>9.0,"duration"=>"2h 32m","cast"=>""],
        ["title"=>"Interstellar","description"=>"A team travels through space to save humanity.","image_url"=>"https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg","genre"=>"Sci-Fi","release_year"=>2014,"rating"=>8.6,"duration"=>"2h 49m","cast"=>""],
    ];

    $stmt = $conn->prepare("INSERT INTO movies (title, description, image_url, video_url, trailer_url, genre, release_year, rating, duration, cast, featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($movies as $idx => $m) {
        $featured = ($idx === 0) ? 1 : 0; // mark first seeded item as featured
        $stmt->bind_param('ssssssidssi', $m['title'], $m['description'], $m['image_url'], $m['video_url'] ?? null, $m['trailer_url'] ?? null, $m['genre'] ?? null, $m['release_year'] ?? null, $m['rating'] ?? 0, $m['duration'] ?? null, $m['cast'] ?? null, $featured);
        $stmt->execute();
    }
    $stmt->close();
    echo "Seeded sample movies.\n";
}

$conn->close();
