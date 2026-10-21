<?php

$path = $_GET['path'] ?? 'index.php';

if ($path === 'api/chatbot.php') {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => [
            'type' => 'unavailable',
            'message' => 'Chat is unavailable in this design preview.',
        ],
    ]);
    exit;
}

$pages = [
    'index.php',
    'about.php',
    'services.php',
    'products.php',
    'contact.php',
    'login.php',
    'logout.php',
];

if (!in_array($path, $pages, true)) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
