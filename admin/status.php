<?php
// Handles status updates + admin notes for quotations and purchases
require_once __DIR__ . '/_admin-layout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400);
    exit('Bad request');
}

$table  = $_POST['table'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowed = [
    'quotations' => ['new', 'reviewed', 'quoted', 'closed'],
    'purchases'  => ['new', 'contacted', 'confirmed', 'delivered', 'cancelled'],
];

if (!isset($allowed[$table]) || $id <= 0 || !in_array($status, $allowed[$table], true)) {
    http_response_code(400);
    exit('Bad request');
}

$stmt = db()->prepare("UPDATE {$table} SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

$back = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
header('Location: ' . $back);
exit;
