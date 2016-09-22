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

    const TIME_LINE_BEFORE = "time_line_before";
    const TIME_LINE_ACTIVE = "time_line_active";
    const TIME_LINE_INACTIVE = "time_line_inactive";
    const TIME_LINE_AFTER = "time_line_after";

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

    public function iterateProfiles(callable $callback)
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
        $time = max($tN - $t0, 0.001);

        foreach ($this->profiles as $profile) {
            $profile->meta[static::TIME_LINE_BEFORE] = floor(($profile->meta[Profiler::START_TIME] - $t0) / $time * 100);
            $profile->meta[static::TIME_LINE_ACTIVE] = floor($profile->duration / $time * 100);
            $profile->meta[static::TIME_LINE_INACTIVE] = floor(($profile->absoluteDuration - $profile->duration) / $time * 100);
            $profile->meta[static::TIME_LINE_AFTER] = 100 - $profile->meta[static::TIME_LINE_BEFORE] - $profile->meta[static::TIME_LINE_ACTIVE] - $profile->meta[static::TIME_LINE_INACTIVE];

            call_user_func($callback, $profile);
        }
    }
}
