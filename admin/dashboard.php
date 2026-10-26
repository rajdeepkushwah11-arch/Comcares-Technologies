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

// Messages are intentionally read-only here. They remain stored until an admin
// explicitly removes them through a future, dedicated deletion action.
$filterDate = trim($_GET['date'] ?? '');
$filterMonth = trim($_GET['month'] ?? '');
$filterDay = trim($_GET['day'] ?? '');
$weekdays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
$where = [];
$params = [];

if ($filterDate !== '') {
    $date = DateTime::createFromFormat('Y-m-d', $filterDate);
    if ($date && $date->format('Y-m-d') === $filterDate) {
        $where[] = 'DATE(created_at) = ?';
        $params[] = $filterDate;
    } else {
        $filterDate = '';
    }
}

if ($filterMonth !== '') {
    $month = DateTime::createFromFormat('!Y-m', $filterMonth);
    if ($month && $month->format('Y-m') === $filterMonth) {
        $where[] = 'created_at >= ? AND created_at < ?';
        $params[] = $month->format('Y-m-01 00:00:00');
        $params[] = $month->modify('+1 month')->format('Y-m-01 00:00:00');
    } else {
        $filterMonth = '';
    }
}

if (!in_array($filterDay, $weekdays, true)) {
    $filterDay = '';
} elseif ($filterDay !== '') {
    $where[] = 'WEEKDAY(created_at) = ?';
    $params[] = array_search($filterDay, $weekdays, true);
}

