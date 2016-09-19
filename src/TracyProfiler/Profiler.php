<?php

namespace Netorg\Common\Debug;

use PetrKnap\Php\Profiler\AdvancedProfiler;
use PetrKnap\Php\Profiler\Profile;
use Tracy\IBarPanel;

class TracyProfiler extends AdvancedProfiler implements IBarPanel
{
    /**
     * @var Profile[]
     */
    private $profiles = array();

    public function __construct()
    {
        $me = $this;
        self::setPostProcessor(function (Profile $profile) use ($me) {
            $me->profiles[] = $profile;
            return $profile;
        });
    }

    /**
     * @inheritdoc
     */
    public function getTab()
    {
        $countOfProfiles = count($this->profiles);
        return sprintf(
            "<span title='%s'>⏱ %s</span>",
            "Profiler info",
            self::$enabled ? sprintf(
                $countOfProfiles == 1 ? "%d profile" : "%d profiles",
                $countOfProfiles
            ) : "disabled"
        );
    }

    /**
     * @inheritdoc
     */
    public function getPanel()
    {
        $table = "<table>";
        $table .= "<tr><th>Start</th><th>Finish</th><th>Time (absolute)</th><th>Memory change (absolute)</th></tr>";
        foreach ($this->profiles as $profile) {
            $table .= sprintf(
                "<tr><td>%s</td><td>%s</td><td>%d&nbsp;ms (%d&nbsp;ms)</td><td>%d&nbsp;kB (%d&nbsp;kB)</td></tr>",
                $profile->meta[self::START_LABEL],
                $profile->meta[self::FINISH_LABEL],
                $profile->duration * 1000,
                $profile->absoluteDuration * 1000,
                $profile->memoryUsageChange / 1024,
                $profile->absoluteMemoryUsageChange / 1024
            );
        }
        $table .= "</table>";
        return sprintf(
            "<h1>Profiler info</h1><div class='tracy-inner'>%s</div>",
            self::$enabled ? $table : "Profiling is disabled."
        );
    }
}
