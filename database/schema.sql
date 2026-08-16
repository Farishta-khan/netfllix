CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    image_url TEXT,
    video_url TEXT,
    genre VARCHAR(50),
    release_year INT,
    rating FLOAT,
    duration VARCHAR(50),
    cast TEXT
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    UNIQUE KEY user_movie_unique (user_id, movie_id)
);