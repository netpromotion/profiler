<?php

namespace Netpromotion\Profiler\Adapter;

use Netpromotion\Profiler\Profiler;
use /** @noinspection PhpInternalEntityUsedInspection */ Netpromotion\Profiler\Service\ProfilerService;
use PetrKnap\Php\Profiler\Profile;
use Tracy\IBarPanel;

class TracyBarAdapter implements IBarPanel
{
    const CONFIG_SHOW = "show";
    const CONFIG_SHOW_MEMORY_USAGE_CHART = "memoryUsageChart";

    private $profilerService;

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        /** @noinspection PhpInternalEntityUsedInspection */
        $this->profilerService = ProfilerService::getInstance();
    }

    /**
     * @internal
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            self::CONFIG_SHOW => [
                self::CONFIG_SHOW_MEMORY_USAGE_CHART => false
            ]
        ];
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
        if ($this->config[self::CONFIG_SHOW][self::CONFIG_SHOW_MEMORY_USAGE_CHART]) {
            $table .= "<tr><td colspan='4' style='text-align: center'>" . $this->getMemoryChart() . "</td></tr>";
        }
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
        $colors = [
            "axis" => "#000000",
            "gridLines" => "#cccccc",
            "memoryUsage" => "#6ba9e6",
            "memoryUsagePoint" => "#3987d4"
        ];
        $margin = 3;
        $maxWidth = 600 - 2 * $margin;
        $maxHeight = 90 - 2 * $margin;
        $gridStep = 10;
        $memoryChart = sprintf(
            "<!--suppress HtmlUnknownAttribute --><svg style='width: %dpx; height: %dpx' viewBox='0 0 %d %d' xmlns='http://www.w3.org/2000/svg'>",
            $maxWidth + 2 * $margin,
            $maxHeight + 2 * $margin,
            $maxWidth + 2 * $margin,
            $maxHeight + 2 * $margin
        );
        for ($tmpY = $maxHeight; $tmpY > 0; $tmpY -= $gridStep) {
            $memoryChart .= sprintf(
                "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />",
                $margin,
                $tmpY + $margin,
                $maxWidth + $margin,
                $tmpY + $margin,
                $colors["gridLines"]
            );
        }
        for ($tmpX = $gridStep; $tmpX < $maxWidth; $tmpX += $gridStep) {
            $memoryChart .= sprintf(
                "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />",
                $tmpX + $margin,
                $margin,
                $tmpX + $margin,
                $maxHeight + $margin,
                $colors["gridLines"]
            );
        }

        $prevX = 0;
        $prevY = $maxHeight;
        $lines = "";
        $points = "";
        $this->profilerService->iterateMemoryTimeLine(function ($time, $height, $metaData) use ($colors, &$memoryChart, $maxWidth, $maxHeight, $margin, &$prevX, &$prevY, &$lines, &$points) {
            if ($prevX == 0) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $memoryChart .= sprintf(
                    "<text x='%d' y='%d' font-size='%d'>%d kB</text>",
                    $margin * 2,
                    10 / 2 + $margin * 2,
                    10,
                    floor($metaData[ProfilerService::META_MEMORY_PEAK] / 1024)
                );
            }
            /** @noinspection PhpInternalEntityUsedInspection */
            $thisX = floor($time / $metaData[ProfilerService::META_TIME_TOTAL] * $maxWidth);
            $thisY = floor($maxHeight - $height * $maxHeight / 100);
            $lines .= sprintf(
                "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />",
                $prevX + $margin,
                $prevY + $margin,
                $thisX + $margin,
                $thisY + $margin,
                $colors["memoryUsage"]
            );
            $points .= sprintf(
                "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />".
                "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />",
                $thisX + $margin,
                $thisY + $margin - 3,
                $thisX + $margin,
                $thisY + $margin + 3,
                $colors["memoryUsagePoint"],
                $thisX + $margin - 3,
                $thisY + $margin,
                $thisX + $margin + 3,
                $thisY + $margin,
                $colors["memoryUsagePoint"]
            );
            $prevX = $thisX;
            $prevY = $thisY;
        });

        $memoryChart .= $lines . $points;

        $memoryChart .= sprintf(
            "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />",
            $margin,
            $maxHeight + $margin,
            $maxWidth + $margin,
            $maxHeight + $margin,
            $colors["axis"]
        );
        $memoryChart .= sprintf(
            "<line x1='%d' y1='%d' x2='%d' y2='%d' stroke-width='1' stroke='%s' />",
            $margin,
            $margin,
            $margin,
            $maxHeight + $margin,
            $colors["axis"]
        );

        $memoryChart .= "</svg>";

        return $memoryChart;
    }
}
