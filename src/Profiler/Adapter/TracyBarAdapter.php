<?php

namespace Netpromotion\Profiler\Adapter;

use Netpromotion\Profiler\Profiler;
use PetrKnap\Php\Profiler\Profile;
use Tracy\IBarPanel;

class TracyBarAdapter implements IBarPanel
{
    /**
     * @var Profile[]
     */
    private $profiles = array();

    private function __construct()
    {
        $me = $this;
        Profiler::setPostProcessor(function (Profile $profile) use ($me) {
            $me->profiles[] = $profile;
            return $profile;
        }, __CLASS__);
    }

    /**
     * @return self
     */
    public static function create()
    {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
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
            Profiler::isEnabled() ? sprintf(
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
        if (count($this->profiles) == 0) {
            $t0 = 0;
            $tN = 0;
        } else {
            $t0 = $this->profiles[0]->meta[Profiler::START_TIME];
            $tN = $this->profiles[0]->meta[Profiler::FINISH_TIME];
            foreach ($this->profiles as $profile) {
                $t0 = min($t0, $profile->meta[Profiler::START_TIME]);
                $tN = max($tN, $profile->meta[Profiler::FINISH_TIME]);
            }
        }
        $time = $tN - $t0;

        $table = "<style>.nette-addons-profiler-hidden{display:none}.nette-addons-profiler-bar{display:inline-block;margin:0;height:0.8em;}</style>";
        $table .= "<table>";
        $table .= "<tr><th>Start</th><th>Finish</th><th>Time (absolute)</th><th>Memory change (absolute)</th></tr>";
        foreach ($this->profiles as $profile) {
            if ($profile->meta[Profiler::START_LABEL] == $profile->meta[Profiler::FINISH_LABEL]) {
                $labels = sprintf(
                    "<td colspan='2'>%s</td>",
                    $profile->meta[Profiler::START_LABEL],
                    $profile->meta[Profiler::FINISH_LABEL]
                );
            } else {
                $labels = sprintf(
                    "<td>%s</td><td>%s</td>",
                    $profile->meta[Profiler::START_LABEL],
                    $profile->meta[Profiler::FINISH_LABEL]
                );
            }
            $table .= sprintf(
                "<tr>%s<td>%d&nbsp;ms (%d&nbsp;ms)</td><td>%d&nbsp;kB (%d&nbsp;kB)</td></tr>",
                $labels,
                $profile->duration * 1000,
                $profile->absoluteDuration * 1000,
                $profile->memoryUsageChange / 1024,
                $profile->absoluteMemoryUsageChange / 1024
            );

            $before = floor(($profile->meta[Profiler::START_TIME] - $t0) / $time * 100);
            $activeTime = floor($profile->duration / $time * 100);
            $inactiveTime = floor(($profile->absoluteDuration - $profile->duration) / $time * 100);
            $after = 100 - $before - $activeTime - $inactiveTime;

            $table .= sprintf(
                "<tr class='nette-addons-profiler-hidden'><td colspan='4'></td></tr><tr><td colspan='4'>" .
                "<span class='nette-addons-profiler-bar' style='width:%d%%;background-color:#cccccc;'></span>" .
                "<span class='nette-addons-profiler-bar' style='width:%d%%;background-color:#3987d4;'></span>" .
                "<span class='nette-addons-profiler-bar' style='width:%s%%;background-color:#6ba9e6;'></span>" .
                "<span class='nette-addons-profiler-bar' style='width:%s%%;background-color:#cccccc;'></span>" .
                "</td></tr>",
                $before,
                $activeTime,
                $inactiveTime,
                $after
            );
        }
        $table .= "</table>";
        return sprintf(
            "<h1>Profiler info</h1><div class='nette-inner'>%s</div>",
            Profiler::isEnabled() ? $table : "Profiling is disabled."
        );
    }
}