$sql = 'SELECT * FROM contact_messages';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC, id DESC';
$statement = db()->prepare($sql);
$statement->execute($params);
$messages = $statement->fetchAll();

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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f8fc;
            --paper: #ffffff;
            --ink: #172033;
            --soft: #596579;
            --faint: #8491a7;
            --line: #e7ecf3;
            --line-strong: #d4dce8;
            --gold: #2563eb;
            --gold-soft: #eaf1ff;
            --green: #0d9a6c;
            --shadow: 0 1px 2px rgba(18, 32, 55, .03), 0 10px 28px rgba(18, 32, 55, .055);
            --shadow-hover: 0 14px 30px rgba(18, 32, 55, .09);
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
            background: linear-gradient(135deg, #f9fbff 0%, var(--bg) 58%, #f2f6fc 100%);
            color: var(--ink);
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        ::selection {
            background: var(--gold);
            color: #fff;
        }

        /* ===================== atmosphere ===================== */
        .paper-grain {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: 0;
            mix-blend-mode: multiply;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)' opacity='.05'/%3E%3C/svg%3E");
        }

        .adm-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 2px;
            width: 0%;
            background: var(--gold);
            z-index: 200;
            transition: width .12s linear;
        }

        /* ===================== shell ===================== */
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
            width: 232px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 2.2rem 1.6rem 1.8rem;
            border-right: 1px solid #263650;
            background: #152238;
            color: #f7faff;
            box-shadow: 8px 0 24px rgba(15, 30, 55, .06);
            animation: fade-in .8s var(--ease) both;
        }

        .side .brand {
            display: flex;
            align-items: baseline;
            gap: .5rem;
            margin-bottom: .4rem;
        }

        .side .brand b {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -.01em;
        }

        .side .brand small {
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .55rem;
            letter-spacing: .28em;
            color: #8db8ff;
        }

        .side .hairline {
            height: 1px;
            background: rgba(255, 255, 255, .13);
            margin-bottom: 1.8rem;
            transform-origin: left;
            animation: draw-x 1s .2s var(--ease) both;
        }

        .side nav {
            display: flex;
            flex-direction: column;
            gap: .15rem;
        }

        .side nav a {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: #b9c7dc;
            text-decoration: none;
            font-size: .86rem;
            font-weight: 600;
            padding: .65rem .7rem;
            border-radius: 10px;
            position: relative;
            transition: color .25s, background .25s, transform .25s var(--ease);
        }

        .side nav a:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
            transform: translateX(2px);
        }

        .side nav a.active {
            color: #fff;
            background: #29446e;
        }

        .side nav a.active::before {
            content: '';
            position: absolute;
            left: -.7rem;
            top: 25%;
            bottom: 25%;
            width: 2px;
            background: #69a2ff;
        }

        .side nav a .cnt {
            margin-left: auto;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .66rem;
            color: #a9c8ff;
        }

        .side .spacer {
            flex: 1;
        }

        .side .who {
            padding: 1rem .2rem 0;
            border-top: 1px solid rgba(255, 255, 255, .13);
            margin-top: 1rem;
        }

        .side .who b {
            display: block;
            font-size: .84rem;
        }

        .side .who span {
            color: #9caec7;
            font-size: .7rem;
        }

        .side .out {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-top: .9rem;
            color: #d6e0ef;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 700;
            padding: .6rem .8rem;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 10px;
            transition: color .25s, border-color .25s, background .25s;
        }

        .side .out:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, .35);
            background: rgba(255, 255, 255, .08);
        }

        .side .out:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        /* ---------- main ---------- */
        .main {
            flex: 1;
            min-width: 0;
            padding: 2.4rem 3.5rem 5rem;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2.8rem;
            animation: fade-in .8s .1s var(--ease) both;
        }

        .topbar .date {
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .7rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--faint);
        }

        .live-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .7rem;
            color: var(--soft);
            border: 1px solid var(--line);
            background: var(--paper);
            border-radius: 999px;
            padding: .45rem .95rem;
        }

        .live-chip .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            animation: breathe 2.6s ease-in-out infinite;
        }

        /* ---------- hero ---------- */
        .hero {
            margin-bottom: 3rem;
        }

        .hero .eyebrow {
            display: flex;
            align-items: center;
            gap: .8rem;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .62rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1.1rem;
            animation: rise .7s .1s var(--ease) both;
        }

        .hero .eyebrow::after {
            content: '';
            width: 56px;
            height: 1px;
            background: var(--gold);
            opacity: .45;
        }

        .hero h1 {
            margin: 0 0 .6rem;
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            font-weight: 800;
            font-size: clamp(2.15rem, 4vw, 3.2rem);
            line-height: 1.15;
            letter-spacing: -.04em;
            animation: rise .7s .18s var(--ease) both;
        }

        .hero h1 em {
            font-style: normal;
            color: var(--gold);
        }

        .hero p.lede {
            margin: 0;
            color: var(--soft);
            font-size: .95rem;
            line-height: 1.7;
            max-width: 520px;
            animation: rise .7s .26s var(--ease) both;
        }

        /* ---------- stats ---------- */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
            margin-bottom: 3rem;
        }

        .stat {
            position: relative;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.5rem 1.6rem 1.4rem;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform .4s var(--ease), box-shadow .4s var(--ease), border-color .4s;
            animation: rise .7s var(--ease) both;
        }

        .stat:nth-child(1) {
            animation-delay: .3s;
        }

        .stat:nth-child(2) {
            animation-delay: .38s;
        }

        .stat:nth-child(3) {
            animation-delay: .46s;
        }

        .stat::after {
            content: '';
            position: absolute;
            left: 1.6rem;
            right: 1.6rem;
            bottom: 0;
            height: 2px;
            background: var(--c, var(--gold));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .45s var(--ease);
        }

        .stat:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            border-color: var(--line-strong);
        }

        .stat:hover::after {
            transform: scaleX(1);
        }

        .stat .lbl {
            display: flex;
            align-items: center;
            gap: .55rem;
            font-size: .72rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--faint);
            font-weight: 700;
            margin-bottom: 1.1rem;
        }

        .stat .lbl .ic {
            font-style: normal;
            color: var(--c, var(--gold));
            font-size: .9rem;
        }

        .stat b.num {
            display: block;
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            font-size: 2.55rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.02em;
            font-variant-numeric: tabular-nums;
            margin-bottom: .5rem;
        }

        .stat .sub {
            color: var(--soft);
            font-size: .78rem;
        }

        /* ---------- activity panel ---------- */
        .panel {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.8rem 1.9rem;
            box-shadow: var(--shadow);
            margin-bottom: 3rem;
            animation: rise .7s .5s var(--ease) both;
        }

        .panel-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.6rem;
        }

        .panel-head h2 {
            margin: 0;
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .panel-head .meta {
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .68rem;
            color: var(--faint);
            letter-spacing: .1em;
        }

        .chart {
            display: flex;
            align-items: flex-end;
            gap: .8rem;
            height: 180px;
            border-bottom: 1px solid var(--line);
            padding-bottom: .1rem;
        }

        .chart .bar {
            max-width: 64px;
        }

        .chart .col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            gap: .55rem;
        }

        .chart .bar {
            width: 100%;
            min-height: 4px;
            border-radius: 4px 4px 0 0;
            background: linear-gradient(180deg, #3b82f6, #93c5fd);
            transform-origin: bottom;
            animation: grow .9s .6s var(--ease) both;
            position: relative;
            transition: filter .25s;
        }

        .chart .bar.is-today {
            background: linear-gradient(180deg, #10b981, #86efac);
        }

        .chart .bar:hover {
            filter: brightness(1.12);
        }

        .chart .bar .tip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            background: var(--ink);
            color: var(--paper);
            font-size: .66rem;
            font-weight: 700;
            padding: .3rem .6rem;
            border-radius: 7px;
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

        .chart-caps {
            display: flex;
            gap: .8rem;
            margin-top: .6rem;
        }

        .chart-caps span {
            flex: 1;
            text-align: center;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .62rem;
            letter-spacing: .08em;
            color: var(--faint);
            text-transform: uppercase;
        }

        /* ---------- inbox ---------- */
        .inbox-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.6rem;
            animation: rise .7s .55s var(--ease) both;
        }

        .inbox-head h2 {
            margin: 0;
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .inbox-head .badge {
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .68rem;
            color: var(--soft);
            border: 1px solid var(--line);
            background: var(--paper);
            padding: .35rem .8rem;
            border-radius: 999px;
        }

        .msgs {
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }

        .msg {
            position: relative;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.35rem 1.55rem;
            transition: transform .35s var(--ease), box-shadow .35s var(--ease), border-color .35s;
            animation: rise .65s var(--ease) both;
            animation-delay: calc(.6s + var(--i, 0) * .07s);
        }

        .msg:hover {
            transform: translateY(-2px);
            border-color: var(--line-strong);
            box-shadow: var(--shadow-hover);
        }

        .msg.is-new {
            border-color: rgba(37, 99, 235, .42);
        }

        .msg.is-new::before {
            content: 'NEW';
            position: absolute;
            top: -9px;
            right: 1.4rem;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .55rem;
            letter-spacing: .2em;
            color: #fff;
            background: var(--gold);
            padding: .18rem .55rem;
            border-radius: 999px;
        }

        .msg-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: .65rem;
        }

        .msg-head .who {
            display: flex;
            align-items: center;
            gap: .8rem;
            min-width: 0;
        }

        .msg-head .ava {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            flex: none;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: .85rem;
            color: var(--paper);
            background: #2563eb;
            transition: transform .35s var(--ease);
        }

        .msg:hover .ava {
            transform: scale(1.06);
        }

        .msg-head .who b {
            font-size: .94rem;
        }

        .msg-head .who .em {
            color: var(--faint);
            font-size: .8rem;
            font-weight: 400;
        }

        .msg-head time {
            color: var(--faint);
            font-size: .68rem;
            font-family: 'DM Mono', Consolas, monospace;
            white-space: nowrap;
            padding-top: .35rem;
        }

        .msg p.msg-body {
            color: var(--soft);
            font-size: .9rem;
            line-height: 1.7;
            margin: 0 0 .9rem;
            white-space: pre-wrap;
        }

        .msg a.mail, .msg button.mail {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            cursor: pointer;
            color: var(--ink);
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            padding: .5rem 1.05rem;
            border: 1px solid var(--line-strong);
            background: transparent;
            border-radius: 999px;
            position: relative;
            overflow: hidden;
            transition: color .3s, border-color .3s, transform .25s var(--ease);
        }

        .msg a.mail::before, .msg button.mail::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--ink);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s var(--ease);
            z-index: 0;
        }

        .msg a.mail:hover::before, .msg button.mail:hover::before {
            transform: scaleX(1);
        }

        .msg a.mail:hover, .msg button.mail:hover {
            color: var(--paper);
            border-color: var(--ink);
            transform: translateY(-2px);
        }

        .msg a.mail span, .msg button.mail span {
            position: relative;
            z-index: 1;
        }

        .msg a.mail:focus-visible, .msg button.mail:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        /* ---------- empty ---------- */
        .empty {
            border: 1px dashed var(--line-strong);
            border-radius: 16px;
            padding: 3.6rem 2rem;
            text-align: center;
            color: var(--soft);
            animation: rise .7s .55s var(--ease) both;
        }

        .empty .big {
            display: inline-grid;
            place-items: center;
            font-size: 2rem;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            border: 1px solid var(--line-strong);
            margin-bottom: 1rem;
            animation: breathe 3.6s ease-in-out infinite;
        }

        /* ---------- footer ---------- */
        .foot {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            color: var(--faint);
            font-size: .74rem;
            animation: fade-in 1s .7s var(--ease) both;
        }

        .foot .mono {
            font-family: 'DM Mono', Consolas, monospace;
            letter-spacing: .14em;
            text-transform: uppercase;
            font-size: .6rem;
        }

        .foot .grad {
            color: var(--gold);
            font-weight: 700;
        }

        /* ===================== keyframes ===================== */
        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes grow {
            from {
                transform: scaleY(0);
            }

            to {
                transform: scaleY(1);
            }
        }

        @keyframes draw-x {
            from {
                transform: scaleX(0);
            }

            to {
                transform: scaleX(1);
            }
        }

        @keyframes breathe {

            0%,
            100% {
                opacity: .45;
                transform: scale(.9);
            }

            50% {
                opacity: 1;
                transform: scale(1.08);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }
        }

        /* ===================== responsive ===================== */
        @media (max-width: 900px) {
            .shell {
                flex-direction: column;
            }

            .side {
                position: static;
                width: 100%;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
                padding: 1.4rem 1.4rem 1.2rem;
            }

            .side .spacer,
            .side .who {
                display: none;
            }

            .side nav {
                flex-direction: row;
                margin-top: .6rem;
            }

            .side nav a.active::before {
                left: 25%;
                right: 25%;
                top: auto;
                bottom: -1px;
                width: auto;
                height: 2px;
            }

            .main {
                padding: 1.8rem 1.4rem 4rem;
            }
        }

        @media (max-width: 640px) {
            .topbar .date {
                display: none;
            }

            .panel,
            .msg,
            .stat {
                padding: 1.15rem 1.1rem;
            }

            .chart {
                gap: .35rem;
                height: 100px;
            }

            .chart-caps span:nth-child(even) {
                visibility: hidden;
            }
        }
    </style>
    <style>
        /* Dashboard refresh: a restrained application interface, not a decorative landing page. */
        :root {
            --bg: #f3f5f9;
            --paper: #ffffff;
            --ink: #172033;
            --soft: #5e6b80;
            --faint: #8793a8;
            --line: #e4e9f1;
            --line-strong: #ccd6e4;
            --gold: #2473e8;
            --gold-soft: #eaf3ff;
            --green: #0a9a75;
            --shadow: 0 2px 6px rgba(20, 35, 60, .025), 0 12px 28px rgba(20, 35, 60, .05);
            --shadow-hover: 0 16px 34px rgba(20, 35, 60, .09);
        }

        body.dash { background: var(--bg); }
        .paper-grain { display: none; }
        .adm-progress { height: 3px; background: #2473e8; }

        .side {
            width: 258px;
            padding: 1.5rem 1rem;
            background: #111c31;
            border-right: 0;
            box-shadow: none;
        }

        .side .brand {
            align-items: center;
            gap: .7rem;
            margin: .15rem .55rem 1.65rem;
        }

        .side .brand::before {
            content: 'C';
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #2473e8;
            color: #fff;
            font: 800 .95rem/1 'Manrope', sans-serif;
            box-shadow: 0 6px 14px rgba(36, 115, 232, .28);
        }

        .side .brand b {
            font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .side .brand small { color: #81b2ff; font-size: .5rem; }
        .side .hairline { margin: 0 .55rem 1rem; background: rgba(255, 255, 255, .1); }
        .side nav { gap: .3rem; }
        .side nav a { padding: .78rem .85rem; border-radius: 8px; color: #aebbd0; }
        .side nav a:hover { background: rgba(255, 255, 255, .07); transform: none; }
        .side nav a.active { background: #2473e8; box-shadow: 0 7px 15px rgba(0, 0, 0, .13); }
        .side nav a.active::before { display: none; }
        .side nav a .cnt { color: #fff; font-size: .64rem; }
        .side .who { margin: 1rem .55rem 0; padding-top: 1rem; }
        .side .who span { color: #91a0b8; }
        .side .out { margin: .9rem .55rem 0; justify-content: center; color: #d9e2f0; }

        .main { max-width: 1440px; padding: 1.75rem 3rem 4rem; }
        .topbar { margin-bottom: 1.25rem; }
        .topbar .date { color: #6d7b91; font-weight: 500; letter-spacing: .1em; }
        .live-chip { box-shadow: 0 1px 2px rgba(20, 35, 60, .04); }

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 1.45rem;
            padding: 2.35rem 2.5rem;
            border-radius: 14px;
            background: #172a46;
            color: #fff;
            box-shadow: 0 14px 28px rgba(19, 42, 76, .14);
        }

        .hero::after {
            content: '';
            position: absolute;
            right: -70px;
            bottom: -110px;
            width: 310px;
            height: 310px;
            border: 50px solid rgba(86, 157, 255, .11);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero .eyebrow { color: #9bc2ff; margin-bottom: .8rem; }
        .hero .eyebrow::after { background: #8bb9ff; }
        .hero h1 { max-width: 680px; color: #fff; font-size: clamp(2rem, 3.5vw, 2.9rem); }
        .hero h1 em { color: #94c0ff; }
        .hero p.lede { position: relative; z-index: 1; max-width: 600px; color: #c3d0e2; }

        .stats { gap: 1rem; margin-bottom: 1.5rem; }
        .stat { padding: 1.35rem 1.4rem; border-radius: 12px; box-shadow: var(--shadow); }
        .stat:hover { transform: translateY(-2px); }
        .stat::after { left: 1.4rem; right: 1.4rem; height: 3px; }
        .stat .lbl { margin-bottom: .85rem; color: #718096; }
        .stat b.num { font-size: 2.35rem; }

        .panel { margin-bottom: 2rem; padding: 1.5rem; border-radius: 12px; }
        .panel-head { margin-bottom: 1.25rem; }
        .panel-head h2, .inbox-head h2 { font-size: 1rem; letter-spacing: -.02em; }
        .chart { height: 160px; padding: 0 .35rem .1rem; gap: 1rem; }
        .chart .bar { border-radius: 5px 5px 1px 1px; background: #a8cbfb; }
        .chart .bar.is-today { background: #2473e8; }
        .chart .bar:hover { filter: brightness(.93); }
        .chart-caps { padding: 0 .35rem; }

        .inbox-head { margin-bottom: 1rem; }
        .inbox-head .badge { background: transparent; color: #718096; }
        .message-filters {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            align-items: end;
            gap: .85rem;
            margin: 0 0 1.25rem;
            padding: 1rem 1.15rem;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--paper);
        }
        .filter-field { display: grid; gap: .38rem; }
        .filter-field label {
            color: #728096;
            font-size: .67rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .filter-field input, .filter-field select {
            width: 100%;
            min-height: 38px;
            border: 1px solid var(--line-strong);
            border-radius: 7px;
            background: #fff;
            color: var(--ink);
            padding: .45rem .65rem;
            font: 600 .78rem 'Manrope', sans-serif;
            outline: none;
        }
        .filter-field input:focus, .filter-field select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-soft); }
        .filter-actions { display: flex; align-items: center; gap: .65rem; }
        .filter-apply, .filter-reset {
            min-height: 38px;
            border-radius: 7px;
            padding: 0 .85rem;
            font: 700 .76rem 'Manrope', sans-serif;
            text-decoration: none;
            white-space: nowrap;
        }
        .filter-apply { border: 1px solid #2473e8; background: #2473e8; color: #fff; cursor: pointer; }
        .filter-reset { display: inline-flex; align-items: center; color: #607087; }
        .filter-reset:hover { color: #2473e8; }
        .filter-summary { margin: -.3rem 0 1rem; color: #718096; font-size: .76rem; }
        .msgs { gap: .75rem; }
        .msg { padding: 1.25rem 1.4rem; border-radius: 10px; box-shadow: none; }
        .msg:hover { transform: none; border-color: #bdd1ee; box-shadow: 0 6px 18px rgba(26, 55, 100, .06); }
        .msg.is-new { border-left: 3px solid #2473e8; }
        .msg.is-new::before { top: 1.15rem; right: 1.3rem; background: #eaf3ff; color: #2473e8; font-weight: 700; }
        .msg-head .ava { width: 38px; height: 38px; background: #eaf3ff; color: #2473e8; border-radius: 9px; }
        .msg:hover .ava { transform: none; }
        .msg p.msg-body { max-width: 900px; color: #536176; }
        .msg a.mail, .msg button.mail { color: #2473e8; border-color: #bdd4f7; background: #fff; border-radius: 7px; }
        .msg a.mail::before, .msg button.mail::before { background: #2473e8; }
        .msg a.mail:hover, .msg button.mail:hover { border-color: #2473e8; }
        .foot { margin-top: 2.25rem; }

        @media (max-width: 900px) {
            .side { width: 100%; padding: 1rem; }
            .side .brand { margin: .15rem .35rem .75rem; }
            .side .hairline { display: none; }
            .side nav a { padding: .65rem .75rem; }
            .main { padding: 1.3rem 1.25rem 3rem; }
            .hero { padding: 1.8rem 1.5rem; }
        }

        @media (max-width: 640px) {
            .hero { border-radius: 10px; }
            .hero h1 { font-size: 1.85rem; }
            .chart { gap: .45rem; }
            .message-filters { grid-template-columns: 1fr; align-items: stretch; }
            .filter-actions { justify-content: space-between; }

        /* ---------- reply panel ---------- */
        .reply-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            backdrop-filter: blur(2px);
            z-index: 290;
            animation: fade-in .25s var(--ease) both;
        }

        .reply-panel {
            position: fixed;
            bottom: 0;
            right: 0;
            top: 0;
            width: min(520px, 100%);
            background: var(--paper);
            z-index: 300;
            box-shadow: -18px 0 48px rgba(18, 32, 55, .18);
            display: flex;
            flex-direction: column;
            padding: 1.8rem 1.9rem;
            animation: slide-in .35s var(--ease) both;
        }

        @keyframes slide-in {
            from { transform: translateX(40px); opacity: 0; }
            to   { transform: none; opacity: 1; }
        }

        .reply-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1.2rem;
            border-bottom: 1px solid var(--line);
            margin-bottom: 1.2rem;
        }

        .reply-eyebrow {
            margin: 0 0 .3rem;
            font-family: 'DM Mono', Consolas, monospace;
            font-size: .6rem;
            letter-spacing: .26em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .reply-head h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .reply-email {
            margin: .3rem 0 0;
            color: var(--faint);
            font-size: .8rem;
            word-break: break-all;
        }

        .reply-close {
            flex: none;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: transparent;
            color: var(--soft);
            font-size: 1.15rem;
            cursor: pointer;
            transition: background .2s, color .2s, border-color .2s;
        }

        .reply-close:hover {
            color: var(--ink);
            border-color: var(--line-strong);
            background: rgba(15, 23, 42, .04);
        }

        .reply-quote {
            border-left: 3px solid var(--line-strong);
            padding: .55rem .9rem;
            margin-bottom: 1.2rem;
            color: var(--soft);
            font-size: .8rem;
            line-height: 1.65;
            white-space: pre-wrap;
            max-height: 130px;
            overflow: auto;
        }

        #replyText {
            width: 100%;
            resize: vertical;
            min-height: 150px;
            border: 1px solid var(--line-strong);
            border-radius: 9px;
            padding: .8rem .95rem;
            font: 500 .86rem/1.6 'Manrope', sans-serif;
            color: var(--ink);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        #replyText:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px var(--gold-soft);
        }

        .reply-actions {
            display: flex;
            align-items: center;
            gap: .55rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .reply-hint {
            color: var(--faint);
            font-size: .72rem;
            font-weight: 700;
        }

        .reply-send {
            cursor: pointer;
            border: 1px solid var(--gold);
            background: var(--gold);
            color: #fff;
            border-radius: 7px;
            padding: .55rem .95rem;
            font: 700 .76rem 'Manrope', sans-serif;
            transition: background .2s, border-color .2s, transform .2s var(--ease);
        }

        .reply-send:hover { transform: translateY(-1px); filter: brightness(1.08); }

        .reply-send.alt {
            background: transparent;
            color: var(--gold);
        }

        .reply-send.alt:hover { background: var(--gold-soft); }

        @media (prefers-reduced-motion: reduce) {
            .reply-panel, .reply-overlay { animation: none !important; }
        }
        }
    </style>
</head>

<body class="dash">
    <div class="adm-progress" id="admProgress" aria-hidden="true"></div>
    <div class="paper-grain" aria-hidden="true"></div>

    <div class="shell">
        <aside class="side">
            <div class="brand">
                <b>Comcares</b>
                <small>ADMIN</small>
            </div>
            <div class="hairline"></div>
            <nav>
                <a class="active" href="dashboard.php" aria-current="page">&#9636;&ensp;Enquiries <span class="cnt"><?= $total ?></span></a>
                <?php
                $adminCounts = [
                    'quotations' => (int)db()->query('SELECT COUNT(*) FROM quotations')->fetchColumn(),
                    'purchases'  => (int)db()->query('SELECT COUNT(*) FROM purchases')->fetchColumn(),
                    'chats'      => (int)db()->query('SELECT COUNT(*) FROM chat_sessions')->fetchColumn(),
                    'users'      => (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
                ];
                ?>
                <a href="quotations.php">&#128196;&ensp;Quotations <span class="cnt"><?= $adminCounts['quotations'] ?></span></a>
                <a href="purchases.php">&#128722;&ensp;Purchases <span class="cnt"><?= $adminCounts['purchases'] ?></span></a>
                <a href="chats.php">&#128172;&ensp;Chatbot <span class="cnt"><?= $adminCounts['chats'] ?></span></a>
                <a href="users.php">&#128100;&ensp;Users <span class="cnt"><?= $adminCounts['users'] ?></span></a>
            </nav>
            <div class="spacer"></div>
            <div class="who">
                <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b>
                <span>Signed in as administrator</span>
            </div>
            <a class="out" href="dashboard.php?action=logout">&#10140;&ensp;Sign out</a>
        </aside>

        <main class="main">
            <div class="topbar">
                <span class="date" id="dateLine"></span>
                <span class="live-chip"><span class="dot"></span><span id="clock">&mdash;:&mdash;</span></span>
            </div>

            <div class="hero">
                <p class="eyebrow">Contact Enquiries</p>
                <h1>Everything your visitors <em>said to you.</em></h1>
                <p class="lede">A calm, simple record of every message received through the website &mdash; newest first, nothing in the way.</p>
            </div>

            <div class="stats">
                <div class="stat" style="--c: var(--gold)">
                    <span class="lbl"><i class="ic" aria-hidden="true">&#9993;</i> Total messages</span>
                    <b class="num"><?= $total ?></b>
                    <span class="sub">All enquiries received to date</span>
                </div>
                <div class="stat" style="--c: var(--green)">
                    <span class="lbl"><i class="ic" aria-hidden="true">&#9788;</i> Today</span>
                    <b class="num"><?= $today ?></b>
                    <span class="sub">Arrived since midnight</span>
                </div>
                <div class="stat" style="--c: var(--ink)">
                    <span class="lbl"><i class="ic" aria-hidden="true">&#9201;</i> Latest</span>
                    <b class="num" style="font-size:1.7rem; padding-top:.6rem;"><?= $latest ? htmlspecialchars($latest['name']) : '&mdash;' ?></b>
                    <span class="sub"><?= $latest ? 'Most recent &middot; ' . timeAgo($latest['created_at']) : 'No messages yet' ?></span>
                </div>
            </div>

            <?php
            // last-7-days activity for the chart
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
                    <h2>This week&rsquo;s activity</h2>
                    <span class="meta">peak <?= $peak ?>/day</span>
                </div>
                <div class="chart" role="img" aria-label="Messages received per day over the last seven days">
                    <?php foreach ($days as $d): ?>
                        <div class="col">
                            <div class="bar <?= $d['today'] ? 'is-today' : '' ?>" style="height: <?= max(3, round($d['count'] / $peak * 100)) ?>%;">
                                <span class="tip"><?= $d['count'] ?> message<?= $d['count'] === 1 ? '' : 's' ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-caps">
                    <?php foreach ($days as $d): ?>
                        <span><?= $d['label'] ?></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="inbox-head" id="inbox">
                <h2>The inbox</h2>
                <span class="badge"><?= $total ?> message<?= $total === 1 ? '' : 's' ?> &middot; newest first</span>
            </div>

            <form class="message-filters" method="get" action="dashboard.php" aria-label="Filter contact messages">
                <div class="filter-field">
                    <label for="filter-date">Specific date</label>
                    <input type="date" id="filter-date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
                </div>
                <div class="filter-field">
                    <label for="filter-month">Month</label>
                    <input type="month" id="filter-month" name="month" value="<?= htmlspecialchars($filterMonth) ?>">
                </div>
                <div class="filter-field">
                    <label for="filter-day">Day of week</label>
                    <select id="filter-day" name="day">
                        <option value="">Every day</option>
                        <?php foreach ($weekdays as $day): ?>
                            <option value="<?= $day ?>" <?= $filterDay === $day ? 'selected' : '' ?>><?= ucfirst($day) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-apply" type="submit">Apply filters</button>
                    <?php if ($filterDate || $filterMonth || $filterDay): ?>
                        <a class="filter-reset" href="dashboard.php">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($filterDate || $filterMonth || $filterDay): ?>
                <p class="filter-summary">Showing <?= $total ?> saved message<?= $total === 1 ? '' : 's' ?> matching your filter<?= ($filterDate && $filterMonth) || $filterDay ? 's' : '' ?>.</p>
            <?php endif; ?>

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
                                        <b><?= htmlspecialchars($msg['name']) ?></b><br>
                                        <span class="em">&lt;<?= htmlspecialchars($msg['email']) ?>&gt;</span>
                                    </span>
                                </div>
                                <time title="<?= htmlspecialchars($msg['created_at']) ?>"><?= timeAgo($msg['created_at']) ?></time>
                            </div>
                            <p class="msg-body"><?= htmlspecialchars($msg['message']) ?></p>
                            <button type="button" class="mail" data-reply
                                    data-name="<?= htmlspecialchars($msg['name']) ?>"
                                    data-email="<?= htmlspecialchars($msg['email']) ?>"
                                    data-message="<?= htmlspecialchars($msg['message']) ?>"
                                    data-sent="<?= htmlspecialchars($msg['created_at']) ?>">
                                <span>&#9993; Reply to this person</span>
                            </button>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="foot">
                <span class="mono">Comcares &middot; Admin Panel</span>
                <span>Designed for <span class="grad">speed &amp; clarity</span></span>
            </div>

            <!-- reply panel -->
            <div class="reply-overlay" id="replyOverlay" hidden></div>
            <section class="reply-panel" id="replyPanel" role="dialog" aria-modal="true" aria-labelledby="replyTitle" hidden>
                <header class="reply-head">
                    <div>
                        <p class="reply-eyebrow">Reply to enquiry</p>
                        <h2 id="replyTitle">&mdash;</h2>
                        <p class="reply-email" id="replyEmail">&mdash;</p>
                    </div>
                    <button type="button" class="reply-close" id="replyClose" aria-label="Close reply panel">&times;</button>
                </header>
                <div class="reply-quote" id="replyQuote"></div>
                <form id="replyForm">
                    <textarea id="replyText" rows="7" placeholder="Type your reply&hellip;"></textarea>
                    <div class="reply-actions">
                        <span class="reply-hint">Choose how to send:</span>
                        <button type="button" class="reply-send" data-mode="gmail">Open in Gmail</button>
                        <button type="button" class="reply-send alt" data-mode="mailapp">System mail app</button>
                        <button type="button" class="reply-send alt" data-mode="copy">Copy email</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        (function() {
            "use strict";
            var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

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

            /* date + live clock */
            var clock = document.getElementById("clock");
            var dateLine = document.getElementById("dateLine");
            var now = new Date();
            if (dateLine) dateLine.textContent = now.toLocaleDateString([], {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric"
            });

            function tickClock() {
                clock.textContent = new Date().toLocaleTimeString([], {
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
                el.textContent = "0";
                var start = null;

                function tick(now) {
                    if (start === null) start = now;
                    var p = Math.min((now - start) / 1200, 1);
                    el.textContent = Math.round(easeOut(p) * goal);
                    if (p < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });

            /* staggered reveal for below-the-fold cards */
            if (!reduce) {
                var io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(en) {
                        if (en.isIntersecting) {
                            en.target.style.animationPlayState = "running";
                            io.unobserve(en.target);
                        }
                    });
                }, {
                    threshold: .12
                });
                document.querySelectorAll(".msg").forEach(function(m) {
                    m.style.animationPlayState = "paused";
                    io.observe(m);
                });
            }

            /* ---------- reply panel ---------- */
            var overlay = document.getElementById("replyOverlay");
            var panel = document.getElementById("replyPanel");
            var closeBtn = document.getElementById("replyClose");
            var tName = document.getElementById("replyTitle");
            var tEmail = document.getElementById("replyEmail");
            var tQuote = document.getElementById("replyQuote");
            var tText = document.getElementById("replyText");
            var current = { name: "", email: "" };

            function esc(s) {
                var d = document.createElement("div");
                d.textContent = s == null ? "" : String(s);
                return d.textContent;
            }

            function openReply(btn) {
                current.name = btn.dataset.name || "";
                current.email = btn.dataset.email || "";
                var sent = btn.dataset.sent || "";
                var body = btn.dataset.message || "";
                tName.textContent = current.name;
                tEmail.textContent = current.email;
                tQuote.textContent = "On " + sent + ", " + current.name + " wrote:\n\n" + body;
                tText.value = "Hi " + current.name + ",\n\nThank you for reaching out to Comcares. ";
                overlay.hidden = false;
                panel.hidden = false;
                document.body.style.overflow = "hidden";
                setTimeout(function() { tText.focus(); }, 80);
            }

            function closeReply() {
                overlay.hidden = true;
                panel.hidden = true;
                document.body.style.overflow = "";
            }

            document.querySelectorAll("[data-reply]").forEach(function(btn) {
                btn.addEventListener("click", function() { openReply(btn); });
            });

            if (closeBtn) closeBtn.addEventListener("click", closeReply);
            if (overlay) overlay.addEventListener("click", closeReply);
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape" && !panel.hidden) closeReply();
            });

            document.querySelectorAll(".reply-send").forEach(function(btn) {
                btn.addEventListener("click", function() {
                    var subject = encodeURIComponent("Re: your enquiry to Comcares");
                    var quoted = tQuote.textContent.split("\n").map(function(l) { return "> " + l; }).join("\n");
                    var body = encodeURIComponent(tText.value + "\n\n\n------\n" + quoted);
                    var mode = btn.dataset.mode;

                    if (mode === "gmail") {
                        window.open("https://mail.google.com/mail/?view=cm&fs=1&to=" + encodeURIComponent(current.email) + "&su=" + subject + "&body=" + body, "_blank");
                    } else if (mode === "mailapp") {
                        window.location.href = "mailto:" + current.email + "?subject=" + subject + "&body=" + body;
                    } else if (mode === "copy") {
                        navigator.clipboard.writeText(current.email).then(function() {
                            var old = btn.textContent;
                            btn.textContent = "Copied!";
                            setTimeout(function() { btn.textContent = old; }, 1400);
                        });
                    }
                });
            });
        })();
    </script>
</body>

</html>
