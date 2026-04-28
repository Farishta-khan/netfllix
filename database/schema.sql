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
    email VARCHAR(255),
    password VARCHAR(255)
);