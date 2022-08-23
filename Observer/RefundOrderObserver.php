<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Observer;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory;
use Axytos\KaufAufRechnung\Model\Constants;
use Error;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;

class RefundOrderObserver implements ObserverInterface
{
    private InvoiceClientInterface $invoiceClient;
    private InvoiceOrderContextFactory $invoiceOrderContextFactory;
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private ErrorReportingClientInterface $errorReportingClient;

    function __construct(
        InvoiceClientInterface $invoiceClient,
        InvoiceOrderContextFactory $invoiceOrderContextFactory,
        PluginConfigurationValidator $pluginConfigurationValidator,
        ErrorReportingClientInterface $errorReportingClient)
    {
        $this->invoiceClient = $invoiceClient;
        $this->invoiceOrderContextFactory = $invoiceOrderContextFactory;
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->errorReportingClient = $errorReportingClient;
    }
    
    function execute(Observer $observer): void
    {
        try
        {
            /** @var Creditmemo */
            $creditmemo = $observer->getDataByKey("creditmemo");
            $order = $creditmemo->getOrder();

            if (is_null($order->getPayment()) || $order->getPayment()->getMethod() !== Constants::PAYMENT_METHOD_CODE)
            {
                return;
            }

            if($this->pluginConfigurationValidator->isInvalid())
            {
                return;
            }
            
            $context = $this->invoiceOrderContextFactory->getInvoiceOrderContext($order, null, $creditmemo);
            $this->invoiceClient->refund($context);
        }
        catch (Exception $exception) 
        {
            $this->errorReportingClient->reportError($exception);
        }
    }
}
