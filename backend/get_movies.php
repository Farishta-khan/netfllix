<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Prefer reading movies from DB if available (configured via backend/config.php)
$dbHost = 'localhost'; $dbUser = 'root'; $dbPass = ''; $dbName = 'netflixx';
if (file_exists(__DIR__ . '/config.php')) {
    include_once __DIR__ . '/config.php';
    if (defined('DB_HOST')) $dbHost = DB_HOST;
    if (defined('DB_USER')) $dbUser = DB_USER;
    if (defined('DB_PASS')) $dbPass = DB_PASS;
    if (defined('DB_NAME')) $dbName = DB_NAME;
}

$useDb = false;
$moviesData = [];

$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn && $conn->connect_errno === 0) {
    $useDb = true;
    $res = $conn->query("SELECT id, title, description, image_url, video_url, genre, release_year, rating, duration, cast, trailer_url FROM movies ORDER BY id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $row['rating'] = floatval($row['rating']);
            $row['release_year'] = $row['release_year'] ? intval($row['release_year']) : null;
            $moviesData[] = $row;
        }
        $res->free();
    }
    $conn->close();
}

if ($useDb && count($moviesData) > 0) {
    echo json_encode(["status" => "success", "data" => $moviesData]);
    exit;
}

// Fallback to static list if DB not available or empty
$movies = json_encode([
    "status" => "success",
    "data" => [
        ["id" => 1, "title" => "Stranger Things", "image_url" => "https://image.tmdb.org/t/p/w500/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg", "video_url" => "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4", "genre" => "Drama", "release_year" => 2016, "rating" => 8.7, "duration" => "4 Seasons", "description" => "A group of kids uncover supernatural mysteries in their small town."],
        ["id" => 2, "title" => "Money Heist", "image_url" => "https://image.tmdb.org/t/p/w500/reEMJA1uzscCbkpeRJeTT2bjqUp.jpg", "video_url" => "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4", "genre" => "Crime", "release_year" => 2017, "rating" => 8.2, "duration" => "5 Seasons", "description" => "A mastermind leads a group to execute the biggest heist in history."],
        ["id" => 3, "title" => "Squid Game", "image_url" => "https://image.tmdb.org/t/p/w500/dDlEmu3EZ0Pgg93K2SVNLCjCSvE.jpg", "video_url" => "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4", "genre" => "Drama", "release_year" => 2021, "rating" => 8.0, "duration" => "1 Season", "description" => "Players risk their lives in deadly games for a massive cash prize."],
        ["id" => 4, "title" => "Breaking Bad", "image_url" => "https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizRGt.jpg", "genre" => "Crime", "release_year" => 2008, "rating" => 9.5, "duration" => "5 Seasons", "description" => "A chemistry teacher turns into a drug kingpin."],
        ["id" => 5, "title" => "The Witcher", "image_url" => "https://image.tmdb.org/t/p/w500/7vjaCdMw15FEbXyLQTVa04URsPm.jpg", "genre" => "Action", "release_year" => 2019, "rating" => 8.2, "duration" => "3 Seasons", "description" => "A monster hunter struggles to find his place in a brutal world."],
        ["id" => 6, "title" => "Inception", "image_url" => "https://image.tmdb.org/t/p/w500/edv5CZvWj09upOsy2Y6IwDhK8bt.jpg", "video_url" => "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4", "genre" => "Sci-Fi", "release_year" => 2010, "rating" => 8.8, "duration" => "2h 28m", "description" => "A thief enters dreams to steal secrets and plant ideas."],
        ["id" => 7, "title" => "The Dark Knight", "image_url" => "https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg", "genre" => "Action", "release_year" => 2008, "rating" => 9.0, "duration" => "2h 32m", "description" => "Batman faces the Joker in a battle for Gotham's soul."],
        ["id" => 8, "title" => "Interstellar", "image_url" => "https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg", "genre" => "Sci-Fi", "release_year" => 2014, "rating" => 8.6, "duration" => "2h 49m", "description" => "A team travels through space to save humanity."],
        ["id" => 9, "title" => "Avengers: Endgame", "image_url" => "https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg", "genre" => "Action", "release_year" => 2019, "rating" => 8.4, "duration" => "3h", "description" => "The Avengers unite to undo the damage caused by Thanos."],
        ["id" => 10, "title" => "Spider-Man: No Way Home", "image_url" => "https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg", "genre" => "Action", "release_year" => 2021, "rating" => 8.2, "duration" => "2h 28m", "description" => "Spider-Man faces villains from different universes."],
        ["id" => 11, "title" => "Dune", "image_url" => "https://image.tmdb.org/t/p/w500/d5NXSklXo0qyIYkgV94XAgMIckC.jpg", "genre" => "Sci-Fi", "release_year" => 2021, "rating" => 8.0, "duration" => "2h 35m", "description" => "A noble family becomes entangled in a war over a desert planet."],
        ["id" => 12, "title" => "The Matrix", "image_url" => "https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg", "genre" => "Sci-Fi", "release_year" => 1999, "rating" => 8.7, "duration" => "2h 16m", "description" => "A hacker discovers the world is a simulation."],
        ["id" => 13, "title" => "Parasite", "image_url" => "https://image.tmdb.org/t/p/w500/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg", "genre" => "Drama", "release_year" => 2019, "rating" => 8.5, "duration" => "2h 12m", "description" => "A poor family schemes to infiltrate a wealthy household."],
        ["id" => 14, "title" => "Joker", "image_url" => "https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg", "genre" => "Drama", "release_year" => 2019, "rating" => 8.4, "duration" => "2h", "description" => "A troubled man descends into madness and becomes Joker."],
        ["id" => 15, "title" => "Titanic", "image_url" => "https://image.tmdb.org/t/p/w500/9xjZS2rlVxm8SFx8kPC3aIGCOYQ.jpg", "genre" => "Romance", "release_year" => 1997, "rating" => 7.9, "duration" => "3h 15m", "description" => "A love story unfolds aboard the ill-fated Titanic."],
        ["id" => 16, "title" => "John Wick", "image_url" => "https://image.tmdb.org/t/p/w500/fZPSd91yGE9fCcCe6OoQr6E3Bev.jpg", "genre" => "Action", "release_year" => 2014, "rating" => 7.4, "duration" => "1h 41m", "description" => "A retired hitman seeks revenge."],
        ["id" => 17, "title" => "Mad Max: Fury Road", "image_url" => "https://image.tmdb.org/t/p/w500/8tZYtuWezp8JbcsvHYO0O46tFbo.jpg", "genre" => "Action", "release_year" => 2015, "rating" => 8.1, "duration" => "2h", "description" => "A high-speed chase across a post-apocalyptic wasteland."],
        ["id" => 18, "title" => "Gladiator", "image_url" => "https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg", "genre" => "Action", "release_year" => 2000, "rating" => 8.5, "duration" => "2h 35m", "description" => "A betrayed general seeks revenge in ancient Rome."],
        ["id" => 19, "title" => "Blade Runner 2049", "image_url" => "https://image.tmdb.org/t/p/w500/gajva2L0rPYkEWjzgFlBXCAVBE5.jpg", "genre" => "Sci-Fi", "release_year" => 2017, "rating" => 8.0, "duration" => "2h 44m", "description" => "A blade runner uncovers a long-buried secret."],
        ["id" => 20, "title" => "Gravity", "image_url" => "https://image.tmdb.org/t/p/w500/kZ2nZw8D681aphje8NJi8EfbL1U.jpg", "genre" => "Sci-Fi", "release_year" => 2013, "rating" => 7.7, "duration" => "1h 31m", "description" => "Astronauts struggle to survive in space after disaster strikes."],
        ["id" => 21, "title" => "Fight Club", "image_url" => "https://image.tmdb.org/t/p/w500/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg", "genre" => "Drama", "release_year" => 1999, "rating" => 8.8, "duration" => "2h 19m", "description" => "An underground fight club spirals into chaos."],
        ["id" => 22, "title" => "Forrest Gump", "image_url" => "https://image.tmdb.org/t/p/w500/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg", "genre" => "Drama", "release_year" => 1994, "rating" => 8.8, "duration" => "2h 22m", "description" => "A simple man lives through extraordinary events."],
        ["id" => 23, "title" => "The Shawshank Redemption", "image_url" => "https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg", "genre" => "Drama", "release_year" => 1994, "rating" => 9.3, "duration" => "2h 22m", "description" => "Two imprisoned men bond over years of hope."],
        ["id" => 24, "title" => "The Godfather", "image_url" => "https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg", "genre" => "Crime", "release_year" => 1972, "rating" => 9.2, "duration" => "2h 55m", "description" => "The aging patriarch transfers control of his empire to his son."],
        ["id" => 25, "title" => "Pulp Fiction", "image_url" => "https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg", "genre" => "Crime", "release_year" => 1994, "rating" => 8.9, "duration" => "2h 34m", "description" => "Interwoven stories of crime in Los Angeles."],
        ["id" => 26, "title" => "The Lion King", "image_url" => "https://image.tmdb.org/t/p/w500/sKCr78MXSLixwmZ8DyJLrpMsd15.jpg", "genre" => "Drama", "release_year" => 1994, "rating" => 8.5, "duration" => "1h 28m", "description" => "A young lion prince flees his kingdom."],
        ["id" => 27, "title" => "Avatar", "image_url" => "https://image.tmdb.org/t/p/w500/kyeqWdyUXW608qlYkRqosgbbJyK.jpg", "genre" => "Sci-Fi", "release_year" => 2009, "rating" => 7.9, "duration" => "2h 42m", "description" => "A marine explores an alien world called Pandora."],
        ["id" => 28, "title" => "Doctor Strange", "image_url" => "https://image.tmdb.org/t/p/w500/uGBVj3bEbCoZbDjjl9wTxcygko1.jpg", "genre" => "Action", "release_year" => 2016, "rating" => 7.5, "duration" => "1h 55m", "description" => "A surgeon learns the mystic arts after an accident."],
        ["id" => 29, "title" => "Black Panther", "image_url" => "https://image.tmdb.org/t/p/w500/uxzzxijgPIY7slzFvMotPv8wjKA.jpg", "genre" => "Action", "release_year" => 2018, "rating" => 7.3, "duration" => "2h 14m", "description" => "The king of Wakanda rises to defend his nation."],
        ["id" => 30, "title" => "The Social Network", "image_url" => "https://image.tmdb.org/t/p/w500/n0ybibhJtQ5icDqTp8eRytcIHJx.jpg", "genre" => "Drama", "release_year" => 2010, "rating" => 7.7, "duration" => "2h", "description" => "The story behind the creation of Facebook."]
    ]
]);

echo $movies;
?>