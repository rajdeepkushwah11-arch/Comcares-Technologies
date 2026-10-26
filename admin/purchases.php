<?php
require_once __DIR__ . '/_admin-layout.php';
$orders = db()->query('SELECT * FROM purchases ORDER BY created_at DESC, id DESC')->fetchAll();
$counts = adminCounts();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin &middot; Product Purchases</title>
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
            max-width: 1200px;
            padding: 1.75rem 3rem 4rem;
        }

        h1 {
            font-size: 1.6rem;
            letter-spacing: -.02em;
            margin: .3rem 0 1.8rem;
        }

        .msg {
            position: relative;
            background: #fff;
            border: 1px solid #e4e9f1;
            border-radius: 10px;
            padding: 1.25rem 1.4rem;
            margin-bottom: .75rem;
        }

        .msg-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: .5rem;
        }

        .msg-head b {
            font-size: .94rem;
        }

        .msg-head .em {
            color: #8793a8;
            font-size: .8rem;
            font-weight: 400;
        }

        .msg time {
            color: #8793a8;
            font: 500 .68rem 'DM Mono', monospace;
        }

        .kv {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin: .4rem 0 .9rem;
            color: #536176;
            font-size: .85rem;
        }

        .kv b {
            color: #172033;
        }

        .msg-actions {
            display: flex;
            gap: .6rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .msg a.mail {
            color: #2473e8;
            border: 1px solid #bdd4f7;
            border-radius: 7px;
            padding: .4rem .9rem;
            font: 700 .76rem Manrope, sans-serif;
            text-decoration: none;
        }

        .msg a.mail:hover {
            background: #2473e8;
            color: #fff;
        }

        .empty {
            border: 1px dashed #ccd6e4;
            border-radius: 16px;
            padding: 3.6rem 2rem;
            text-align: center;
            color: #5e6b80;
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
                <a href="purchases.php" class="active">Product Purchases <span class="cnt"><?= $counts['orders'] ?></span></a>
                <a href="chats.php">Chatbot Conversations <span class="cnt"><?= $counts['sessions'] ?></span></a>
                <a href="users.php">Login / Users <span class="cnt"><?= $counts['users'] ?></span></a>
            </nav>
            <div class="spacer"></div>
            <div class="who"><b><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></b><span><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></span></div>
            <a class="out" href="?action=logout">Logout</a>
        </aside>
        <main class="main">
            <h1>Product Purchase Requests</h1>
            <?php if (!$orders): ?>
                <div class="empty">No purchase requests yet. When a visitor submits the purchase form, it will appear here.</div>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <div class="msg">
                    <div class="msg-head">
                        <div><b>Order #<?= (int)$o['id'] ?> &middot; <?= htmlspecialchars($o['name']) ?></b><br><span class="em"><?= htmlspecialchars($o['email']) ?></span></div>
                        <div style="text-align:right"><?= adminStatusBadge($o['status']) ?><br><time><?= adminTimeAgo($o['created_at']) ?></time></div>
                    </div>
                    <div class="kv">
                        <span>Product: <b><?= htmlspecialchars($o['product']) ?></b></span>
                        <span>Quantity: <b><?= (int)$o['quantity'] ?></b></span>
                        <?php if ($o['phone'] !== ''): ?><span>Phone: <b><?= htmlspecialchars($o['phone']) ?></b></span><?php endif; ?>
                    </div>
                    <?php if ($o['message'] !== ''): ?><p><?= htmlspecialchars($o['message']) ?></p><?php endif; ?>
                    <div class="msg-actions">
                        <a class="mail" href="mailto:<?= htmlspecialchars($o['email']) ?>">Reply by email</a>
                        <?= adminStatusForm('purchases', (int)$o['id'], $o['status'], ['new', 'contacted', 'confirmed', 'delivered', 'cancelled']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
</body>

</html>