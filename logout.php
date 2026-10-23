<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';
logoutUser();
header('Location: index.php');
exit;
