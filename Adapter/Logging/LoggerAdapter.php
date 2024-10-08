<?php

namespace Axytos\KaufAufRechnung\Adapter\Logging;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Logging\LoggerAdapter as LoggingLoggerAdapter;

class LoggerAdapter implements LoggerAdapterInterface
{
    /**
     * @var LoggingLoggerAdapter
     */
    private $logger;

    /**
     * @return void
     */
    public function __construct(LoggingLoggerAdapter $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function error($message)
    {
        $this->logger->error($message);
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function warning($message)
    {
        $this->logger->warning($message);
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function info($message)
    {
        $this->logger->info($message);
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function debug($message)
    {
        $this->logger->debug($message);
    }
}
