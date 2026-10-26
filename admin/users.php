<?php
require_once __DIR__ . '/_admin-layout.php';

// Suspend / activate a user account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'toggle') {
    if (hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $uid = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT status FROM users WHERE id = ?');
        $stmt->execute([$uid]);
        $cur = $stmt->fetchColumn();
        if ($cur !== false) {
            $next = $cur === 'active' ? 'suspended' : 'active';
            $upd = db()->prepare('UPDATE users SET status = ? WHERE id = ?');
            $upd->execute([$next, $uid]);
        }
    }
    header('Location: users.php');
    exit;
}

$users = db()->query('SELECT id, name, email, status, created_at FROM users ORDER BY created_at DESC, id DESC')->fetchAll();
$counts = adminCounts();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin &middot; Registered Users</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid #e4e9f1;
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            text-align: left;
            padding: .85rem 1.1rem;
            font-size: .86rem;
            border-bottom: 1px solid #eef2f8;
        }

        th {
            background: #f6f8fc;
            font-size: .68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #728096;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        td .toggle {
            border: 1px solid #bdd4f7;
            background: #fff;
            color: #2473e8;
            border-radius: 7px;
            padding: .35rem .8rem;
            font: 700 .72rem Manrope, sans-serif;
            cursor: pointer;
        }

        td .toggle:hover {
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

        .hint {
            color: #718096;
            font-size: .8rem;
            margin-bottom: 1.2rem;
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

            .tablewrap {
                overflow-x: auto
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
                <a href="chats.php">Chatbot Conversations <span class="cnt"><?= $counts['sessions'] ?></span></a>
                <a href="users.php" class="active">Login / Users <span class="cnt"><?= $counts['users'] ?></span></a>
            </nav>
            <div class="spacer"></div>
            <div class="who"><b><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></b><span><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></span></div>
            <a class="out" href="?action=logout">Logout</a>
        </aside>
        <main class="main">
            <h1>Registered User Accounts</h1>
            <p class="hint">Passwords are stored hashed and are never visible to anyone, including admins. You can suspend or reactivate an account below.</p>
            <?php if (!$users): ?>
                <div class="empty">No user accounts registered yet. Sign-ups from login.php will appear here.</div>
            <?php else: ?>
                <div class="tablewrap">
                    <table>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><b><?= htmlspecialchars($u['name']) ?></b></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= adminStatusBadge($u['status']) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <form method="post" style="margin:0">
                                        <input type="hidden" name="do" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                        <button class="toggle" type="submit"><?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>