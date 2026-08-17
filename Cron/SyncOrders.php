<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Cron;

use Axytos\KaufAufRechnung\Core\OrderSyncWorker;
use Magento\Framework\App\AreaList;
use Psr\Log\LoggerInterface;

class SyncOrders
{
    public function __construct(
        private OrderSyncWorker $orderSyncWorker,
        private LoggerInterface $logger,
        private AreaList $areaList
    ) {
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            // Ensure admin area für WebAPI-Abhängigkeiten
            $this->areaList->getArea(\Magento\Framework\App\Area::AREA_GLOBAL);

            $this->logger->info('Axytos Order Sync starting...');

            // Sync Orders in CHECKOUT_CONFIRMED, INVOICED, CANCELED States
            // batchSize: 100 = verarbeitet bis zu 100 Orders pro Durchlauf
            $nextToken = $this->orderSyncWorker->sync(null, 100, null);

            if ('' !== $nextToken) {
                // Pagination: Mehr Orders warten auf Verarbeitung
                $this->logger->info(
                    "Axytos Order Sync: More orders pending. Next token: {$nextToken}"
                );
            } else {
                $this->logger->info('Axytos Order Sync completed successfully.');
            }
        } catch (\Throwable $e) {
            $this->logger->critical("Axytos Order Sync failed: {$e->getMessage()}");
            $this->logger->critical($e->getTraceAsString());

            // Exception nicht werfen, sonst blockiert der Cron
            // Fehler wird trotzdem geloggt
        }
    }
}
