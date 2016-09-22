<?php

namespace Netpromotion\Profiler;

use /** @noinspection PhpInternalEntityUsedInspection */ Netpromotion\Profiler\Service\ProfilerService;
use PetrKnap\Php\Profiler\AdvancedProfiler;
use PetrKnap\Php\Profiler\Profile;

class Profiler extends AdvancedProfiler
{
    /**
     * @var bool
     */
    protected static $enabled = false;

    /**
     * @var Profile[]
     */
    protected static $stack = [];

    /**
     * @var callable
     */
    protected static $postProcessor = null;

    /**
     * @inheritdoc
     */
    public static function enable()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        ProfilerService::init();
        parent::enable();
    }

    /**
     * @inheritdoc
     * @internal
     */
    public static function setPostProcessor(callable $postProcessor)
    {
        parent::setPostProcessor($postProcessor);
    }
}
