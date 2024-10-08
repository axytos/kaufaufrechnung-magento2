<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Model\Ui;

use Axytos\ECommerce\Clients\Checkout\CheckoutClientInterface;
use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Model\Constants;
use Magento\Checkout\Model\ConfigProviderInterface;

class CreditCheckInfoProvider implements ConfigProviderInterface
{
    /**
     * @var PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;
    /**
     * @var CheckoutClientInterface
     */
    private $checkoutClientInterface;
    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        CheckoutClientInterface $checkoutClientInterface,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->checkoutClientInterface = $checkoutClientInterface;
        $this->errorReportingClient = $errorReportingClient;
    }

    /**
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        try {
            return [
                'creditCheckInfo' => [
                    Constants::PAYMENT_METHOD_CODE => [
                        'infoText' => [
                            $this->getInfoText(),
                        ],
                    ],
                ],
            ];
        } catch (\Exception $exception) {
            $this->errorReportingClient->reportError($exception);
            throw $exception;
        }
    }

    private function getInfoText(): string
    {
        if ($this->pluginConfigurationValidator->isInvalid()) {
            return '';
        }

        return $this->checkoutClientInterface->getCreditCheckAgreementInfo();
    }
}
