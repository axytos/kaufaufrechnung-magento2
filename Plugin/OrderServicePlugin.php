<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Plugin;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Exception;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory;
use Axytos\KaufAufRechnung\Exception\DisablePaymentMethodException;
use Axytos\KaufAufRechnung\Model\Constants;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Axytos\KaufAufRechnung\Core\OrderCheckProcessStateMachine;
use Axytos\KaufAufRechnung\Core\OrderStateMachine;

class OrderServicePlugin
{
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private InvoiceClientInterface $invoiceClient;
    private InvoiceOrderContextFactory $invoiceOrderContextFactory;
    private OrderCheckProcessStateMachine $orderCheckProcessStateMachine;
    private OrderStateMachine $orderStateMachine;
    private ErrorReportingClientInterface $errorReportingClient;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        InvoiceClientInterface $invoiceClient,
        InvoiceOrderContextFactory $invoiceOrderContextFactory,
        OrderCheckProcessStateMachine $orderCheckProcessStateMachine,
        OrderStateMachine $orderStateMachine,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->invoiceClient = $invoiceClient;
        $this->invoiceOrderContextFactory = $invoiceOrderContextFactory;
        $this->orderCheckProcessStateMachine = $orderCheckProcessStateMachine;
        $this->orderStateMachine = $orderStateMachine;
        $this->errorReportingClient = $errorReportingClient;
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

            $this->orderStateMachine->setPaymentReview($order);
            $this->orderCheckProcessStateMachine->setUnchecked($order);

            $invoiceOrderContext = $this->invoiceOrderContextFactory->getInvoiceOrderContext($order);

            $shopAction = $this->invoiceClient->precheck($invoiceOrderContext);

            $this->orderCheckProcessStateMachine->setChecked($order);

            if ($shopAction === ShopActions::CHANGE_PAYMENT_METHOD) {
                $this->orderStateMachine->setCanceled($order);
                throw new DisablePaymentMethodException(__("PAYMENT_REJECTED_MESSAGE"), Constants::PAYMENT_METHOD_CODE);
            }

            $this->invoiceClient->confirmOrder($invoiceOrderContext);
            $this->orderCheckProcessStateMachine->setConfirmed($order);
            $this->orderStateMachine->setPendingPayment($order);

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
