<?php
// Shared layout + helpers for admin section pages (quotes, orders, chats, users)
require_once __DIR__ . '/admin-auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

if (($_GET['action'] ?? '') === 'logout') {
    adminLogout();
    header('Location: login.php');
    exit;
}

function adminTimeAgo(string $ts): string
{
    $diff = time() - strtotime($ts);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($ts));
}

function adminCounts(): array
{
    static $c = null;
    if ($c === null) {
        $pdo = db();
        $c = [
            'messages' => (int)$pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
            'quotes'   => (int)$pdo->query('SELECT COUNT(*) FROM quotations')->fetchColumn(),
            'orders'   => (int)$pdo->query('SELECT COUNT(*) FROM purchases')->fetchColumn(),
            'sessions' => (int)$pdo->query('SELECT COUNT(*) FROM chat_sessions')->fetchColumn(),
            'msgs'     => (int)$pdo->query('SELECT COUNT(*) FROM chat_messages')->fetchColumn(),
            'users'    => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        ];
    }
    return $c;
}

function adminStatusBadge(string $status): string
{
    $colors = [
        'new' => '#2473e8',
        'reviewed' => '#8a63d2',
        'quoted' => '#0a9a75',
        'closed' => '#8793a8',
        'contacted' => '#8a63d2',
        'confirmed' => '#0a9a75',
        'delivered' => '#0a9a75',
        'cancelled' => '#c0392b',
        'active' => '#0a9a75',
        'suspended' => '#c0392b',
        'pending' => '#b8860b',
    ];
    $c = $colors[$status] ?? '#8793a8';
    return '<span style="display:inline-block;padding:.25rem .7rem;border-radius:999px;font:700 .68rem/1 Manrope,sans-serif;color:#fff;background:' . $c . '">' . htmlspecialchars($status) . '</span>';
}

function adminStatusForm(string $table, int $id, string $current, array $options): string
{
    $opts = '';
    foreach ($options as $o) {
        $opts .= '<option value="' . $o . '" ' . ($o === $current ? 'selected' : '') . '>' . ucfirst($o) . '</option>';
    }
    $nonce = htmlspecialchars($table);
    return '<form method="post" action="status.php" style="display:inline-flex;gap:.4rem;align-items:center;margin:0">'
        . '<input type="hidden" name="table" value="' . $nonce . '">'
        . '<input type="hidden" name="id" value="' . (int)$id . '">'
        . '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf'] ?? '') . '">'
        . '<select name="status" style="min-height:32px;border:1px solid #ccd6e4;border-radius:6px;padding:.2rem .5rem;font:600 .74rem Manrope,sans-serif">' . $opts . '</select>'
        . '<button style="min-height:32px;border:1px solid #2473e8;background:#2473e8;color:#fff;border-radius:6px;padding:.2rem .7rem;font:700 .72rem Manrope,sans-serif;cursor:pointer">Save</button>'
        . '</form>';
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
