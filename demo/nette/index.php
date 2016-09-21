<?php

use Netpromotion\Profiler\Extension\ProfilerNetteExtension;
use Netpromotion\Profiler\Profiler;
use Nette\Configurator;

require __DIR__ . "/../../vendor/autoload.php";

ProfilerNetteExtension::enable(); // this is required only if you need to profile before container is created

Profiler::start("Demo application");

Profiler::start("Configure application");
$configurator = new Configurator();
$configurator->setDebugMode(TRUE);
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
