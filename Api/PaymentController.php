<?php

namespace Axytos\KaufAufRechnung\Api;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceOrderPaymentUpdate;
use Axytos\ECommerce\Clients\Invoice\PaymentStatus;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Axytos\KaufAufRechnung\Core\OrderStateMachine;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Request as WebapiRequest;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class PaymentController implements PaymentControllerInterface
{
    private WebapiRequest $request;
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private PluginConfiguration $pluginConfiguration;
    private ErrorReportingClientInterface $errorReportingClient;
    private InvoiceClientInterface $invoiceClient;
    private OrderStateMachine $orderStateMachine;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        WebapiRequest $request,
        PluginConfigurationValidator $pluginConfigurationValidator,
        PluginConfiguration $pluginConfiguration,
        ErrorReportingClientInterface $errorReportingClient,
        InvoiceClientInterface $invoiceClient,
        OrderStateMachine $orderStateMachine,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->request = $request;
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->pluginConfiguration = $pluginConfiguration;
        $this->errorReportingClient = $errorReportingClient;
        $this->invoiceClient = $invoiceClient;
        $this->orderStateMachine = $orderStateMachine;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritDoc}
     *
     * Payment.
     *
     * @param string $paymentId
     */
    public function payment(string $paymentId)
    {
        try {
            if ($this->pluginConfigurationValidator->isInvalid()) {
                throw new WebapiException(__(), 0, WebapiException::HTTP_INTERNAL_ERROR);
            }

            if ($this->isClientSecretInvalid()) {
                throw new WebapiException(__(), 0, WebapiException::HTTP_UNAUTHORIZED);
            }

            $this->setOrderState($paymentId);
        } catch (WebapiException $webApiException) {
            throw $webApiException;
        } catch (Exception $exception) {
            $this->errorReportingClient->reportError($exception);

            throw new WebapiException(__(), 0, WebapiException::HTTP_INTERNAL_ERROR);
        }
    }

    private function isClientSecretInvalid(): bool
    {
        $configClientSecret = $this->pluginConfiguration->getClientSecret();

        $headerClientSecret = $this->request->getHeader('X-secret');

        return is_null($configClientSecret) || $configClientSecret !== $headerClientSecret;
    }

    private function setOrderState(string $paymentId): void
    {
        $invoiceOrderPaymentUpdate = $this->invoiceClient->getInvoiceOrderPaymentUpdate($paymentId);


        switch ($invoiceOrderPaymentUpdate->paymentStatus) {
            case PaymentStatus::PAID:
                $order = $this->getOrderByIncrementId($invoiceOrderPaymentUpdate->orderId);
                $this->orderStateMachine->setComplete($order);
                return;
            case PaymentStatus::OVERPAID:
                $order = $this->getOrderByIncrementId($invoiceOrderPaymentUpdate->orderId);
                $this->orderStateMachine->setComplete($order);
                return;
        }
    }

    private function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->create();

        $orders = $this->orderRepository->getList($criteria)->getItems();
        /**
         * @phpstan-ignore-next-line because there will bet at least on order item
         */
        return current($orders);
    }
}
