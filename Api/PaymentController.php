<?php

namespace Axytos\KaufAufRechnung\Api;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\PaymentStatus;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Request as WebapiRequest;

/**
 * Call: http://localhost/rest/V1/axytos/KaufAufRechnung/payment/123
 * Or Call: http://localhost/rest/[store_code]/V1/axytos/KaufAufRechnung/payment/123 -- CURRENTLY NOT SUPPORTED.
 *
 * See: https://www.mageplaza.com/devdocs/magento-2-create-api/
 */
class PaymentController implements PaymentControllerInterface
{
    /**
     * @var WebapiRequest
     */
    private $request;
    /**
     * @var PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;
    /**
     * @var PluginConfiguration
     */
    private $pluginConfiguration;
    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;
    /**
     * @var InvoiceClientInterface
     */
    private $invoiceClient;
    /**
     * @var OrderSyncRepositoryInterface
     */
    private $orderSyncRepository;

    public function __construct(
        WebapiRequest $request,
        PluginConfigurationValidator $pluginConfigurationValidator,
        PluginConfiguration $pluginConfiguration,
        ErrorReportingClientInterface $errorReportingClient,
        InvoiceClientInterface $invoiceClient,
        OrderSyncRepositoryInterface $orderSyncRepository
    ) {
        $this->request = $request;
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->pluginConfiguration = $pluginConfiguration;
        $this->errorReportingClient = $errorReportingClient;
        $this->invoiceClient = $invoiceClient;
        $this->orderSyncRepository = $orderSyncRepository;
    }

    /**
     * {@inheritDoc}
     *
     * Payment.
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
            // rethrow WebapiException
            // because mangento sets http status codes via WebapiExceptions
            throw $webApiException;
        } catch (\Exception $exception) {
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
        $pluginOrder = $this->orderSyncRepository->getOrderByOrderNumber($invoiceOrderPaymentUpdate->orderId);

        if (is_null($pluginOrder)) {
            return;
        }

        switch ($invoiceOrderPaymentUpdate->paymentStatus) {
            case PaymentStatus::PAID:
                $pluginOrder->saveHasBeenPaid();

                return;
            case PaymentStatus::OVERPAID:
                $pluginOrder->saveHasBeenPaid();

                return;
        }
    }
}
