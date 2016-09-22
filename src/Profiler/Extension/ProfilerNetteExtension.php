<?php

namespace Netpromotion\Profiler\Extension;

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
            ->setClass(self::TRACY_BAR_ADAPTER);
        $builder
            ->getDefinition("tracy.bar")
            ->addSetup("addPanel", ["@" . $this->prefix("panel")]);

        Profiler::enable();
    }

    /**
     * TODO remove in version 2
     *
     * @deprecated will be removed in version 2
     */
    public static function enable()
    {
        Profiler::enable();
    }
}
