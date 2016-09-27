<?php

namespace Netpromotion\Profiler\Service;

use Netpromotion\Profiler\Profiler;
use PetrKnap\Php\Profiler\Profile;
use PetrKnap\Php\Singleton\SingletonInterface;
use PetrKnap\Php\Singleton\SingletonTrait;

/**
 * @internal
 */
class ProfilerService implements SingletonInterface
{
    use SingletonTrait;

    const META_TIME_ZERO = "meta_time_zero";
    const META_TIME_TOTAL = "meta_time_total";
    const META_MEMORY_PEAK = "meta_memory_peak";
    const META_TIME_LINE = "meta_time_line";
    const META_TIME_LINE__MEMORY_USAGE = "meta_time_line__memory_usage";

    const TIME_LINE_BEFORE = "time_line_before"; // int [0 - 100] percentage
    const TIME_LINE_ACTIVE = "time_line_active"; // int [0 - 100] percentage
    const TIME_LINE_INACTIVE = "time_line_inactive"; // int [0 - 100] percentage
    const TIME_LINE_AFTER = "time_line_after"; // int [0 - 100] percentage

    /**
     * @var mixed[]
     */
    private $metaData = [];

    /**
     * @var Profile[]
     */
    private $profiles = [];

    private function __construct()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        Profiler::setPostProcessor([$this, "addProfile"]);
    }

    /**
     * Initializes the service
     *
     * @return void
     */
    public static function init()
    {
        static::getInstance();
    }

    /**
     * @param Profile $profile
     * @return Profile
     * @internal
     */
    public function addProfile(Profile $profile)
    {
        $this->metaData = [];
        $this->profiles[] = $profile;
        return $profile;
    }

    /**
     * @return Profile[]
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    private function getMetaData()
    {
        if (empty($this->metaData)) {
            $this->metaData[self::META_TIME_LINE] = [];
            $this->metaData[self::META_MEMORY_PEAK] = 0;
            if (count($this->profiles) == 0) {
                $this->metaData[self::META_TIME_ZERO] = 0;
                $timeEnd = 0;
            } else {
                $this->metaData[self::META_TIME_ZERO] = $this->profiles[0]->meta[Profiler::START_TIME];
                $timeEnd = $this->profiles[0]->meta[Profiler::FINISH_TIME];
                foreach ($this->profiles as $profile) {
                    $this->metaData[self::META_TIME_ZERO] = min(
                        $this->metaData[self::META_TIME_ZERO],
                        $profile->meta[Profiler::START_TIME]
                    );
                    $timeEnd = max(
                        $timeEnd,
                        $profile->meta[Profiler::FINISH_TIME]
                    );
                    $this->metaData[self::META_MEMORY_PEAK] = max(
                        $this->metaData[self::META_MEMORY_PEAK],
                        $profile->meta[Profiler::START_MEMORY_USAGE],
                        $profile->meta[Profiler::FINISH_MEMORY_USAGE]
                    );
                    $this->metaData[self::META_TIME_LINE][$profile->meta[Profiler::START_TIME]] = [
                        self::META_TIME_LINE__MEMORY_USAGE => $profile->meta[Profiler::START_MEMORY_USAGE]
                    ];
                    $this->metaData[self::META_TIME_LINE][$profile->meta[Profiler::FINISH_TIME]] = [
                        self::META_TIME_LINE__MEMORY_USAGE => $profile->meta[Profiler::FINISH_MEMORY_USAGE]
                    ];
                }
            }
            $this->metaData[self::META_TIME_TOTAL] = max($timeEnd - $this->metaData[self::META_TIME_ZERO], 0.001);
            ksort($this->metaData[self::META_TIME_LINE]);
        }

        return $this->metaData;
    }

    public function iterateProfiles(callable $callback)
    {
        $metaData = $this->getMetaData();
        foreach ($this->profiles as $profile) {
            $profile->meta[static::TIME_LINE_BEFORE] = floor(
                ($profile->meta[Profiler::START_TIME] - $metaData[self::META_TIME_ZERO]) / $this->metaData[self::META_TIME_TOTAL] * 100
            );
            $profile->meta[static::TIME_LINE_ACTIVE] = floor(
                $profile->duration / $this->metaData[self::META_TIME_TOTAL] * 100
            );
            $profile->meta[static::TIME_LINE_INACTIVE] = floor(
                ($profile->absoluteDuration - $profile->duration) / $this->metaData[self::META_TIME_TOTAL] * 100
            );
            $profile->meta[static::TIME_LINE_AFTER] = 100 - $profile->meta[static::TIME_LINE_BEFORE] - $profile->meta[static::TIME_LINE_ACTIVE] - $profile->meta[static::TIME_LINE_INACTIVE];

            call_user_func($callback, $profile);
        }
    }

    public function iterateMemoryTimeLine(callable $callback)
    {
        $metaData = $this->getMetaData();
        $total = 0;
        foreach ($metaData[self::META_TIME_LINE] as $time => $values) {
            $width = floor(
                $time / $this->metaData[self::META_TIME_TOTAL] * 100
            );
            $height = floor(
                $values[self::META_TIME_LINE__MEMORY_USAGE] / $this->metaData[self::META_MEMORY_PEAK] * 100
            );
            $total += $width;
            call_user_func($callback, $width, $height);
        }
        if ($total < 100) {
            call_user_func($callback, 100 - $total, 0);
        }
    }
}
