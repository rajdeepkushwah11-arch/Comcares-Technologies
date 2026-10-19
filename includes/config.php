<?php
// Site-wide configuration constants
define('SITE_NAME', 'Comcares Technology');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
