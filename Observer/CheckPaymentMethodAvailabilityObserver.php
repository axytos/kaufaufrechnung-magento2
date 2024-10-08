<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Observer;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Axytos\KaufAufRechnung\Model\Constants;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\CartTotalRepositoryInterface;

class CheckPaymentMethodAvailabilityObserver implements ObserverInterface
{
    /**
     * @var PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;
    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;
    /**
     * @var PluginConfiguration
     */
    private $pluginConfiguration;
    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        ErrorReportingClientInterface $errorReportingClient,
        PluginConfiguration $pluginConfiguration,
        CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->errorReportingClient = $errorReportingClient;
        $this->pluginConfiguration = $pluginConfiguration;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        try {
            if (!$this->isPaymentMethodIsActiveEvent($observer)) {
                return;
            }

            if (!$this->isForAxytosKaufAufRechnungPaymentMethod($observer)) {
                return;
            }

            $this->setAxytosKaufAufRechnungAvailability($observer);
        } catch (\Exception $exception) {
            $this->errorReportingClient->reportError($exception);
        }
    }

    private function isPaymentMethodIsActiveEvent(\Magento\Framework\Event\Observer $observer): bool
    {
        return 'payment_method_is_active' === $observer->getEvent()->getName();
    }

    private function isForAxytosKaufAufRechnungPaymentMethod(\Magento\Framework\Event\Observer $observer): bool
    {
        /**
         * @var Adapter
         *
         * @phpstan-ignore-next-line because getMethodInstance() is invoked via DataObject::__call
         */
        $methodInstance = $observer->getEvent()->getMethodInstance();

        return Constants::PAYMENT_METHOD_CODE === $methodInstance->getCode();
    }

    private function setAxytosKaufAufRechnungAvailability(\Magento\Framework\Event\Observer $observer): void
    {
        $isAvailable = $this->isAxytosKaufAufRechnungAvailable($observer);

        /**
         * @var DataObject
         *
         * @phpstan-ignore-next-line because getResult() is invoked via DataObject::__call
         */
        $eventResult = $observer->getEvent()->getResult();
        $eventResult->setData('is_available', $isAvailable);
    }

    private function isAxytosKaufAufRechnungAvailable(\Magento\Framework\Event\Observer $observer): bool
    {
        if ($this->pluginConfigurationValidator->isInvalid()) {
            return false;
        }

        if (!$this->isCartGrandTotalWithinConfiguredLimit($observer)) {
            return false;
        }

        return true;
    }

    private function isCartGrandTotalWithinConfiguredLimit(\Magento\Framework\Event\Observer $observer): bool
    {
        $event = $observer->getEvent();
        /** @var \Magento\Quote\Api\Data\CartInterface|null */
        $quote = $event->getDataByKey('quote');

        if ($quote instanceof \Magento\Quote\Api\Data\CartInterface) {
            /** @var \Magento\Quote\Api\Data\TotalsInterface */
            $totals = $this->cartTotalRepository->get($quote->getId());
            /** @var float|null */
            $grandTotal = $totals->getGrandTotal();
            /** @var float */
            $maximumOrderAmount = $this->pluginConfiguration->getMaximumOrderAmount();

            if (!is_null($grandTotal) && 0 < $maximumOrderAmount) {
                return $grandTotal <= $maximumOrderAmount;
            }
        }

        return true;
    }
}
