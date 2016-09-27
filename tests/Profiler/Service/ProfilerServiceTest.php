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

    /**
     * @runInSeparateProcess
     * @dataProvider dataIterateMemoryTimeLineComputesCorrectValues
     * @param int[][] $input
     * @param int[] $expected
     */
    public function testIterateMemoryTimeLineComputesCorrectValues(array $input, array $expected)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $service = ProfilerService::getInstance();

        foreach ($input as $profile) {
            $times = array_keys($profile);
            $memory = array_values($profile);
            $profile = new Profile();
            $profile->meta[Profiler::START_TIME] = $times[0];
            $profile->meta[Profiler::FINISH_TIME] = $times[1];
            $profile->meta[Profiler::START_MEMORY_USAGE] = $memory[0];
            $profile->meta[Profiler::FINISH_MEMORY_USAGE] = $memory[1];

            /** @noinspection PhpInternalEntityUsedInspection */
            $service->addProfile($profile);
        }

        $i = 0;
        $widths = array_keys($expected);
        $heights = array_values($expected);
        $service->iterateMemoryTimeLine(function($width, $height) use ($widths, $heights, &$i) {
            $this->assertEquals($widths[$i], $width);
            $this->assertEquals($heights[$i], $height);
            $i++;
        });
        $this->assertEquals(count($expected), $i);
    }

    public function dataIterateMemoryTimeLineComputesCorrectValues()
    {
        return [
            [[[0 => 0, 10 => 10]], [0 => 0, 100 => 100]],
            [[[0 => 1, 10 => 10], [4 => 6, 6 => 4]], [0 => 10, 40 => 60, 60 => 40, 100 => 100]],
        ];
    }
}
