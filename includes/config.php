<?php
// Site-wide configuration constants
define('SITE_NAME', 'Comcares Technology');

// Match PHP's clock to the server's timezone (Asia/Kolkata = IST, UTC+5:30).
// Without this, PHP falls back to UTC and "time ago" calculations drift.
date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
