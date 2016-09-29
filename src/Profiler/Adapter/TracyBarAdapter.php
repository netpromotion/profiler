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
        $table = "<style>.tracy-addons-profiler-hidden{display:none}.tracy-addons-profiler-bar{display:inline-block;margin:0;height:0.8em;}</style>";
        $table .= "<table>";
        $table .= "<tr><td colspan='4' style='height:3.2em'>" . $this->getMemoryChart() . "</td></tr>";
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

    private function getMemoryChart()
    {
        $colors = ["#000000", "#cccccc", "#6ba9e6"];
        $maxWidth = 600;
        $maxHeight = 90;
        $gridStep = 10;
        $memoryChart = "<!--suppress HtmlUnknownAttribute --><svg style='width: 100%' viewBox='0 0 {$maxWidth} {$maxHeight}' xmlns='http://www.w3.org/2000/svg'>";
        for ($tmpY = 0; $tmpY < $maxHeight; $tmpY += $gridStep) {
            $memoryChart .= "<line x1='0' y1='{$tmpY}' x2='{$maxWidth}' y2='{$tmpY}' stroke-width='1' stroke='{$colors[1]}' />";
        }
        for ($tmpX = $gridStep; $tmpX < $maxWidth; $tmpX += $gridStep) {
            $memoryChart .= "<line x1='{$tmpX}' y1='0' x2='{$tmpX}' y2='{$maxHeight}' stroke-width='1' stroke='{$colors[1]}' />";
        }
        $memoryChart .= "<line x1='0' y1='{$maxHeight}' x2='{$maxWidth}' y2='{$maxHeight}' stroke-width='1' stroke='{$colors[0]}' />";
        $memoryChart .= "<line x1='0' y1='0' x2='0' y2='{$maxHeight}' stroke-width='1' stroke='{$colors[0]}' />";

        $prevX = 0;
        $prevY = $maxHeight;
        $this->profilerService->iterateMemoryTimeLine(function ($width, $height, $metaData) use ($colors, &$memoryChart, $maxWidth, $maxHeight, &$prevX, &$prevY) {
            if ($prevX == 0) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $memoryChart .= sprintf(
                    "<text x='5' y='10' font-size='10'>%d kB</text>",
                    floor($metaData[ProfilerService::META_MEMORY_PEAK] / 1024)
                );
            }
            $thisX = floor($prevX + $width * $maxWidth / 100);
            $thisY = floor($maxHeight - $height * $maxHeight / 100);
            $memoryChart .= "<line x1='{$prevX}' y1='{$prevY}' x2='{$thisX}' y2='{$thisY}' stroke-width='1' stroke='{$colors[2]}' />";
            $prevX = $thisX;
            $prevY = $thisY;
        });

        $memoryChart .= "</svg>";

        return $memoryChart;
    }
}
