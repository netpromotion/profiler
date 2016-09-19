<?php

namespace Netpromotion\Profiler;

use PetrKnap\Php\Profiler\AdvancedProfiler;

class Profiler extends AdvancedProfiler
{
    public static function isEnabled()
    {
        return self::$enabled;
    }

    public static function setPostProcessor(callable $postProcessor)
    {
        parent::setPostProcessor($postProcessor);
    }
}
