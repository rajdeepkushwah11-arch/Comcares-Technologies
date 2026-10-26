<?php
require_once __DIR__ . '/_admin-layout.php';

$pdo = db();

// Optional single-session detail view
$focus = preg_replace('/[^a-z0-9]/i', '', (string)($_GET['session'] ?? ''));
if ($focus !== '') {
    $stmt = $pdo->prepare('SELECT sender, message, created_at FROM chat_messages WHERE session_id = ? ORDER BY id ASC');
    $stmt->execute([$focus]);
    $msgs = $stmt->fetchAll();
}

$sessions = $pdo->query(
    'SELECT cs.session_id, cs.created_at, COUNT(cm.id) AS total,
            SUM(cm.sender = "user") AS user_msgs,
            (SELECT message FROM chat_messages WHERE session_id = cs.session_id AND sender = "user" ORDER BY id DESC LIMIT 1) AS last_user
     FROM chat_sessions cs
     LEFT JOIN chat_messages cm ON cm.session_id = cs.session_id
     GROUP BY cs.session_id, cs.created_at
     ORDER BY cs.created_at DESC'
)->fetchAll();
$counts = adminCounts();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin &middot; Chatbot Conversations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background: #f3f5f9;
            color: #172033;
            font-family: 'Manrope', system-ui, sans-serif;
        }

        .shell {
            display: flex;
            min-height: 100vh;
        }

        .side {
            position: sticky;
            top: 0;
            align-self: flex-start;
            width: 258px;
            height: 100vh;
            padding: 1.5rem 1rem;
            background: #111c31;
            color: #fff;
            display: flex;
            flex-direction: column;
        }

        .side .brand {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin: .15rem .55rem 1.65rem;
            font-weight: 800;
        }

        .side .brand::before {
            content: 'C';
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #2473e8;
            font: 800 .95rem/1 Manrope, sans-serif;
        }

        .side .hairline {
            height: 1px;
            background: rgba(255, 255, 255, .1);
            margin: 0 .55rem 1rem;
        }

        .side nav {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .side nav a {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .78rem .85rem;
            border-radius: 8px;
            color: #aebbd0;
            text-decoration: none;
            font-size: .86rem;
            font-weight: 600;
        }

        .side nav a:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        .side nav a.active {
            background: #2473e8;
            color: #fff;
            box-shadow: 0 7px 15px rgba(0, 0, 0, .13);
        }

        .side nav a .cnt {
            margin-left: auto;
            font: 500 .64rem 'DM Mono', monospace;
            color: #fff;
        }

        .side .spacer {
            flex: 1;
        }

        .side .who {
            padding: 1rem .55rem 0;
            border-top: 1px solid rgba(255, 255, 255, .13);
            margin-top: 1rem;
        }

        .side .who b {
            display: block;
            font-size: .84rem;
        }

        .side .who span {
            color: #91a0b8;
            font-size: .7rem;
        }

        .side .out {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin: .9rem .55rem 0;
            padding: .6rem .8rem;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 10px;
            color: #d9e2f0;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 700;
        }

        .side .out:hover {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .main {
            flex: 1;
            min-width: 0;
            max-width: 1100px;
            padding: 1.75rem 3rem 4rem;
        }

        h1 {
            font-size: 1.6rem;
            letter-spacing: -.02em;
            margin: .3rem 0 1.8rem;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            background: #fff;
            border: 1px solid #e4e9f1;
            border-radius: 10px;
            padding: 1rem 1.3rem;
            margin-bottom: .6rem;
        }

        .row .sid {
            font: 500 .7rem 'DM Mono', monospace;
            color: #5e6b80;
            word-break: break-all;
        }

        .row .last {
            color: #172033;
            font-size: .88rem;
            font-weight: 600;
        }

        .row time {
            color: #8793a8;
            font: 500 .68rem 'DM Mono', monospace;
            white-space: nowrap;
        }

        .row a.view {
            color: #2473e8;
            border: 1px solid #bdd4f7;
            border-radius: 7px;
            padding: .35rem .8rem;
            font: 700 .74rem Manrope, sans-serif;
            text-decoration: none;
            white-space: nowrap;
        }

        .row a.view:hover {
            background: #2473e8;
            color: #fff;
        }

        .bubble {
            max-width: 78%;
            padding: .8rem 1.1rem;
            border-radius: 14px;
            margin-bottom: .55rem;
            font-size: .88rem;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .bubble.user {
            background: #eaf1ff;
            color: #172033;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .bubble.bot {
            background: #fff;
            border: 1px solid #e4e9f1;
            color: #536176;
            border-bottom-left-radius: 4px;
        }

        .thread {
            background: #f6f8fc;
            border: 1px solid #e4e9f1;
            border-radius: 12px;
            padding: 1.4rem;
            margin-top: 1.5rem;
        }

        .thread h2 {
            font-size: 1rem;
            margin: 0 0 1rem;
        }

        .bubble time {
            display: block;
            margin-top: .3rem;
            font: 400 .62rem 'DM Mono', monospace;
            opacity: .7;
        }

        .empty {
            border: 1px dashed #ccd6e4;
            border-radius: 16px;
            padding: 3.6rem 2rem;
            text-align: center;
            color: #5e6b80;
        }

        .back {
            display: inline-block;
            margin-bottom: 1rem;
            color: #2473e8;
            text-decoration: none;
            font-weight: 700;
            font-size: .82rem;
        }

        @media (max-width:900px) {
            .shell {
                flex-direction: column
            }

            .side {
                position: static;
                width: 100%;
                height: auto
            }

            .main {
                padding: 1.3rem 1.25rem 3rem
            }

            .bubble {
                max-width: 94%
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <aside class="side">
            <div class="brand">Comcares <small>ADMIN</small></div>
            <div class="hairline"></div>
            <nav>
                <a href="dashboard.php">Enquiries <span class="cnt"><?= $counts['messages'] ?></span></a>
                <a href="quotations.php">Quotations <span class="cnt"><?= $counts['quotes'] ?></span></a>
                <a href="purchases.php">Product Purchases <span class="cnt"><?= $counts['orders'] ?></span></a>
                <a href="chats.php" class="active">Chatbot Conversations <span class="cnt"><?= $counts['sessions'] ?></span></a>
                <a href="users.php">Login / Users <span class="cnt"><?= $counts['users'] ?></span></a>
            </nav>
            <div class="spacer"></div>
            <div class="who"><b><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></b><span><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></span></div>
            <a class="out" href="?action=logout">Logout</a>
        </aside>
        <main class="main">
            <h1>Chatbot Conversations</h1>
            <?php if ($focus !== ''): ?>
                <a class="back" href="chats.php">&larr; All conversations</a>
                <div class="thread">
                    <h2>Session: <?= htmlspecialchars($focus) ?></h2>
                    <?php foreach ($msgs as $m): ?>
                        <div class="bubble <?= $m['sender'] === 'user' ? 'user' : 'bot' ?>">
                            <?= htmlspecialchars($m['message']) ?>
                            <time><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></time>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$msgs): ?><div class="empty">No messages in this session.</div><?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (!$sessions): ?>
                    <div class="empty">No chatbot conversations yet. Once visitors use the assistant, their sessions appear here.</div>
                <?php endif; ?>
                <?php foreach ($sessions as $s): ?>
                    <div class="row">
                        <div>
                            <div class="last"><?= htmlspecialchars(mb_substr((string)$s['last_user'] ?: '(no user message)', 0, 90)) ?></div>
                            <div class="sid"><?= htmlspecialchars($s['session_id']) ?> &middot; <?= (int)$s['total'] ?> messages (<?= (int)$s['user_msgs'] ?> from visitor)</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:1rem">
                            <time><?= adminTimeAgo($s['created_at']) ?></time>
                            <a class="view" href="chats.php?session=<?= urlencode($s['session_id']) ?>">View thread</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>