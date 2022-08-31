<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Axytos\ECommerce\Clients\Checkout\CheckoutClientInterface;
use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Model\Constants;
use Exception;

class CreditCheckInfoProvider implements ConfigProviderInterface
{
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private CheckoutClientInterface $checkoutClientInterface;
    private ErrorReportingClientInterface $errorReportingClient;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        CheckoutClientInterface $checkoutClientInterface,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->checkoutClientInterface = $checkoutClientInterface;
        $this->errorReportingClient = $errorReportingClient;
    }

    public function getConfig(): array
    {
        try {
            return [
                'creditCheckInfo' => [
                    Constants::PAYMENT_METHOD_CODE => [
                        'infoText' => [
                            $this->getInfoText()
                        ]
                    ]
                ]
            ];
        } catch (Exception $exception) {
            $this->errorReportingClient->reportError($exception);
            throw $exception;
        }
    }

    private function getInfoText(): string
    {
        if ($this->pluginConfigurationValidator->isInvalid()) {
            return "";
        }

        return $this->checkoutClientInterface->getCreditCheckAgreementInfo();
    }
}
