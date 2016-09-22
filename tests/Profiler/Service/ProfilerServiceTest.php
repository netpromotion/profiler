<?php

namespace Netpromotion\Profiler\Test\Service;

use Netpromotion\Profiler\Profiler;
use /** @noinspection PhpInternalEntityUsedInspection */ Netpromotion\Profiler\Service\ProfilerService;
use PetrKnap\Php\Profiler\Profile;

class TracyBarAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testAddProfileWorks()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $service = ProfilerService::getInstance();

        $this->assertAttributeCount(0, "profiles", $service);

        for ($i = 0; $i < 5; $i++) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $service->addProfile(new Profile());
        }

        $this->assertAttributeCount(5, "profiles", $service);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetProfilesWorks()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $service = ProfilerService::getInstance();
        for ($i = 0; $i < 5; $i++) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $service->addProfile(new Profile());
        }

        $this->assertCount(5, $service->getProfiles());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIterateProfilesWorks()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $service = ProfilerService::getInstance();
        for ($i = 0; $i < 5; $i++) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $service->addProfile(new Profile());
        }

        $service->iterateProfiles(function() use (&$j) {
            $j++;
        });

        $this->assertEquals(5, $j);
    }

    /**
     * @runInSeparateProcess
     * @dataProvider dataIterateProfilesComputesRightTimeLineValues
     * @param int[][] $times
     * @param int[][] $percentages
     */
    public function testIterateProfilesComputesRightTimeLineValues(array $times, array $percentages)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $service = ProfilerService::getInstance();

        foreach ($times as $time) {
            $profile = new Profile();
            $profile->meta[Profiler::START_TIME] = $time[0];
            $profile->meta[Profiler::FINISH_TIME] = $time[1];
            $profile->duration = $time[2];
            $profile->absoluteDuration = $time[1] - $time[0];

            /** @noinspection PhpInternalEntityUsedInspection */
            $service->addProfile($profile);
        }

        $i = 0;
        $service->iterateProfiles(function(Profile $profile) use ($percentages, &$i) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->assertArraySubset([
                ProfilerService::TIME_LINE_BEFORE => $percentages[$i][0],
                ProfilerService::TIME_LINE_ACTIVE => $percentages[$i][1],
                ProfilerService::TIME_LINE_INACTIVE => $percentages[$i][2],
                ProfilerService::TIME_LINE_AFTER => $percentages[$i][3]
            ], $profile->meta);
            $i++;
        });
        $this->assertEquals(count($times), $i);
    }

    public function dataIterateProfilesComputesRightTimeLineValues()
    {
        return [
            [[[0, 10, 5]], [[0, 50, 50, 0]]],
            [[[0, 10, 5], [10, 20, 5]], [[0, 25, 25, 50], [50, 25, 25, 0]]]
        ];
    }
}
