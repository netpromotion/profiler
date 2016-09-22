<?php

namespace Netpromotion\Profiler\Test;

use Netpromotion\Profiler\Profiler;
use Netpromotion\Profiler\Service\ProfilerService;
use PetrKnap\Php\Profiler\Profile;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testEnableCallsProfilerServiceInit()
    {
        Profiler::enable();

        Profiler::start();
        Profiler::finish();

        /** @noinspection PhpInternalEntityUsedInspection */
        $this->assertCount(1, ProfilerService::getInstance()->getProfiles());
    }
}
