<?php

/**
 * COMCARES assistant endpoint.
 * Stateful scripted knowledge-base responder (no external AI service).
 * - PDO prepared statements only
 * - Conversation context stored in the chat_sessions/chat_messages tables
 * - Simple per-session rate limiting
 * - Never exposes server errors or credentials to the frontend
 */

session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

/* ---------- helpers ---------- */
function fail(string $type, string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => ['type' => $type, 'message' => $message]]);
    exit;
}

function stripTags(string $s): string
{
    $s = trim($s);
    $s = strip_tags($s);
    return mb_substr($s, 0, 1000);
}

/* ---------- rate limiting (simple, per PHP session) ---------- */
if (!isset($_SESSION['chat_hits'])) $_SESSION['chat_hits'] = [];
$_SESSION['chat_hits'] = array_values(array_filter($_SESSION['chat_hits'], fn($t) => $t > time() - 60));
if (count($_SESSION['chat_hits']) >= 20) {
    fail('rate_limited', 'You are sending messages quite quickly. Please wait a few seconds and try again.', 429);
}
$_SESSION['chat_hits'][] = time();

/* ---------- input ---------- */
$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) $data = $_POST;

$message = stripTags((string)($data['message'] ?? ''));
if ($message === '') {
    fail('validation', 'Please type a message before sending.');
}

/* ---------- session / context ---------- */
$sessionId = preg_replace('/[^a-z0-9]/i', '', substr((string)($data['session_id'] ?? ''), 0, 40));
if ($sessionId === '') {
    $sessionId = bin2hex(random_bytes(16));
}

$pdo = db();

// Ensure the session row exists
$ins = $pdo->prepare('INSERT IGNORE INTO chat_sessions (session_id) VALUES (?)');
$ins->execute([$sessionId]);

// Short conversation history for context-aware small talk
$hist = $pdo->prepare('SELECT sender, message FROM chat_messages WHERE session_id = ? ORDER BY id DESC LIMIT 4');
$hist->execute([$sessionId]);
$recent = array_reverse($hist->fetchAll());

/* ---------- knowledge base (scripted but context aware) ---------- */
$KB = [
    'services' => "We offer four core services:\n1. **Website Design & Development** — responsive, SEO-friendly websites.\n2. **Web Applications** — dashboards, portals and booking systems in PHP/MySQL.\n3. **Maintenance & Hosting Support** — updates, backups and monitoring.\n4. **IT Consulting** — practical advice on tools, security and infrastructure.\n\nSee the full list on our [services page](services.php) — or ask me about any of them!",
    'pricing'  => "Every project is scoped individually, so pricing depends on what you need. A simple website starts small, while custom portals or school management systems are quoted after a short discovery chat.\n\nThe easiest way to get a number: [send us your idea](contact.php) and we'll reply with a clear estimate — no obligation.",
    'contact'  => "You can reach us through the [contact form](contact.php) — tell us your name, email and what you're working on, and the team responds directly. Messages land straight in our admin dashboard, so nothing gets lost.",
    'products' => "Our flagship product is **School Sathi** — a complete school management platform covering admissions, fees, attendance, timetables, exams and parent communication. Explore it at [schoolsathi.in](https://schoolsathi.in/hub/home).\n\nWe also build custom business websites and web apps, and offer IT support plans. See [our products page](products.php).",
    'about'    => "**COMCARES Innovation Services Pvt. Ltd.** builds websites, business software and intelligent digital systems. We work end-to-end — strategy, design, engineering and ongoing support — so you deal with one team instead of five vendors. More on our [about page](about.php).",
    'admin'    => "The admin panel is for our team only — it's where we review contact enquiries. If you're a client, use the [contact form](contact.php) and we'll get back to you quickly.",
    'hours'    => "Our website takes messages 24/7, and we typically respond within one business day. Ongoing support plans include priority response times.",
    'hello'    => "Hello! 👋 I'm the COMCARES assistant. I can tell you about our services, our product School Sathi, pricing approach, or how to reach the team. What would you like to know?",
    'thanks'   => "You're very welcome! If anything else comes to mind — services, School Sathi, or starting a project — just ask. 😊",
    'goodbye'  => "Thanks for stopping by! Whenever you're ready to start something, the [contact form](contact.php) is the fastest route to the team. Goodbye! 👋",
    'fallback' => "That's a good question — it's a bit outside my scripted knowledge though. I can tell you about our **services**, our product **School Sathi**, **pricing**, or **how to contact** the team.\n\nFor anything detailed, [send us a message](contact.php) and a real person will answer.",
];

/* Greeting on first message of the session with empty-ish intent handled below */

$subject = mb_strtolower($message);
$reply = null;

// Small-talk detection uses recent context (is this a follow-up?)
$lastBot = null;
foreach ($recent as $r) {
    if ($r['sender'] === 'bot') {
        $lastBot = mb_strtolower($r['message']);
        break;
    }
}

$rules = [
    ['hello',    '~\b(hi|hii|hey+|hello+|namaste|good (morning|afternoon|evening)|salam)\b~u'],
    ['services', '~\b(service|services|offer|offering|what do you (do|offer)|capabilities|solutions)\b~u'],
    ['pricing',  '~\b(price|pricing|cost|charge|budget|quote|how much|rate|estimate|fees?)\b~u'],
    ['contact',  '~\b(contact|reach|email|phone|call|touch|message you|talk to)\b~u'],
    ['products', '~\b(product|products|school sathi|schoolsathi|software you (have|built|own))\b~u'],
    ['about',    '~\b(about (you|us|comcares)|who are you|company|your team|tell me about)\b~u'],
    ['admin',    '~\b(admin|admin panel|dashboard|login)\b~u'],
    ['hours',    '~\b(hours|timing|open|available|response time|how (fast|soon))\b~u'],
    ['thanks',   '~\b(thanks|thank you|thx|great|awesome|nice|cool)\b~u'],
    ['goodbye',  '~\b(bye|goodbye|see you|later)\b~u'],
];

$matchedTopic = null;
foreach ($rules as [$topic, $regex]) {
    if (preg_match($regex, $subject)) {
        $matchedTopic = $topic;
        break;
    }
}

// "what is your name" style meta questions
if (preg_match('~\b(your name|who are you|what are you)\b~u', $subject) && preg_match('~\b(what|who)\b~u', $subject)) {
    $matchedTopic = 'about';
}

if ($matchedTopic && isset($KB[$matchedTopic])) {
    $reply = $KB[$matchedTopic];
} elseif ($matchedTopic === null && $recent && $lastBot) {
    // Very short follow-ups like "more", "yes", "2nd one" → politely steer
    if (mb_strlen($subject) <= 6 && !preg_match('~\b(price|cost|services?)\b~u', $subject)) {
        $reply = "Could you tell me a little more? For example you can ask about our **services**, **pricing**, the **School Sathi** product, or **how to contact** the team.";
    }
}

if ($reply === null) {
    $reply = $KB['fallback'];
}

/* ---------- persist the exchange ---------- */
$save = $pdo->prepare('INSERT INTO chat_messages (session_id, sender, message) VALUES (?, ?, ?)');
$save->execute([$sessionId, 'user', $message]);
$save->execute([$sessionId, 'bot', $reply]);

/* ---------- respond ---------- */
echo json_encode([
    'ok'         => true,
    'reply'      => $reply,
    'session_id' => $sessionId,
    'role'       => 'assistant', // honest: scripted assistant, not a live AI model
]);
