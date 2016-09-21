<?php

namespace Netpromotion\Profiler\Extension;

use Netpromotion\Profiler\Adapter\TracyBarAdapter;
use Netpromotion\Profiler\Profiler;
use Nette\DI\CompilerExtension;
use Tracy\Debugger;

class ProfilerNetteExtension extends CompilerExtension
{
    const TRACY_BAR_ADAPTER = "Netpromotion\\Profiler\\Adapter\\TracyBarAdapter";

    public function loadConfiguration()
    {
        if (!class_exists("Tracy\\Debugger") || Debugger::$productionMode === TRUE) {
            return;
        }

        $builder = $this->getContainerBuilder();
        $builder
            ->addDefinition($this->prefix("panel"))
            ->setClass(self::TRACY_BAR_ADAPTER)
            ->setFactory(self::TRACY_BAR_ADAPTER . "::create");
        $builder
            ->getDefinition("tracy.bar")
            ->addSetup("addPanel", ["@" . $this->prefix("panel")]);

        Profiler::enable();
    }

    public static function enable()
    {
        TracyBarAdapter::create();
        Profiler::enable();
    }
}
