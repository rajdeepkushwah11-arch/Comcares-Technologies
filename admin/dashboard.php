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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0c12;
            --glass: rgba(255, 255, 255, .055);
            --line: rgba(255, 255, 255, .09);
            --line-hi: rgba(255, 255, 255, .18);
            --a1: #5b8cff;
            --a2: #8b5cf6;
            --a3: #34d399;
            --amber: #fbbf24;
            --ink: #f4f6fb;
            --dim: #9aa3b8;
            --faint: #6b7284;
            --ease: cubic-bezier(.22, .8, .24, 1);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body.dash {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        ::selection {
            background: var(--a1);
            color: #fff;
        }

        /* ================= atmosphere ================= */
        .fx {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .fx .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: .5;
        }

        .fx .b1 {
            width: 52vw;
            height: 52vw;
            top: -22vw;
            right: -14vw;
            background: radial-gradient(circle at 40% 40%, rgba(91, 140, 255, .5), rgba(91, 140, 255, .12) 55%, transparent 75%);
            animation: drift1 28s ease-in-out infinite alternate;
        }

        .fx .b2 {
            width: 42vw;
            height: 42vw;
            bottom: -20vw;
            left: -12vw;
            background: radial-gradient(circle at 55% 45%, rgba(139, 92, 246, .42), rgba(139, 92, 246, .1) 55%, transparent 75%);
            animation: drift2 34s ease-in-out infinite alternate;
        }

        .fx .b3 {
            width: 24vw;
            height: 24vw;
            top: 38%;
            left: 30%;
            background: radial-gradient(circle, rgba(52, 211, 153, .16), transparent 70%);
            animation: drift3 24s ease-in-out infinite alternate;
        }

        @keyframes drift1 {
            to {
                transform: translate(-7vw, 6vw) scale(1.16);
            }
        }

        @keyframes drift2 {
            to {
                transform: translate(6vw, -5vw) scale(1.12);
            }
        }

        @keyframes drift3 {
            to {
                transform: translate(-6vw, -7vw);
                opacity: .8;
            }
        }

        .fx .grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .03) 1px, transparent 1px);
            background-size: 46px 46px;
            mask-image: radial-gradient(ellipse 100% 65% at 50% 0%, #000 25%, transparent 78%);
            -webkit-mask-image: radial-gradient(ellipse 100% 65% at 50% 0%, #000 25%, transparent 78%);
        }

        .fx .noise {
            position: absolute;
            inset: 0;
            opacity: .35;
            mix-blend-mode: overlay;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='120' height='120' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        .adm-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, var(--a1), var(--a2), var(--a3));
            box-shadow: 0 0 14px rgba(91, 140, 255, .7);
            z-index: 200;
            border-radius: 0 3px 3px 0;
            transition: width .1s linear;
        }

        /* ================= layout ================= */
        .shell {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100vh;
        }

        /* ---------- sidebar ---------- */
        .side {
            position: sticky;
            top: 0;
            align-self: flex-start;
            width: 248px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 1.6rem 1.1rem;
            background: rgba(13, 16, 24, .72);
            border-right: 1px solid var(--line);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            animation: side-in .7s var(--ease) both;
        }

        @keyframes side-in {
            from {
                opacity: 0;
                transform: translateX(-24px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .side .logo {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: 0 .4rem 1.6rem;
        }

        .side .logo .mark {
            width: 40px;
            height: 40px;
            border-radius: 13px;
            flex: none;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--a1), var(--a2));
            color: #fff;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 8px 24px rgba(91, 140, 255, .4), inset 0 1px 0 rgba(255, 255, 255, .4);
            position: relative;
            overflow: hidden;
            animation: mark-glow 3.4s ease-in-out infinite;
        }

        .side .logo .mark::after {
            content: '';
            position: absolute;
            top: -60%;
            left: -60%;
            width: 55%;
            height: 220%;
            transform: rotate(25deg);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .55), transparent);
            animation: sheen 3.8s ease-in-out infinite;
        }

        @keyframes mark-glow {

            0%,
            100% {
                box-shadow: 0 8px 24px rgba(91, 140, 255, .35);
            }

            50% {
                box-shadow: 0 8px 34px rgba(139, 92, 246, .55);
            }
        }

        @keyframes sheen {

            0%,
            58% {
                left: -60%;
            }

            88%,
            100% {
                left: 130%;
            }
        }

        .side .logo b {
            display: block;
            font-size: .95rem;
            letter-spacing: -.01em;
        }

        .side .logo small {
            display: block;
            color: var(--a1);
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .56rem;
            letter-spacing: .26em;
            font-weight: 400;
            margin-top: .15rem;
        }

        .side .nav-label {
            color: var(--faint);
            font-size: .62rem;
            letter-spacing: .22em;
            text-transform: uppercase;
            padding: .4rem .6rem;
            margin-bottom: .5rem;
        }

        .side nav {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .side .nav-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--dim);
            text-decoration: none;
            font-size: .87rem;
            font-weight: 600;
            padding: .68rem .8rem;
            border-radius: 12px;
            border: 1px solid transparent;
            position: relative;
            transition: background .25s, color .25s, border-color .25s, transform .25s;
        }

        .side .nav-item .ic {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            flex: none;
            display: grid;
            place-items: center;
            font-size: .85rem;
            background: rgba(255, 255, 255, .05);
            border: 1px solid var(--line);
            transition: border-color .25s, transform .35s var(--ease);
        }

        .side .nav-item:hover {
            color: var(--ink);
            background: rgba(255, 255, 255, .05);
            transform: translateX(3px);
        }

        .side .nav-item:hover .ic {
            border-color: rgba(91, 140, 255, .5);
            transform: scale(1.06);
        }

        .side .nav-item.active {
            color: #fff;
            background: linear-gradient(120deg, rgba(91, 140, 255, .2), rgba(139, 92, 246, .14));
            border-color: rgba(91, 140, 255, .35);
            box-shadow: 0 6px 22px rgba(91, 140, 255, .16), inset 0 1px 0 rgba(255, 255, 255, .12);
        }

        .side .nav-item.active::before {
            content: '';
            position: absolute;
            left: -1.1rem;
            top: 22%;
            bottom: 22%;
            width: 3px;
            border-radius: 3px;
            background: linear-gradient(180deg, var(--a1), var(--a2));
            box-shadow: 0 0 12px rgba(91, 140, 255, .9);
        }

        .side .nav-item.active .ic {
            background: linear-gradient(135deg, var(--a1), var(--a2));
            border-color: transparent;
            color: #fff;
        }

        .side .nav-item .count {
            margin-left: auto;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .68rem;
            color: var(--a1);
            background: rgba(91, 140, 255, .14);
            border: 1px solid rgba(91, 140, 255, .35);
            padding: .12rem .5rem;
            border-radius: 999px;
        }

        .side .spacer {
            flex: 1;
        }

        .side .me {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .8rem;
            border-radius: 14px;
            background: var(--glass);
            border: 1px solid var(--line);
            margin-bottom: .7rem;
        }

        .side .me .ava {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            flex: none;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: .8rem;
            background: linear-gradient(135deg, var(--a1), var(--a2));
            color: #fff;
            box-shadow: 0 4px 14px rgba(91, 140, 255, .35);
        }

        .side .me b {
            display: block;
            font-size: .8rem;
        }

        .side .me span {
            color: var(--faint);
            font-size: .68rem;
        }

        .side .out {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            color: var(--dim);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 700;
            padding: .68rem;
            border-radius: 12px;
            border: 1px solid var(--line);
            transition: color .25s, border-color .25s, background .25s, transform .2s;
        }

        .side .out:hover {
            color: #fff;
            border-color: rgba(224, 122, 106, .5);
            background: rgba(224, 122, 106, .12);
            transform: translateY(-2px);
        }

        .side .out:focus-visible {
            outline: 2px solid var(--amber);
            outline-offset: 2px;
        }

        /* ---------- main ---------- */
        .main {
            flex: 1;
            min-width: 0;
            padding: 0 2.2rem 5rem;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
            margin-bottom: 2rem;
            background: linear-gradient(180deg, var(--bg) 78%, transparent);
        }

        .topbar .crumb {
            display: flex;
            align-items: center;
            gap: .55rem;
            color: var(--faint);
            font-size: .78rem;
        }

        .topbar .crumb b {
            color: var(--ink);
            font-weight: 700;
        }

        .topbar .crumb .sep {
            opacity: .4;
        }

        .live-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .72rem;
            color: var(--dim);
            background: var(--glass);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: .45rem .9rem;
            backdrop-filter: blur(10px);
            white-space: nowrap;
        }

        .live-chip .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--a3);
            box-shadow: 0 0 10px rgba(52, 211, 153, .9);
            animation: pulse 2.2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: .4;
                transform: scale(.85);
            }

            50% {
                opacity: 1;
                transform: scale(1.15);
            }
        }

        /* hero heading */
        .hero {
            margin-bottom: 2.3rem;
        }

        .hero .kicker {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            font-family: 'DM Mono', Consolas, monospace;
            color: var(--a1);
            font-size: .66rem;
            letter-spacing: .24em;
            text-transform: uppercase;
            margin-bottom: .8rem;
            padding: .4rem .85rem;
            border-radius: 999px;
            background: rgba(91, 140, 255, .1);
            border: 1px solid rgba(91, 140, 255, .3);
            animation: rise .6s .05s var(--ease) both;
        }

        .hero .kicker .d {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--a1);
            box-shadow: 0 0 8px rgba(91, 140, 255, .9);
            animation: pulse 2.4s ease-in-out infinite;
        }

        .hero h1 {
            margin: 0 0 .55rem;
            font-size: clamp(1.9rem, 4.4vw, 3rem);
            font-weight: 800;
            letter-spacing: -.03em;
            line-height: 1.08;
            animation: rise .65s .12s var(--ease) both;
        }

        .hero h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
            font-style: italic;
            background: linear-gradient(100deg, var(--a1) 5%, var(--a2) 50%, var(--a3) 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: grad-slide 6s ease-in-out infinite alternate;
        }

        @keyframes grad-slide {
            to {
                background-position: 100% 0;
            }
        }

        .hero .lede {
            margin: 0;
            color: var(--dim);
            font-size: .95rem;
            max-width: 560px;
            line-height: 1.7;
            animation: rise .65s .2s var(--ease) both;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* stat cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.1rem;
            margin-bottom: 2.6rem;
        }

        .stat {
            position: relative;
            overflow: hidden;
            cursor: default;
            background: linear-gradient(160deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .025));
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 1.4rem 1.5rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .1), 0 18px 44px rgba(0, 0, 0, .35);
            transition: transform .35s var(--ease), border-color .35s, box-shadow .35s;
            animation: rise .6s var(--ease) both;
        }

        .stat:nth-child(1) {
            animation-delay: .22s;
        }

        .stat:nth-child(2) {
            animation-delay: .3s;
        }

        .stat:nth-child(3) {
            animation-delay: .38s;
        }

        .stat::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: radial-gradient(240px 180px at var(--mx, 50%) var(--my, -30%), rgba(91, 140, 255, .18), transparent 70%);
            opacity: 0;
            transition: opacity .4s;
            pointer-events: none;
        }

        .stat::after {
            content: '';
            position: absolute;
            top: 0;
            left: 12%;
            right: 12%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--c, var(--a1)), transparent);
            opacity: .55;
        }

        .stat:hover {
            transform: translateY(-6px);
            border-color: color-mix(in srgb, var(--c, var(--a1)) 45%, transparent);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .1), 0 26px 60px rgba(0, 0, 0, .45);
        }

        .stat:hover::before {
            opacity: 1;
        }

        .stat .head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat .ic {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            background: color-mix(in srgb, var(--c, var(--a1)) 16%, transparent);
            border: 1px solid color-mix(in srgb, var(--c, var(--a1)) 40%, transparent);
            color: var(--c, var(--a1));
            transition: transform .4s var(--ease);
        }

        .stat:hover .ic {
            transform: scale(1.14) rotate(-8deg);
        }

        .stat b.num {
            display: block;
            font-size: 2.4rem;
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1;
            margin-bottom: .45rem;
            font-variant-numeric: tabular-nums;
            background: linear-gradient(180deg, #fff 30%, color-mix(in srgb, var(--c, var(--a1)) 70%, #fff));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat .lbl {
            color: var(--faint);
            font-size: .78rem;
            letter-spacing: .04em;
        }

        .stat .spark {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 26px;
        }

        .stat .spark i {
            width: 5px;
            border-radius: 3px;
            background: linear-gradient(180deg, var(--c, var(--a1)), transparent);
            opacity: .7;
            transform-origin: bottom;
            animation: grow .8s var(--ease) both;
        }

        @keyframes grow {
            from {
                transform: scaleY(0);
            }

            to {
                transform: scaleY(1);
            }
        }

        /* ring stat */
        .stat .ring {
            position: relative;
            width: 46px;
            height: 46px;
        }

        .stat .ring svg {
            transform: rotate(-90deg);
        }

        .stat .ring circle {
            fill: none;
            stroke-width: 4;
        }

        .stat .ring .track {
            stroke: rgba(255, 255, 255, .09);
        }

        .stat .ring .fill {
            stroke: var(--c, var(--a1));
            stroke-linecap: round;
            stroke-dasharray: 125.6;
            stroke-dashoffset: 125.6;
            animation: ring 1.6s .5s var(--ease) forwards;
            filter: drop-shadow(0 0 5px color-mix(in srgb, var(--c, var(--a1)) 70%, transparent));
        }

        @keyframes ring {
            to {
                stroke-dashoffset: var(--dash, 30);
            }
        }

        .stat .ring b {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            font-size: .62rem;
            font-weight: 800;
            color: var(--ink);
        }

        /* activity chart */
        .panel {
            background: linear-gradient(165deg, rgba(255, 255, 255, .07), rgba(255, 255, 255, .02));
            border: 1px solid var(--line);
            border-radius: 24px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 20px 50px rgba(0, 0, 0, .32);
            padding: 1.6rem 1.7rem;
            margin-bottom: 2.4rem;
            animation: rise .6s .44s var(--ease) both;
        }

        .panel-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.3rem;
            flex-wrap: wrap;
        }

        .panel-head h2 {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .panel-head h2 i {
            font-style: normal;
            font-size: .8rem;
            color: var(--a1);
            width: 28px;
            height: 28px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            background: rgba(91, 140, 255, .14);
            border: 1px solid rgba(91, 140, 255, .35);
        }

        .panel-head .meta {
            color: var(--faint);
            font-size: .74rem;
            font-family: 'DM Mono', Consolas, monospace;
        }

        .chart {
            display: flex;
            align-items: flex-end;
            gap: .7rem;
            height: 130px;
        }

        .chart .col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            height: 100%;
            justify-content: flex-end;
        }

        .chart .bar {
            width: 100%;
            max-width: 42px;
            border-radius: 8px 8px 4px;
            min-height: 4px;
            background: linear-gradient(180deg, var(--a1), rgba(139, 92, 246, .35));
            box-shadow: 0 0 16px rgba(91, 140, 255, .25), inset 0 1px 0 rgba(255, 255, 255, .35);
            position: relative;
            transform-origin: bottom;
            animation: grow 1s .55s var(--ease) both;
            transition: filter .25s;
        }

        .chart .bar.is-today {
            background: linear-gradient(180deg, var(--a3), rgba(52, 211, 153, .3));
            box-shadow: 0 0 18px rgba(52, 211, 153, .35), inset 0 1px 0 rgba(255, 255, 255, .4);
        }

        .chart .bar:hover {
            filter: brightness(1.25);
        }

        .chart .bar .tip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            background: rgba(15, 18, 28, .95);
            border: 1px solid var(--line-hi);
            color: var(--ink);
            font-size: .68rem;
            font-weight: 700;
            padding: .25rem .55rem;
            border-radius: 8px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s, transform .2s;
            font-family: 'DM Mono', Consolas, monospace;
            z-index: 5;
        }

        .chart .bar:hover .tip {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .chart .cap {
            color: var(--faint);
            font-size: .62rem;
            font-family: 'DM Mono', Consolas, monospace;
            letter-spacing: .06em;
        }

        /* inbox */
        .inbox-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.15rem;
            max-width: 1100px;
            animation: rise .6s .5s var(--ease) both;
        }

        .inbox-label h2 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: 1.02rem;
            font-weight: 800;
        }

        .inbox-label h2 i {
            font-style: normal;
            font-size: .8rem;
            color: var(--a2);
            width: 28px;
            height: 28px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            background: rgba(139, 92, 246, .14);
            border: 1px solid rgba(139, 92, 246, .35);
        }

        .inbox-label .badge {
            color: var(--dim);
            font-size: .74rem;
            font-family: 'DM Mono', Consolas, monospace;
            background: var(--glass);
            border: 1px solid var(--line);
            padding: .35rem .8rem;
            border-radius: 999px;
        }

        .inbox-label .badge b {
            color: var(--a1);
        }

        .msgs {
            display: flex;
            flex-direction: column;
            position: relative;
            max-width: 1100px;
        }

        .msgs::before {
            content: '';
            position: absolute;
            left: 26px;
            top: 20px;
            bottom: 20px;
            width: 1px;
            background: linear-gradient(180deg, rgba(91, 140, 255, .45), rgba(139, 92, 246, .25), transparent);
        }

        .msg {
            position: relative;
            margin-left: 54px;
            background: linear-gradient(160deg, rgba(255, 255, 255, .07), rgba(255, 255, 255, .022));
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 1.25rem 1.45rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .07), 0 12px 34px rgba(0, 0, 0, .26);
            transition: transform .3s var(--ease), border-color .3s, box-shadow .3s;
            animation: rise .55s var(--ease) both;
            animation-delay: calc(.55s + var(--i, 0) * .06s);
        }

        .msg::before {
            content: '';
            position: absolute;
            left: -34px;
            top: 1.7rem;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--faint);
            border: 2px solid var(--bg);
            transition: background .3s, box-shadow .3s;
        }

        .msg:hover {
            transform: translateY(-3px);
            border-color: rgba(91, 140, 255, .4);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 20px 48px rgba(0, 0, 0, .4);
        }

        .msg:hover::before {
            background: var(--a1);
            box-shadow: 0 0 12px rgba(91, 140, 255, .9);
        }

        .msg.is-new {
            border-color: rgba(91, 140, 255, .4);
        }

        .msg.is-new::before {
            background: var(--a1);
            box-shadow: 0 0 14px rgba(91, 140, 255, 1);
            animation: pulse 2s ease-in-out infinite;
        }

        .msg.is-new .tag {
            display: inline-flex;
            align-items: center;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .6rem;
            letter-spacing: .16em;
            color: var(--a3);
            background: rgba(52, 211, 153, .12);
            border: 1px solid rgba(52, 211, 153, .4);
            padding: .22rem .6rem;
            border-radius: 999px;
            margin-left: .6rem;
            vertical-align: 2px;
        }

        .msg-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: .55rem;
        }

        .msg-head .who {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-width: 0;
        }

        .msg-head .ava {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            flex: none;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: .85rem;
            color: #fff;
            background: linear-gradient(135deg, var(--a1), var(--a2));
            box-shadow: 0 5px 16px rgba(91, 140, 255, .3), inset 0 1px 0 rgba(255, 255, 255, .4);
            transition: transform .35s var(--ease);
        }

        .msg:hover .ava {
            transform: scale(1.08) rotate(-5deg);
        }

        .msg-head .who b {
            font-size: .95rem;
        }

        .msg-head .who .em {
            color: var(--faint);
            font-weight: 400;
            font-size: .8rem;
        }

        .msg-head time {
            color: var(--faint);
            font-size: .7rem;
            font-family: 'DM Mono', Consolas, monospace;
            background: rgba(255, 255, 255, .05);
            border: 1px solid var(--line);
            padding: .28rem .65rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        .msg p.msg-body {
            color: var(--dim);
            font-size: .9rem;
            line-height: 1.7;
            margin: 0 0 .8rem;
            white-space: pre-wrap;
        }

        .msg a.mail {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            color: var(--a1);
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            padding: .45rem .95rem;
            border-radius: 999px;
            background: rgba(91, 140, 255, .1);
            border: 1px solid rgba(91, 140, 255, .35);
            transition: background .25s, transform .2s, box-shadow .25s, color .25s;
        }

        .msg a.mail:hover {
            background: linear-gradient(120deg, var(--a1), var(--a2));
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(91, 140, 255, .35);
        }

        .msg a.mail:focus-visible {
            outline: 2px solid var(--amber);
            outline-offset: 2px;
        }

        /* empty */
        .empty {
            border: 1px dashed var(--line-hi);
            border-radius: 22px;
            padding: 3.4rem 2rem;
            text-align: center;
            color: var(--faint);
            background: rgba(255, 255, 255, .03);
            animation: rise .6s .5s var(--ease) both;
        }

        .empty .big {
            font-size: 2.4rem;
            display: inline-grid;
            place-items: center;
            width: 76px;
            height: 76px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(91, 140, 255, .14), rgba(139, 92, 246, .1));
            border: 1px solid rgba(91, 140, 255, .3);
            margin-bottom: 1rem;
            filter: drop-shadow(0 0 22px rgba(91, 140, 255, .4));
            animation: float-y 3.4s ease-in-out infinite;
        }

        @keyframes float-y {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-9px);
            }
        }

        /* footer */
        .foot {
            margin-top: 3rem;
            padding-top: 1.4rem;
            border-top: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            color: var(--faint);
            font-size: .74rem;
            flex-wrap: wrap;
            animation: rise .6s .6s var(--ease) both;
        }

        .foot .mono {
            font-family: 'DM Mono', Consolas, monospace;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .62rem;
        }

        .foot .grad {
            background: linear-gradient(90deg, var(--a1), var(--a2), var(--a3));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }
        }

        @media (max-width: 980px) {
            .side {
                display: none;
            }

            .main {
                padding: 0 1.2rem 4rem;
            }

            .msgs::before,
            .msg::before {
                display: none;
            }

            .msg {
                margin-left: 0;
            }
        }

        @media (max-width: 640px) {
            .topbar .crumb {
                display: none;
            }

            .panel,
            .msg {
                padding: 1.1rem 1rem;
            }

            .chart {
                gap: .35rem;
                height: 100px;
            }

            .chart .col:nth-child(even) .cap {
                visibility: hidden;
            }
        }
    </style>
