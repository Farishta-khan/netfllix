<?php
// Change this secret before deploying. Keep it safe (use environment variables in production).
if (!defined('JWT_SECRET')) define('JWT_SECRET', 'replace_this_with_a_long_random_secret_ChangeMe');
if (!defined('JWT_ALGO')) define('JWT_ALGO', 'HS256');
?>