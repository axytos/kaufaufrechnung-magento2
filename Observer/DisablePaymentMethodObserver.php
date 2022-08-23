<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Observer;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Model\Constants;
use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\Method\Adapter;

class DisablePaymentMethodObserver implements ObserverInterface
{
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private ErrorReportingClientInterface $errorReportingClient;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        ErrorReportingClientInterface $errorReportingClient)
    {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->errorReportingClient = $errorReportingClient;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        try 
        {
            if (!$this->isPaymentMethodIsActiveEvent($observer))
            { 
                return;
            }

            if(!$this->isForAxytosKaufAufRechnungPaymentMethod($observer))
            {
                return;
            }

            $this->setAxytosKaufAufRechnungAvailability($observer);
        } 
        catch (Exception $exception) 
        {
            $this->errorReportingClient->reportError($exception);
        }
    }

    private function isPaymentMethodIsActiveEvent(\Magento\Framework\Event\Observer $observer): bool
    {
        return $observer->getEvent()->getName() === 'payment_method_is_active';
    }

    private function isForAxytosKaufAufRechnungPaymentMethod(\Magento\Framework\Event\Observer $observer): bool
    {
        /** 
         * @var Adapter
         * @phpstan-ignore-next-line because getMethodInstance() is invoked via DataObject::__call
         */
        $methodInstance = $observer->getEvent()->getMethodInstance();
        return $methodInstance->getCode() === Constants::PAYMENT_METHOD_CODE;
    }

    private function setAxytosKaufAufRechnungAvailability(\Magento\Framework\Event\Observer $observer): void
    {
        $isAvailable = !$this->pluginConfigurationValidator->isInvalid();
        
        /** 
         * @var DataObject
         * @phpstan-ignore-next-line because getResult() is invoked via DataObject::__call
         */
        $eventResult = $observer->getEvent()->getResult();
        $eventResult->setData('is_available', $isAvailable);
    }
}