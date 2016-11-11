<?php

namespace Netpromotion\Profiler\Adapter;

use Netpromotion\Profiler\Profiler;
use /** @noinspection PhpInternalEntityUsedInspection */ Netpromotion\Profiler\Service\ProfilerService;
use PetrKnap\Php\Profiler\Profile;
use Psr\Log\LoggerInterface;

class PsrLoggerAdapter
{
    /**
     * @var ProfilerService
     */
    private $service;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $this->service = ProfilerService::getInstance();
        $this->logger = $logger;
    }

    /**
     * Logs known profiles
     *
     * @return void
     */
    public function log()
    {
        if (Profiler::isEnabled()) {
            $this->service->iterateProfiles(function (Profile $profile) {
                $this->logger->debug(sprintf(
                    "%s -> %s: %d ms, %d kB",
                    $profile->meta[Profiler::START_LABEL],
                    $profile->meta[Profiler::FINISH_LABEL],
                    $profile->duration * 1000,
                    $profile->memoryUsageChange / 1024
                ), $profile->jsonSerialize());
            });
        }
    }
}
