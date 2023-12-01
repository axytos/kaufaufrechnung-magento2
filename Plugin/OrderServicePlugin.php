<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Plugin;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Exception;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory;
use Axytos\KaufAufRechnung\Exception\DisablePaymentMethodException;
use Axytos\KaufAufRechnung\Model\Constants;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Axytos\KaufAufRechnung\Core\OrderCheckProcessStateMachine;
use Axytos\KaufAufRechnung\Core\OrderStateMachine;
use Magento\Framework\Phrase;

class OrderServicePlugin
{
    /**
     * @var \Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;
    /**
     * @var \Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface
     */
    private $invoiceClient;
    /**
     * @var \Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory
     */
    private $invoiceOrderContextFactory;
    /**
     * @var \Axytos\KaufAufRechnung\Core\OrderCheckProcessStateMachine
     */
    private $orderCheckProcessStateMachine;
    /**
     * @var \Axytos\KaufAufRechnung\Core\OrderStateMachine
     */
    private $orderStateMachine;
    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface
     */
    private $errorReportingClient;
    /**
     * @var \Axytos\KaufAufRechnung\Configuration\PluginConfiguration
     */
    private $pluginConfiguration;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        InvoiceClientInterface $invoiceClient,
        InvoiceOrderContextFactory $invoiceOrderContextFactory,
        OrderCheckProcessStateMachine $orderCheckProcessStateMachine,
        OrderStateMachine $orderStateMachine,
        ErrorReportingClientInterface $errorReportingClient,
        PluginConfiguration $pluginConfiguration
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->invoiceClient = $invoiceClient;
        $this->invoiceOrderContextFactory = $invoiceOrderContextFactory;
        $this->orderCheckProcessStateMachine = $orderCheckProcessStateMachine;
        $this->orderStateMachine = $orderStateMachine;
        $this->errorReportingClient = $errorReportingClient;
        $this->pluginConfiguration = $pluginConfiguration;
    }

    public function afterPlace(OrderService $subject, OrderInterface $order): OrderInterface
    {
        try {
            if (is_null($order->getPayment()) || $order->getPayment()->getMethod() !== Constants::PAYMENT_METHOD_CODE) {
                return $order;
            }

            if ($this->pluginConfigurationValidator->isInvalid()) {
                return $order;
            }

            $this->orderCheckProcessStateMachine->setUnchecked($order);

            $invoiceOrderContext = $this->invoiceOrderContextFactory->getInvoiceOrderContext($order);

            $shopAction = $this->invoiceClient->precheck($invoiceOrderContext);

            $this->orderCheckProcessStateMachine->setChecked($order);

            if ($shopAction === ShopActions::CHANGE_PAYMENT_METHOD) {
                $this->orderStateMachine->setCanceled($order);

                $errorMessage = $this->pluginConfiguration->getCustomErrorMessage();
                if (is_null($errorMessage)) {
                    $errorPhrase = __("PAYMENT_REJECTED_MESSAGE");
                } else {
                    $errorPhrase = new Phrase($errorMessage);
                }

                throw new DisablePaymentMethodException($errorPhrase, Constants::PAYMENT_METHOD_CODE);
            }

            $this->invoiceClient->confirmOrder($invoiceOrderContext);
            $this->orderCheckProcessStateMachine->setConfirmed($order);
            $this->orderStateMachine->setConfiguredAfterCheckoutOrderStatus($order);

            return $order;
        } catch (LocalizedException $exception) {
            $this->orderCheckProcessStateMachine->setFailed($order);
            $this->orderStateMachine->setTechnicalError($order);

            $couldNotSaveException = new CouldNotSaveException(__($exception->getMessage()));
            $couldNotSaveException->addException($exception);
            throw $couldNotSaveException;
        } catch (Exception $e) {
            $this->orderCheckProcessStateMachine->setFailed($order);
            $this->orderStateMachine->setTechnicalError($order);

            $this->errorReportingClient->reportError($e);
            throw $e;
        }
    }
}