</head>

<body class="dash">
    <div class="adm-progress" id="admProgress" aria-hidden="true"></div>

    <div class="fx" aria-hidden="true">
        <div class="blob b1"></div>
        <div class="blob b2"></div>
        <div class="blob b3"></div>
        <div class="grid"></div>
        <div class="noise"></div>
    </div>

    <div class="shell">
        <aside class="side">
            <div class="logo">
                <span class="mark" aria-hidden="true">C</span>
                <span><b>Comcares</b><small>CONTROL PANEL</small></span>
            </div>
            <p class="nav-label">Menu</p>
            <nav>
                <a class="nav-item active" href="dashboard.php" aria-current="page">
                    <span class="ic" aria-hidden="true">&#9636;</span> Dashboard
                </a>
                <a class="nav-item" href="#inbox">
                    <span class="ic" aria-hidden="true">&#9993;</span> Inbox
                    <span class="count"><?= $total ?></span>
                </a>
            </nav>
            <div class="spacer"></div>
            <div class="me">
                <span class="ava" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['admin_name'], 0, 1))) ?></span>
                <span>
                    <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b>
                    <span>Signed in</span>
                </span>
            </div>
            <a class="out" href="dashboard.php?action=logout"><span aria-hidden="true">&#10140;</span> Logout</a>
        </aside>

        <main class="main">
            <div class="topbar">
                <div class="crumb">
                    <b>Admin</b> <span class="sep">&#8250;</span> Dashboard <span class="sep">&#8250;</span> Contact Enquiries
                </div>
                <span class="live-chip"><span class="dot"></span><span id="clock" aria-label="Current time">&mdash;:&mdash;</span></span>
            </div>

            <div class="hero">
                <p class="kicker"><span class="d"></span> Live overview</p>
                <h1>Recent contact <em>enquiries.</em></h1>
                <p class="lede">Everyone who has recently contacted you through the website's contact form &mdash; newest first.</p>
            </div>

            <div class="stats">
                <div class="stat" style="--c: var(--a1)">
                    <div class="head">
                        <span class="ic" aria-hidden="true">&#9993;</span>
                        <div class="spark" aria-hidden="true">
                            <i style="height:35%; animation-delay:.5s"></i><i style="height:60%; animation-delay:.58s"></i><i style="height:45%; animation-delay:.66s"></i><i style="height:80%; animation-delay:.74s"></i><i style="height:100%; animation-delay:.82s"></i>
                        </div>
                    </div>
                    <b class="num"><?= $total ?></b>
                    <span class="lbl">Total messages received</span>
                </div>
                <div class="stat" style="--c: var(--a3)">
                    <div class="head">
                        <span class="ic" aria-hidden="true">&#9788;</span>
                        <div class="ring" aria-hidden="true">
                            <svg width="46" height="46" viewBox="0 0 46 46">
                                <circle class="track" cx="23" cy="23" r="20"></circle>
                                <circle class="fill" cx="23" cy="23" r="20" style="--dash: <?= $total > 0 ? max(125.6 - (125.6 * $today / max($total, 1)), 110) : 125.6 ?>"></circle>
                            </svg>
                            <b><?= $today ?>/<?= $total ?></b>
                        </div>
                    </div>
                    <b class="num"><?= $today ?></b>
                    <span class="lbl">Received today</span>
                </div>
                <div class="stat" style="--c: var(--a2)">
                    <div class="head">
                        <span class="ic" aria-hidden="true">&#9201;</span>
                    </div>
                    <b class="num" style="font-size:1.5rem; padding-top:.5rem;"><?= $latest ? htmlspecialchars($latest['name']) : '&mdash;' ?></b>
                    <span class="lbl"><?= $latest ? 'Most recent &middot; ' . timeAgo($latest['created_at']) : 'No messages yet' ?></span>
                </div>
            </div>

            <?php
            // last-7-days activity for the chart (visualises the same inbox data)
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $c = 0;
                foreach ($messages as $m) {
                    if (date('Y-m-d', strtotime($m['created_at'])) === $d) $c++;
                }
                $days[] = ['label' => date('D', strtotime($d)), 'count' => $c, 'today' => $i === 0];
            }
            $peak = max(1, max(array_column($days, 'count')));
            ?>
            <section class="panel">
                <div class="panel-head">
                    <h2><i aria-hidden="true">&#9636;</i> Activity &mdash; last 7 days</h2>
                    <span class="meta">peak <?= $peak ?>/day</span>
                </div>
                <div class="chart" role="img" aria-label="Messages received per day over the last seven days">
                    <?php foreach ($days as $d): ?>
                        <div class="col">
                            <div class="bar <?= $d['today'] ? 'is-today' : '' ?>" style="height: <?= max(4, round($d['count'] / $peak * 100)) ?>%; animation-delay: <?= number_format(0.55 + 0.06 * 0, 2) ?>s;">
                                <span class="tip"><?= $d['count'] ?> message<?= $d['count'] === 1 ? '' : 's' ?></span>
                            </div>
                            <span class="cap"><?= $d['label'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="inbox-label" id="inbox">
                <h2><i aria-hidden="true">&#9993;</i> Inbox</h2>
                <span class="badge"><b><?= $total ?></b> message<?= $total === 1 ? '' : 's' ?> &middot; newest first</span>
            </div>

            <?php if (!$messages): ?>
                <div class="empty">
                    <span class="big" aria-hidden="true">&#128233;</span><br>
                    No contact messages yet. When someone fills in the contact form, they'll appear here.
                </div>
            <?php else: ?>
                <div class="msgs">
                    <?php foreach ($messages as $i => $msg): ?>
                        <article class="msg <?= $i === 0 ? 'is-new' : '' ?>" style="--i: <?= min($i, 8) ?>">
                            <div class="msg-head">
                                <div class="who">
                                    <span class="ava" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($msg['name'], 0, 1))) ?></span>
                                    <span>
                                        <b><?= htmlspecialchars($msg['name']) ?></b><?= $i === 0 ? '<span class="tag">NEW</span>' : '' ?><br>
                                        <span class="em">&lt;<?= htmlspecialchars($msg['email']) ?>&gt;</span>
                                    </span>
                                </div>
                                <time title="<?= htmlspecialchars($msg['created_at']) ?>"><?= timeAgo($msg['created_at']) ?></time>
                            </div>
                            <p class="msg-body"><?= htmlspecialchars($msg['message']) ?></p>
                            <a class="mail" href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: your enquiry to Comcares">&#9993; Reply to this person</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="foot">
                <span class="mono">Comcares Admin &middot; Control Panel</span>
                <span>Designed for <span class="grad">speed &amp; clarity</span>
            </div>
        </main>
    </div>

    <script>
        (function() {
            "use strict";
            var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
            var fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

            /* scroll progress */
            var bar = document.getElementById("admProgress");

            function onScroll() {
                var h = document.documentElement;
                var max = h.scrollHeight - h.clientHeight;
                if (bar) bar.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + "%";
            }
            window.addEventListener("scroll", onScroll, {
                passive: true
            });
            onScroll();

            /* live clock chip */
            var clock = document.getElementById("clock");

            function tickClock() {
                var d = new Date();
                clock.textContent = d.toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit"
                });
            }
            tickClock();
            setInterval(tickClock, 15000);

            /* count-up numbers */
            function easeOut(p) {
                return 1 - Math.pow(1 - p, 3);
            }
            document.querySelectorAll(".stat b.num").forEach(function(el) {
                var goal = parseInt(el.textContent, 10);
                if (isNaN(goal) || goal === 0 || reduce) return;
                var start = null;

                function tick(now) {
                    if (start === null) start = now;
                    var p = Math.min((now - start) / 1300, 1);
                    el.textContent = Math.round(easeOut(p) * goal);
                    if (p < 1) requestAnimationFrame(tick);
                }
                el.textContent = "0";
                requestAnimationFrame(tick);
            });

            /* cursor spotlight on cards */
            if (fine && !reduce) {
                var raf = 0,
                    ev = null;
                document.addEventListener("pointermove", function(e) {
                    ev = e;
                    if (raf) return;
                    raf = requestAnimationFrame(function() {
                        raf = 0;
                        document.querySelectorAll(".stat, .panel").forEach(function(c) {
                            var r = c.getBoundingClientRect();
                            if (ev.clientX < r.left - 80 || ev.clientX > r.right + 80 ||
                                ev.clientY < r.top - 80 || ev.clientY > r.bottom + 80) return;
                            c.style.setProperty("--mx", (ev.clientX - r.left) + "px");
                            c.style.setProperty("--my", (ev.clientY - r.top) + "px");
                        });
                    });
                }, {
                    passive: true
                });

                /* aurora parallax */
                var b1 = document.querySelector(".fx .b1");
                var b2 = document.querySelector(".fx .b2");
                window.addEventListener("scroll", function() {
                    var y = window.scrollY;
                    if (b1) b1.style.translate = "0 " + y * 0.05 + "px";
                    if (b2) b2.style.translate = "0 " + y * -0.08 + "px";
                }, {
                    passive: true
                });
            }
        })();
    </script>
</body>

</html>