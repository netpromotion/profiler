<?php

namespace Netpromotion\Profiler\Test;

use Netpromotion\Profiler\Profiler;
use PetrKnap\Php\Profiler\Profile;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testMultipleHooksWorks()
    {
        $order = [];
        Profiler::setPostProcessor(function (Profile $profile) use (&$order) {
            $order[] = 1;
            return $profile;
        });

        Profiler::setPostProcessor(function (Profile $profile) use (&$order) {
            $order[] = 2;
            return $profile;
        });

        Profiler::setPostProcessor(function (Profile $profile) use (&$order) {
            $order[] = 3;
            return $profile;
        }, "B");

        Profiler::setPostProcessor(function (Profile $profile) use (&$order) {
            $order[] = 4;
            return $profile;
        }, "A");

        Profiler::enable();
        Profiler::start();
        Profiler::finish();

        $this->assertEquals([3, 4, 2], $order);
    }
}
