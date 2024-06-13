<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Observer;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory;
use Axytos\KaufAufRechnung\Model\Constants;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;

class ShipOrderObserver implements ObserverInterface
{
    /**
     * @var \Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface
     */
    private $invoiceClient;
    /**
     * @var \Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory
     */
    private $invoiceOrderContextFactory;
    /**
     * @var \Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;
    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface
     */
    private $errorReportingClient;

    public function __construct(
        InvoiceClientInterface $invoiceClient,
        InvoiceOrderContextFactory $invoiceOrderContextFactory,
        PluginConfigurationValidator $pluginConfigurationValidator,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->invoiceClient = $invoiceClient;
        $this->invoiceOrderContextFactory = $invoiceOrderContextFactory;
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->errorReportingClient = $errorReportingClient;
    }

    public function execute(Observer $observer): void
    {
        try {
            /** @var Shipment */
            $shipment = $observer->getDataByKey("shipment");
            $order = $shipment->getOrder();

            if (is_null($order->getPayment()) || $order->getPayment()->getMethod() !== Constants::PAYMENT_METHOD_CODE) {
                return;
            }

            if ($this->pluginConfigurationValidator->isInvalid()) {
                return;
            }

            $context = $this->invoiceOrderContextFactory->getInvoiceOrderContext($order, $shipment);
            $this->invoiceClient->reportShipping($context);
        } catch (Exception $exception) {
            $this->errorReportingClient->reportError($exception);
        }
    }
}
