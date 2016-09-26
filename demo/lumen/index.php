<?php

use DebugBar\DebugBar;
use Laravel\Lumen\Application;
use Netpromotion\Profiler\Profiler;

require_once __DIR__ . "/../require_me.php";

if (DEBUG_MODE === true) {
    Profiler::enable(); // this is required only if you need to profile before container is created
}

Profiler::start(/* keep default label for better preview */);

$headAppendix = "";
$bodyAppendix = "";
if (DEBUG_MODE === true) {
    $debugBar = new DebugBar();
    $debugBarRenderer = $debugBar->getJavascriptRenderer();
    $headAppendix .= $debugBarRenderer->renderHead();
    $bodyAppendix .= $debugBarRenderer->render();
}

$app = new Application(__DIR__);

$app->get("/lumen/", function() use ($app) {
    return $app->welcome();
});

ob_start();
$app->run();
$output = ob_get_contents();
ob_end_clean();

$output = preg_replace('/<\/head>/i', $headAppendix . "</head>", $output, 1);
$output = preg_replace('/<\/body>/i', $bodyAppendix . "</body>", $output, 1);

Profiler::finish(/* keep default label for better preview */);

print($output);
