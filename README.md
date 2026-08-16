Portfolio Project — Netflix Clone

Overview

This is a Netflix-style single-page application (frontend) backed by simple PHP endpoints and MySQL. The project demonstrates a full-stack web app with a responsive, modern UI, movie browsing, movie detail modal, search, "My List" persistence (localStorage), and secure user authentication (password hashing).

What's included

- Frontend: plain HTML/CSS/JavaScript (no build tools). Main files:
  - frontend/index.html
  - frontend/css/style.css
  - frontend/js/app.js
- Backend: PHP endpoints + DB helper
  - backend/get_movies.php — returns JSON movie data (demo includes video_url values pointing to public sample videos for streaming demo)
  - backend/register.php — user registration (passwords hashed with password_hash)
  - backend/login.php — user login (password_verify)
  - backend/db.php — MySQL connection helper
  - backend/setup_db.php — creates movies, users, and user_favorites tables (users.email is UNIQUE)
- Database schema: database/schema.sql

Quick start (XAMPP / local PHP + MySQL)

1. Start XAMPP (Apache + MySQL).
2. Create a database named `netflixx` (or change details in backend/db.php).
3. Run backend/setup_db.php once in your browser to create tables (http://localhost/your-path/backend/setup_db.php).
4. Place the project in your web root (for example: htdocs/netfllix.worktrees/portfolio-project-enhancement-guide/frontend).
5. Open frontend/index.html in browser via the server (e.g. http://localhost/netfllix.worktrees/portfolio-project-enhancement-guide/frontend/index.html).

Notes

- For demo streaming, some movies include `video_url` fields pointing to public sample videos (BigBuckBunny, Elephant's Dream, etc.). In production you'd host or proxy your own video files and implement proper streaming (HLS/DASH) and DRM if needed.
- Passwords are hashed using PHP's password_hash and verified with password_verify.
- This repo is intended for portfolio showcase. Next improvements: server-side "My List", profiles, ratings, TMDB integration, containerization, and deploying to a cloud host.

What I changed (short)

- Added password hashing (register.php) and verification (login.php).
- Implemented JWT-based authentication: login and register now return a signed token used by the frontend for protected API calls.
- Added server-side "My List" (favorites) API and DB table (user_favorites) plus frontend wiring to store a user's favorites on the server when logged in.
- Added demo video_url fields and a video player overlay with Play/Close handling.
- Fixed duplicate JS function and improved modal/player UX.
- Made users.email UNIQUE in DB schema and setup script.
- Added README with run instructions.

If you'd like, next steps can be:
- Move additional features server-side (ratings, comments) and add admin tooling.
- Add Dockerfile and a deployment script to publish a live demo.

API (brief)

- POST backend/register.php  — form-data: email, password. Returns JSON {status, user, token} on success.
- POST backend/login.php     — form-data: email, password. Returns JSON {status, user, token} on success.
- GET  backend/get_movies.php — returns {status, data} movie list (some items include video_url for demo streaming).
- GET/POST/DELETE backend/favorites.php — protected; requires Authorization: Bearer <token>. GET returns the user's favorites; POST {movie_id} adds; DELETE {movie_id} removes.

Notes on secrets

- The JWT secret is set in backend/config.php for development. Do not commit a production secret — use environment variables or a secrets manager in real deployments.

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>