<?php

use Laravel\Lumen\Application;
use Netpromotion\Profiler\Profiler;

require_once __DIR__ . "/../require_me.php";

if (DEBUG_MODE === true) {
    Profiler::enable(); // this is required only if you need to profile before container is created
}

Profiler::start(/* keep default label for better preview */);

$app = new Application(__DIR__);

$app->get("/lumen/", function() use ($app) {
    return $app->welcome();
});

$app->run();

Profiler::finish(/* keep default label for better preview */);
