<?php

namespace Netpromotion\Profiler\Extension;

use Netpromotion\Profiler\Profiler;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

class ProfilerNetteExtension extends CompilerExtension
{
    const PROFILER = "Netpromotion\\Profiler\\Profiler";
    const TRACY_BAR_ADAPTER = "Netpromotion\\Profiler\\Adapter\\TracyBarAdapter";

    const CONFIG_PROFILE = "profile";
    const CONFIG_PROFILE_CREATE_SERVICE = "createService";

    private $defaultConfig = [
        self::CONFIG_PROFILE => [
            self::CONFIG_PROFILE_CREATE_SERVICE => false
        ]
    ];

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
            $this->validateConfig($this->defaultConfig);

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
            $__construct = $class->getMethod("__construct");
            $__construct->setBody(sprintf(
                "%s::enable();%s",
                self::PROFILER,
                $__construct->getBody()
            ));

            if ($this->config[self::CONFIG_PROFILE][self::CONFIG_PROFILE_CREATE_SERVICE]) {
                foreach ($class->getMethods() as $method) {
                    if (preg_match('/^createService/', $method->getName())) {
                        $createService = &$method;
                        $createService->setBody(sprintf(
                            "%s::start(__METHOD__);%s%s::finish(__METHOD__);return \$return;",
                            self::PROFILER,
                            str_replace("return ", "\$return = ", $createService->getBody()),
                            self::PROFILER
                        ));
                    }
                }
            }
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
