<?php

use Netpromotion\Profiler\Extension\ProfilerNetteExtension;
use Netpromotion\Profiler\Profiler;
use Nette\Configurator;

require __DIR__ . "/../../vendor/autoload.php";

ProfilerNetteExtension::enable(); // this is required only if you need to profile before container is created

Profiler::start();
{
    $configurator = new Configurator();

    Profiler::start();
    {
        $configurator->setDebugMode(TRUE);
        $configurator->enableDebugger(__DIR__ . "/log");
        $configurator->setTempDirectory(__DIR__ . "/temp");
    }
    Profiler::finish();

    Profiler::start();
    {
        $configurator->addConfig(__DIR__ . "/config/config.neon");
        $container = $configurator->createContainer();
    }
    Profiler::finish();

    Profiler::start();
    {
        $container->getService("application")->run();
    }
    Profiler::finish();
}
Profiler::finish();
