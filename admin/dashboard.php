<?php
require_once __DIR__ . '/admin-auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

// Logout
if (($_GET['action'] ?? '') === 'logout') {
    adminLogout();
    header('Location: login.php');
    exit;
}

// Fetch recent contact messages (newest first)
$messages = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC, id DESC')->fetchAll();

$total  = count($messages);
$latest = $total ? $messages[0] : null;
$today  = 0;
foreach ($messages as $m) {
    if (date('Y-m-d', strtotime($m['created_at'])) === date('Y-m-d')) $today++;
}

function timeAgo(string $ts): string
{
    $diff = time() - strtotime($ts);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($ts));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard &middot; Contact Enquiries</title>
    <style>
        :root {
            --bg: #0b0e0a;
            --card: rgba(255,255,255,.045);
            --card-hover: rgba(255,255,255,.075);
            --line: rgba(255,255,255,.1);
            --accent: #a3c14b;
            --accent-soft: rgba(163,193,75,.14);
            --ink: #f2f4ec;
            --ink-dim: #a8b096;
            --ink-faint: #767d68;
        }
        * { box-sizing: border-box; }
        body.admin-shell {
            margin: 0; min-height: 100vh;
            background:
                radial-gradient(900px 420px at 85% -10%, rgba(163,193,75,.09), transparent 60%),
                radial-gradient(700px 380px at -10% 30%, rgba(46,88,180,.07), transparent 60%),
                var(--bg);
            color: var(--ink);
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .admin-topbar {
            position: sticky; top: 0; z-index: 50;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: .95rem 2rem;
            background: rgba(11,14,10,.82);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            animation: bar-in .5s cubic-bezier(.2,.75,.2,1) both;
        }
        @keyframes bar-in { from { opacity: 0; transform: translateY(-14px); } to { opacity: 1; transform: none; } }
        .admin-topbar .brand { display: flex; align-items: center; gap: .75rem; font-weight: 800; }
        .admin-topbar .brand .mark {
            width: 34px; height: 34px; border-radius: 10px;
            display: grid; place-items: center;
            background: linear-gradient(135deg, var(--accent), #5f7524);
            color: #10140c; font-size: .95rem; flex: none;
        }
        .admin-topbar .brand small {
            display: block; color: var(--accent);
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .58rem; letter-spacing: .22em; font-weight: 400;
        }
        .admin-topbar .right { display: flex; align-items: center; gap: 1rem; }
        .admin-user { display: flex; align-items: center; gap: .6rem; font-size: .85rem; color: var(--ink-dim); }
        .admin-user .ava {
            width: 30px; height: 30px; border-radius: 50%;
            display: grid; place-items: center;
            background: var(--accent-soft); color: var(--accent);
            font-weight: 700; font-size: .8rem;
        }
        .admin-logout {
            color: #10140c; background: var(--accent); text-decoration: none;
            font-size: .78rem; font-weight: 700; letter-spacing: .04em;
            padding: .5rem .95rem; border-radius: 999px;
            transition: transform .18s ease, filter .18s ease, box-shadow .25s ease;
        }
        .admin-logout:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 8px 22px rgba(163,193,75,.3); }
        .admin-logout:focus-visible { outline: 3px solid #ffd76a; outline-offset: 2px; }
        .admin-main { max-width: 1060px; margin: 0 auto; padding: 2.6rem 1.5rem 5rem; }
        .admin-heading { margin-bottom: 2.2rem; animation: rise .6s .05s cubic-bezier(.2,.75,.2,1) both; }
        .admin-heading .kicker {
            display: inline-flex; align-items: center; gap: .5rem;
            color: var(--accent); font-family: 'DM Mono', Consolas, monospace;
            font-size: .68rem; letter-spacing: .2em; text-transform: uppercase; margin-bottom: .7rem;
        }
        .admin-heading .kicker::before { content: ''; width: 26px; height: 1px; background: var(--accent); }
        .admin-heading h1 { margin: 0 0 .5rem; font-size: clamp(1.7rem, 4vw, 2.5rem); font-weight: 700; letter-spacing: -.02em; line-height: 1.1; }
        .admin-heading h1 em { font-family: Georgia, serif; font-weight: 400; color: var(--accent); }
        .admin-heading .lede { margin: 0; color: var(--ink-dim); font-size: .95rem; max-width: 560px; line-height: 1.65; }
        @keyframes rise { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: none; } }
        .admin-stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem; margin-bottom: 2.4rem;
        }
        .stat-card {
            position: relative;
            background: var(--card); border: 1px solid var(--line);
            border-radius: 18px; padding: 1.3rem 1.4rem; overflow: hidden;
            transition: transform .3s cubic-bezier(.2,.75,.2,1), border-color .3s, background .3s, box-shadow .3s;
            animation: rise .6s cubic-bezier(.2,.75,.2,1) both;
        }
        .stat-card:nth-child(1) { animation-delay: .12s; }
        .stat-card:nth-child(2) { animation-delay: .2s; }
        .stat-card:nth-child(3) { animation-delay: .28s; }
        .stat-card:hover {
            transform: translateY(-4px);
            background: var(--card-hover);
            border-color: rgba(163,193,75,.4);
            box-shadow: 0 18px 42px rgba(0,0,0,.35);
        }
        .stat-card .icon {
            width: 40px; height: 40px; border-radius: 12px;
            display: grid; place-items: center;
            background: var(--accent-soft); color: var(--accent);
            font-size: 1.05rem; margin-bottom: .9rem;
        }
        .stat-card b { display: block; font-size: 2.1rem; font-weight: 700; letter-spacing: -.03em; line-height: 1; margin-bottom: .35rem; }
        .stat-card span { color: var(--ink-faint); font-size: .8rem; }
        .stat-card::after {
            content: ''; position: absolute; top: 0; left: 10%;
            width: 80%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(163,193,75,.5), transparent);
            opacity: 0; transition: opacity .4s;
        }
        .stat-card:hover::after { opacity: 1; }
        .section-label {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.1rem; color: var(--ink-dim);
            font-size: .82rem; letter-spacing: .06em; text-transform: uppercase;
            animation: rise .6s .32s cubic-bezier(.2,.75,.2,1) both;
        }
        .section-label b { color: var(--ink); }
        .msg-list { display: flex; flex-direction: column; gap: .85rem; }
        .msg-card {
            background: var(--card); border: 1px solid var(--line);
            border-radius: 16px; padding: 1.15rem 1.35rem;
            transition: transform .28s cubic-bezier(.2,.75,.2,1), border-color .28s, background .28s, box-shadow .28s;
            animation: rise .55s cubic-bezier(.2,.75,.2,1) both;
            animation-delay: calc(.36s + var(--i, 0) * .07s);
        }
        .msg-card:hover {
            transform: translateY(-3px);
            background: var(--card-hover);
            border-color: rgba(163,193,75,.35);
            box-shadow: 0 14px 34px rgba(0,0,0,.3);
        }
        .msg-card.is-new { border-left: 3px solid var(--accent); }
        .msg-head { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .5rem; }
        .msg-head .who { display: flex; align-items: center; gap: .7rem; }
        .msg-head .who .dot {
            width: 34px; height: 34px; border-radius: 10px;
            display: grid; place-items: center;
            background: var(--accent-soft); color: var(--accent);
            font-weight: 700; font-size: .8rem; flex: none;
        }
        .msg-head .who b { color: var(--ink); font-size: .95rem; }
        .msg-head .who span { color: var(--ink-faint); font-weight: 400; font-size: .82rem; }
        .msg-head time {
            color: var(--ink-faint); font-size: .72rem;
            font-family: 'DM Mono', Consolas, monospace;
            background: rgba(255,255,255,.05); border: 1px solid var(--line);
            padding: .25rem .6rem; border-radius: 999px; white-space: nowrap;
        }
        .msg-card p.msg { color: var(--ink-dim); font-size: .9rem; line-height: 1.65; margin: 0 0 .7rem; white-space: pre-wrap; }
        .msg-card a.mail {
            display: inline-flex; align-items: center; gap: .4rem;
            color: var(--accent); font-size: .8rem; font-weight: 600; text-decoration: none;
            padding: .4rem .8rem; border: 1px solid rgba(163,193,75,.3); border-radius: 999px;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .msg-card a.mail:hover { background: var(--accent-soft); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(163,193,75,.18); }
        .msg-card a.mail:focus-visible { outline: 2px solid #ffd76a; outline-offset: 2px; }
        .admin-empty {
            border: 1px dashed rgba(255,255,255,.18); border-radius: 18px;
            padding: 3.2rem 2rem; text-align: center; color: var(--ink-faint);
            animation: rise .6s .35s cubic-bezier(.2,.75,.2,1) both;
        }
        .admin-empty .big { font-size: 2rem; display: block; margin-bottom: .7rem; opacity: .7; }
        @media (prefers-reduced-motion: reduce) {
            .admin-topbar, .admin-heading, .stat-card, .msg-card, .section-label, .admin-empty { animation: none; }
            .stat-card, .msg-card, .admin-logout, .msg-card a.mail { transition: none; }
        }
        @media (max-width: 640px) {
            .admin-topbar { padding: .8rem 1.1rem; }
            .admin-topbar .brand small { display: none; }
            .admin-user span.uname { display: none; }
            .admin-main { padding: 1.8rem 1rem 4rem; }
            .msg-card { padding: 1rem 1.05rem; }
        }
    </style>
</head>

<body class="admin-shell">
    <div class="admin-topbar">
        <div class="brand">
            <span class="mark" aria-hidden="true">C</span>
            <span>Comcares Admin <small>CONTROL PANEL</small></span>
        </div>
        <div class="right">
            <span class="admin-user">
                <span class="ava" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['admin_name'], 0, 1))) ?></span>
                <span><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            </span>
            <a class="admin-logout" href="dashboard.php?action=logout">Logout</a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-heading">
            <p class="kicker">Overview / Inbox</p>
            <h1>Recent contact <em>enquiries.</em></h1>
            <p class="lede">Everyone who has recently contacted you through the website's contact form &mdash; newest first.</p>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <span class="icon" aria-hidden="true">&#9993;</span>
                <b><?= $total ?></b>
                <span>Total messages received</span>
            </div>
            <div class="stat-card">
                <span class="icon" aria-hidden="true">&#9788;</span>
                <b><?= $today ?></b>
                <span>Received today</span>
            </div>
            <div class="stat-card">
                <span class="icon" aria-hidden="true">&#9201;</span>
                <b style="font-size:1.25rem; padding-top:.5rem;"><?= $latest ? htmlspecialchars($latest['name']) : '&mdash;' ?></b>
                <span><?= $latest ? 'Most recent &middot; ' . timeAgo($latest['created_at']) : 'No messages yet' ?></span>
            </div>
        </div>

        <div class="section-label">
            <span>Inbox</span>
            <span><b><?= $total ?></b> message<?= $total === 1 ? '' : 's' ?></span>
        </div>

        <?php if (!$messages): ?>
            <div class="admin-empty">
                <span class="big" aria-hidden="true">&#128233;</span><br>
                No contact messages yet. When someone fills in the contact form, they'll appear here.
            </div>
        <?php else: ?>
            <div class="msg-list">
                <?php foreach ($messages as $i => $msg): ?>
                    <article class="msg-card <?= $i === 0 ? 'is-new' : '' ?>" style="--i: <?= min($i, 8) ?>">
                        <div class="msg-head">
                            <div class="who">
                                <span class="dot" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($msg['name'], 0, 1))) ?></span>
                                <b><?= htmlspecialchars($msg['name']) ?></b>
                                <span>&lt;<?= htmlspecialchars($msg['email']) ?>&gt;</span>
                            </div>
                            <time title="<?= htmlspecialchars($msg['created_at']) ?>"><?= timeAgo($msg['created_at']) ?></time>
                        </div>
                        <p class="msg"><?= htmlspecialchars($msg['message']) ?></p>
                        <a class="mail" href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: your enquiry to Comcares">&#9993; Reply to this person</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>