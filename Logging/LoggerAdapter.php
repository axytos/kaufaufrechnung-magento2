<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Logging;

use Axytos\ECommerce\Logging\LoggerAdapterInterface;
use Psr\Log\LoggerInterface;

class LoggerAdapter implements LoggerAdapterInterface
{
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    function error(string $message): void
    {
        $this->logger->error($message);
    }

    function warning(string $message): void
    {
        $this->logger->warning($message);
    }

    function info(string $message): void
    {
        $this->logger->info($message);
    }

    function debug(string $message): void
    {
        $this->logger->debug($message);
    }
}