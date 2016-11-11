<?php

namespace Netpromotion\Profiler\Test\Adapter;

use Netpromotion\Profiler\Adapter\PsrLoggerAdapter;
use Netpromotion\Profiler\Profiler;

class PsrLoggerAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataAdapterWorks
     * @param bool $enabled
     * @runInSeparateProcess
     */
    public function testAdapterWorks($enabled)
    {
        $logger = $this->getMock("Psr\\Log\\LoggerInterface");
        $logger->expects($this->exactly($enabled ? 6 : 0))
            ->method("debug")->willReturn(null);
        $adapter = new PsrLoggerAdapter($logger);

        if ($enabled) {
            Profiler::enable();
        }

        Profiler::start("1");
        {
            Profiler::start("1.1");
            {
                Profiler::start("1.1.1");
                Profiler::finish("1.1.1");
                Profiler::start("1.1.2");
                Profiler::finish("1.1.2");
            }
            Profiler::finish("1.1");
            Profiler::start("1.2");
            Profiler::finish("1.2");
        }
        Profiler::finish("1");
        Profiler::start("2");
        Profiler::finish("2");

        $adapter->log();
    }

    public function dataAdapterWorks()
    {
        return [[false], [true]];
    }
}
