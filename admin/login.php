<?php
require_once __DIR__ . '/admin-auth.php';

// Already logged in? go to the dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $result = adminLogin($email, $password);
        if ($result['ok']) {
            header('Location: dashboard.php');
            exit;
        }
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login &middot; <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .admin-login-body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(800px 400px at 80% -10%, rgba(163,193,75,.12), transparent 60%),
                radial-gradient(600px 350px at -5% 40%, rgba(46,88,180,.08), transparent 60%),
                #0b0e0a;
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            padding: 1.5rem;
            color: #f2f4ec;
        }

        .admin-login-card {
            width: min(420px, 100%);
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 22px;
            padding: 2.6rem 2.3rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 30px 80px rgba(0,0,0,.5);
            animation: card-in .55s cubic-bezier(.2,.75,.2,1) both;
        }
        @keyframes card-in { from { opacity: 0; transform: translateY(26px) scale(.98); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .admin-login-card { animation: none; } }

        .admin-login-card h1 {
            color: #f2f4ec;
            font-size: 1.6rem;
            margin: 0 0 .35rem;
        }
        .admin-login-card h1 em { font-family: Georgia, serif; font-weight: 400; color: #a3c14b; }

        .admin-login-card p.sub {
            color: #a8b096;
            font-size: .9rem;
            margin: 0 0 1.8rem;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .62rem;
            letter-spacing: .2em;
            color: #a3c14b;
            background: rgba(163,193,75,.12);
            border: 1px solid rgba(163,193,75,.3);
            padding: .35rem .75rem;
            border-radius: 999px;
            margin-bottom: 1.3rem;
        }
        .admin-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #a3c14b; animation: b-pulse 2.4s ease-in-out infinite; }
        @keyframes b-pulse { 0%,100% { opacity: .5; } 50% { opacity: 1; } }

        .admin-login-card label {
            display: block;
            color: #d5dac7;
            font-size: .8rem;
            margin: 0 0 .35rem;
        }

        .admin-login-card input {
            width: 100%;
            box-sizing: border-box;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 12px;
            color: #f2f4ec;
            font: inherit;
            padding: .8rem .95rem;
            margin-bottom: 1.1rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .admin-login-card input:focus {
            outline: none;
            border-color: #a3c14b;
            box-shadow: 0 0 0 3px rgba(163,193,75,.18);
        }

        .admin-login-card button {
            width: 100%;
            border: 0;
            cursor: pointer;
            background: linear-gradient(135deg, #a3c14b, #5f7524);
            color: #10140c;
            font: inherit;
            font-weight: 800;
            padding: .85rem;
            border-radius: 12px;
            transition: transform .18s, filter .18s, box-shadow .25s;
        }

        .admin-login-card button:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
            box-shadow: 0 10px 26px rgba(163,193,75,.28);
        }
        .admin-login-card button:focus-visible { outline: 3px solid #ffd76a; outline-offset: 3px; }

        .admin-error {
            background: rgba(224, 122, 106, .12);
            border: 1px solid rgba(224, 122, 106, .4);
            color: #f0b6b6;
            font-size: .85rem;
            padding: .7rem .9rem;
            border-radius: 12px;
            margin-bottom: 1.2rem;
            animation: shake .38s ease both;
        }
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 55% { transform: translateX(5px); } 80% { transform: translateX(-2px); } }

        .admin-back {
            display: block;
            text-align: center;
            margin-top: 1.4rem;
            color: #8d947c;
            font-size: .8rem;
            text-decoration: none;
        }

        .admin-back:hover {
            color: #c6d38a;
        }
    </style>
</head>

<body class="admin-login-body">
    <div class="admin-login-card">
        <span class="admin-badge">ADMIN AREA</span>
        <h1>Admin <em>Sign In</em></h1>
        <p class="sub">Only administrators can access this area.</p>

        <?php if ($error): ?>
            <div class="admin-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>
            <label for="email">Admin email</label>
            <input type="email" id="email" name="email" placeholder="admin@comcares.com" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>

            <button type="submit">Sign in to Admin Panel →</button>
        </form>

        <a class="admin-back" href="../index.php">← Back to website</a>
    </div>
</body>

</html>