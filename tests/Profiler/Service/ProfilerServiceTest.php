<?php

namespace Netpromotion\Profiler\Test\Service;

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
     */
    public function testIterateProfilesComputesRightTimeLineValues()
    {
        $this->markTestSkipped();
    }
}
