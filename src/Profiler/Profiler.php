<?php

namespace Netpromotion\Profiler;

use PetrKnap\Php\Profiler\AdvancedProfiler;
use PetrKnap\Php\Profiler\Profile;

class Profiler extends AdvancedProfiler
{
    private static $postProcessors;

    public static function setPostProcessor(callable $postProcessor)
    {
        $postProcessorId = func_get_arg(1);
        self::$postProcessors[$postProcessorId] = $postProcessor;

        $postProcessors = self::$postProcessors;
        parent::setPostProcessor(function (Profile $profile) use ($postProcessors) {
            foreach ($postProcessors as $postProcessor) {
                $profile = call_user_func($postProcessor, $profile);
            }
            return $profile;
        });
    }
}
