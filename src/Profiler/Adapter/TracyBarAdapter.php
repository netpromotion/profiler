<?php

namespace Netpromotion\Profiler\Adapter;

use Netpromotion\Profiler\Profiler;
use /** @noinspection PhpInternalEntityUsedInspection */ Netpromotion\Profiler\Service\ProfilerService;
use PetrKnap\Php\Profiler\Profile;
use Tracy\IBarPanel;

class TracyBarAdapter implements IBarPanel
{
    private $profilerService;

    public function __construct()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->profilerService = ProfilerService::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getTab()
    {
        $countOfProfiles = count($this->profilerService->getProfiles());
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
        $table = "<style>.tracy-addons-profiler-hidden{display:none}.tracy-addons-profiler-bar,.tracy-addons-profiler-chart{display:inline-block;margin:0;height:0.8em;}</style>";
        $table .= "<table>";

        $memoryChart = "";
        $this->profilerService->iterateMemoryTimeLine(function ($width, $height) use (&$memoryChart) {
            $memoryChart .= sprintf(
                "<span class='tracy-addons-profiler-chart' style='width:%d%%;height:%d%%;background-color:#6ba9e6;'></span>",
                $width,
                $height
            );
        });
        $table .= "<tr><td colspan='4' style='height:3.2em'>" . $memoryChart . "</td></tr>";

        $table .= "<tr><th>Start</th><th>Finish</th><th>Time (absolute)</th><th>Memory change (absolute)</th></tr>";
        $this->profilerService->iterateProfiles(function (Profile $profile) use (&$table) {
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

            /** @noinspection PhpInternalEntityUsedInspection */
            $table .= sprintf(
                "<tr class='tracy-addons-profiler-hidden'><td colspan='4'></td></tr><tr><td colspan='4'>" .
                "<span class='tracy-addons-profiler-bar' style='width:%d%%;background-color:#cccccc;'></span>" .
                "<span class='tracy-addons-profiler-bar' style='width:%d%%;background-color:#3987d4;'></span>" .
                "<span class='tracy-addons-profiler-bar' style='width:%s%%;background-color:#6ba9e6;'></span>" .
                "<span class='tracy-addons-profiler-bar' style='width:%s%%;background-color:#cccccc;'></span>" .
                "</td></tr>",
                $profile->meta[ProfilerService::TIME_LINE_BEFORE],
                $profile->meta[ProfilerService::TIME_LINE_ACTIVE],
                $profile->meta[ProfilerService::TIME_LINE_INACTIVE],
                $profile->meta[ProfilerService::TIME_LINE_AFTER]
            );
        });

        $table .= "</table>";

        return sprintf(
            "<h1>Profiler info</h1><div class='tracy-inner'>%s</div>",
            Profiler::isEnabled() ? $table : "Profiling is disabled."
        );
    }
}
