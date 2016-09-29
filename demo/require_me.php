<?php

use Netpromotion\Profiler\Profiler;

define("DEBUG_MODE", true);

require_once __DIR__ . "/../vendor/autoload.php";

function doSomething()
{
    Profiler::start("Do something");
    $objects = [];
    for ($i = 0; $i < 3; $i++) {
        Profiler::start("Do something - iteration #%d", $i);
        $hugeObject = null;
        for ($j = 0; $j < 1000 * $i; $j++) {
            $hugeObject = new \Exception("Huge object", 0, $hugeObject);
        }
        $objects[] = $hugeObject;
        usleep(10000);
        Profiler::finish("Do something - iteration #%d", $i);
    }
    unset($objects);
    Profiler::finish("Do something");
}
