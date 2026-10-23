<?php
// Site-wide configuration constants
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Comcares Technology');
}

// Optional public site URL for password-reset emails. Leave empty for automatic local URL detection.
// Set this to your HTTPS domain before deploying (for example, https://www.example.com).
if (!defined('SITE_URL')) {
    define('SITE_URL', getenv('SITE_URL') ?: '');
}
if (!defined('RESET_LINK_TTL')) {
    define('RESET_LINK_TTL', 86400); // 24 hours
}
if (!defined('RESET_FROM_EMAIL')) {
    define('RESET_FROM_EMAIL', 'no-reply@comcares.local');
}

// Match PHP's clock to the server's timezone (Asia/Kolkata = IST, UTC+5:30).
// Without this, PHP falls back to UTC and "time ago" calculations drift.
date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
