<?php
$catalogue = [
    'digital-experiences' => [
        'name' => 'Digital experiences', 'label' => 'Websites and digital products', 'topic' => 'New website', 'from' => 'Rs. 4.5L',
        'intro' => 'A clear, quick and credible online presence that gives your customers a reason to stay.',
        'outcomes' => ['A responsive experience built around your customers', 'A content structure your team can keep current', 'Performance, SEO and analytics ready from launch'],
        'process' => ['Content and audience workshop', 'Design direction and clickable prototype', 'Build, testing and launch support']
    ],
    'custom-software' => [
        'name' => 'Custom software', 'label' => 'Portals, dashboards and workflows', 'topic' => 'Custom software', 'from' => 'Rs. 9L',
        'intro' => 'Useful internal tools and customer portals shaped around the way your team already works.',
        'outcomes' => ['Workflows that remove repetitive tasks', 'Role-based access, reporting and audit trails', 'Practical integrations with your existing systems'],
        'process' => ['Workflow mapping with your team', 'Prototype the critical journeys first', 'Phased build, demos and handover']
    ],
    'cloud-automation' => [
        'name' => 'Cloud and automation', 'label' => 'Infrastructure that keeps work moving', 'topic' => 'Custom software', 'from' => 'Rs. 6.5L',
        'intro' => 'Reliable cloud foundations and automations that give your team more time for valuable work.',
        'outcomes' => ['A secure, scalable cloud setup', 'Backup and recovery plans that are tested', 'Automated hand-offs between the tools you use'],
        'process' => ['Audit the current environment', 'Plan migration and safeguards', 'Move, validate and monitor']
    ],
    'managed-technology' => [
        'name' => 'Managed technology', 'label' => 'Support after launch', 'topic' => 'Support & maintenance', 'from' => 'Rs. 25k / month',
        'intro' => 'Proactive maintenance and a team that already understands your system when help is needed.',
        'outcomes' => ['Monitoring and clear incident communication', 'Security patches and routine upkeep', 'A direct route to an engineer who knows the work'],
        'process' => ['Technical handover and baseline audit', 'Monitoring and maintenance plan', 'Monthly improvements and transparent reporting']
    ],
    'it-consulting' => [
        'name' => 'IT consulting', 'label' => 'Decisions with confidence', 'topic' => 'General enquiry', 'from' => 'Rs. 1.5L',
        'intro' => 'Straight, independent technical advice before you invest time or money in the wrong direction.',
        'outcomes' => ['A roadmap linked to business priorities', 'A clear view of technical risk and cost', 'Recommendations you can use with any vendor'],
        'process' => ['Listen to the problem and constraints', 'Review systems, vendors and options', 'Deliver clear recommendations and next steps']
    ],
    'ecommerce-builds' => [
        'name' => 'E-commerce builds', 'label' => 'Online stores that convert', 'topic' => 'New website', 'from' => 'Rs. 7L',
        'intro' => 'Online shopping experiences that make it easy to find, trust and buy what you sell.',
        'outcomes' => ['A catalogue and checkout designed for conversion', 'Payments, order flows and inventory connected', 'Reporting that shows what customers actually do'],
        'process' => ['Map products and customer journey', 'Design the store and checkout', 'Integrate, test and launch with your team']
    ],
];
$slug = trim($_GET['service'] ?? '');
$service = $catalogue[$slug] ?? null;
if (!$service) { http_response_code(404); $service = $catalogue['digital-experiences']; }
$pageTitle = $service['name'];
$pageDesc = $service['intro'];
require __DIR__ . '/includes/header.php';
$enquiry = rawurlencode('I would like to discuss ' . $service['name'] . '.');
?>
<div class="next-site next-service-detail">
    <section class="next-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div><div class="next-orb next-orb-one" aria-hidden="true"></div><div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Services / <?= htmlspecialchars($service['label']) ?></p>
            <h1><?= htmlspecialchars($service['name']) ?><br><em>made practical.</em></h1>
            <div class="next-hero-bottom"><p><?= htmlspecialchars($service['intro']) ?></p><div class="next-actions"><a class="next-button next-button-brand next-ripple" href="contact.php?topic=<?= rawurlencode($service['topic']) ?>&amp;message=<?= $enquiry ?>">Discuss this service <b>&nearr;</b></a><a class="next-text-link" href="#outcomes">What you get <b>&darr;</b></a></div></div>
            <div class="next-hero-facts"><div class="next-fact"><strong><?= htmlspecialchars($service['from']) ?></strong><span>Typical starting scope</span></div><div class="next-fact"><strong>01</strong><span>Dedicated delivery team</span></div><div class="next-fact"><strong>30</strong><span>Days of launch support</span></div></div>
        </div>
    </section>
    <section class="next-section" id="outcomes"><div class="next-wrap"><div class="next-section-head next-animate"><p class="next-eyebrow"><span></span>What changes</p><h2>Built around<br><em>real work.</em></h2><p class="next-intro">Each engagement starts with the problem behind the request, then concentrates on the changes that make the most difference.</p></div><div class="next-detail-grid"><?php foreach ($service['outcomes'] as $n => $outcome): ?><article class="next-detail-card next-animate"><span>0<?= $n + 1 ?></span><h3><?= htmlspecialchars($outcome) ?></h3></article><?php endforeach; ?></div></div></section>
    <section class="next-dark-panel"><div class="next-wrap"><div class="next-process next-animate"><div><p class="next-eyebrow"><span></span>How we deliver</p><h2>A route you can<br><em>see clearly.</em></h2></div><p class="next-process-copy">No black box. You will see the work early, give feedback at the right moments, and know what happens after launch.</p></div><div class="next-steps next-animate"><?php foreach ($service['process'] as $n => $step): ?><article><span>0<?= $n + 1 ?> / Step</span><h3><?= htmlspecialchars($step) ?></h3><p>Clear ownership, visible progress and a decision-ready next step.</p></article><?php endforeach; ?></div></div></section>
    <section class="next-contact"><div class="next-wrap next-animate"><p class="next-eyebrow"><span></span>Make the next move</p><h2>Have this in<br><em>mind already?</em></h2><a class="next-button next-button-dark next-ripple" href="contact.php?topic=<?= rawurlencode($service['topic']) ?>&amp;message=<?= $enquiry ?>">Start a focused conversation <b>&nearr;</b></a></div></section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
