<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> &middot; <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=4">
</head>
<body>
    <header class="site-header">
        <div class="container">
           
             <a href="index.php" class="logo brand-lockup"
             aria-label="Comcares Innovation Services Private Limited, home">
               <img
                src="images/logo.jpeg"
                alt="COMCARES Innovation Services Pvt. Ltd."
                class="brand-logo"
               >
           </a>

            <button class="nav-toggle" id="navToggle" type="button" aria-label="Toggle menu" aria-expanded="false"><span></span><span></span></button>
            <nav id="mainNav" aria-label="Primary navigation">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="services.php">Services</a>
                <a href="products.php">Products</a>
                <a href="contact.php">Contact</a>
            </nav>
            <div class="auth-area">
                <?php if (!empty($_SESSION['user_name'])): ?>
                    <a href="logout.php" class="btn btn-auth btn-logout">Hi, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?> &middot; Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-auth">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="container">
