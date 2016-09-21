<?php

namespace Netpromotion\Profiler;

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
     * @var callable[]
     */
    protected static $postProcessors;

    /**
     * @inheritdoc
     */
    public static function setPostProcessor(callable $postProcessor, $postProcessorId = "default")
    {
        static::$postProcessors[$postProcessorId] = $postProcessor;

        $postProcessors = static::$postProcessors;
        parent::setPostProcessor(function (Profile $profile) use ($postProcessors) {
            foreach ($postProcessors as $key => $postProcessor) {
                if ($key !== "default") {
                    $profile = call_user_func($postProcessor, $profile);
                }
            }
            if (isset($postProcessors["default"])) {
                $profile = call_user_func($postProcessors["default"], $profile);
            }
            return $profile;
        });
    }
}
