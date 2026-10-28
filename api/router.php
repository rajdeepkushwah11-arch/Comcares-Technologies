<?php

$path = $_GET['path'] ?? 'index.php';

if ($path === 'api/chatbot.php') {
    /* Degraded mode (e.g. Vercel preview without a MySQL backend):
       answer with a friendly scripted reply instead of a raw 503 error,
       so the chat widget keeps working for visitors. */
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '[]', true);
    if (!is_array($data)) $data = $_POST;
    $msg = strtolower(trim((string)($data['message'] ?? '')));

    if ($msg === '' ) $msg = 'ping';

    if ($msg === 'ping') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'reply' => "Hi! I'm the COMCARES assistant. Ask me about our services, School Sathi, pricing or how to get started.",
        ]);
        exit;
    }

    /* Very small keyword responder so common questions still get answers. */
    $replies = [
        'service' => "We build websites, web applications, e-commerce, custom software and offer hosting, maintenance and IT consulting. More on the [Services](services.php) page.",
        'website' => "Yes — websites are our core service, from single-page portfolios to full business sites with CMS. See [Services](services.php) for details.",
        'school'  => "School Sathi is our school management platform — admissions, fees, attendance and communication in one place. Ask for a demo via the [contact page](contact.php).",
        'price'   => "Pricing depends on scope. Use the [project estimator](index.php#estimator) on the homepage for an instant ballpark, or request a quote on the [quote page](quote.php).",
        'cost'    => "Pricing depends on scope. Use the [project estimator](index.php#estimator) on the homepage for an instant ballpark, or request a quote on the [quote page](quote.php).",
        'contact' => "You can reach us any time through the [contact page](contact.php) — we reply within one working day.",
        'email'   => "Email us at hello@comcares.com or use the [contact page](contact.php).",
        'phone'   => "Message us on WhatsApp at +91 97544 57120, or use the [contact page](contact.php).",
        'whatsapp'=> "Message us on WhatsApp at +91 97544 57120, or use the [contact page](contact.php).",
        'hello'   => "Hello! 👋 How can I help — services, School Sathi, pricing or something else?",
        'hi'      => "Hello! 👋 How can I help — services, School Sathi, pricing or something else?",
    ];
    foreach ($replies as $needle => $reply) {
        if (strpos($msg, $needle) !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'reply' => $reply]);
            exit;
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'reply' => "Thanks for your message! This preview is running a lightweight version of the assistant. For a full answer, check the [services](services.php) or [contact](contact.php) page — or try asking about services, pricing or School Sathi.",
    ]);
    exit;
}

$pages = [
    'index.php',
    'about.php',
    'services.php',
    'products.php',
    'contact.php',
    'login.php',
    'forgot-password.php',
    'reset-password.php',
    'logout.php',
    'admin/dashboard.php',
    'admin/login.php',
    'admin/status.php',
    'admin/chats.php',
    'admin/purchases.php',
    'admin/quotations.php',
    'admin/users.php',
];

if (!in_array($path, $pages, true)) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
