<?php
// Copy this file to backend/config.php and replace the placeholder values with your environment values.
// Never commit real secrets to version control. Use environment variables in production.

if (!defined('JWT_SECRET')) define('JWT_SECRET', 'REPLACE_ME_WITH_A_STRONG_RANDOM_SECRET');
if (!defined('JWT_ALGO')) define('JWT_ALGO', 'HS256');

// Database settings (optional override)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'netflixx');
?>