<?php

namespace Netpromotion\Profiler\Extension;

use Netpromotion\Profiler\Profiler;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

class ProfilerNetteExtension extends CompilerExtension
{
    const PROFILER = "Netpromotion\\Profiler\\Profiler";
    const TRACY_BAR_ADAPTER = "Netpromotion\\Profiler\\Adapter\\TracyBarAdapter";

    /**
     * @internal
     * @return bool
     */
    private static function isActive()
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        if (!class_exists("Tracy\\Debugger") || \Tracy\Debugger::$productionMode === TRUE) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function loadConfiguration()
    {
        if (self::isActive()) {
            $builder = $this->getContainerBuilder();
            $builder
                ->addDefinition($this->prefix("panel"))
                ->setClass(self::TRACY_BAR_ADAPTER);
            $builder
                ->getDefinition("tracy.bar")
                ->addSetup("addPanel", ["@" . $this->prefix("panel")]);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterCompile(ClassType $class)
    {
        if (self::isActive()) {
            $method = $class->getMethod("__construct");
            $method->setBody(sprintf(
                "%s::enable();%s",
                self::PROFILER,
                $method->getBody()
            ));
        }
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
