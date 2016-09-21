<?php

use Netpromotion\Profiler\Extension\ProfilerNetteExtension;
use Netpromotion\Profiler\Profiler;
use Nette\Configurator;

require_once __DIR__ . "/../require_me.php";

if (DEBUG_MODE === true) {
    ProfilerNetteExtension::enable(); // this is required only if you need to profile before container is created
}

Profiler::start("Demo application");

Profiler::start("Configure application");
$configurator = new Configurator();
if (DEBUG_MODE === true) {
    $configurator->setDebugMode(true);
}
$configurator->enableDebugger(__DIR__ . "/log");
$configurator->setTempDirectory(__DIR__ . "/temp");
$configurator->addConfig(__DIR__ . "/config/config.neon");
Profiler::finish("Configure application");

Profiler::start("Create container");
$container = $configurator->createContainer();
Profiler::finish("Create container");

Profiler::start("Run");
$container->getService("application")->run();
Profiler::finish("Run");

Profiler::finish("Demo application");
