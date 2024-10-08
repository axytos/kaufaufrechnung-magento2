<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Configuration;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class PluginConfiguration extends AbstractHelper
{
    public function getApiHost(): string
    {
        return strval($this->getConfigValue(PluginConfigurationValueNames::API_HOST));
    }

    public function getApiKey(): string
    {
        return strval($this->getConfigValue(PluginConfigurationValueNames::API_KEY));
    }

    public function getClientSecret(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->getConfigValue(PluginConfigurationValueNames::CLIENT_SECRET);
    }

    public function getAfterCheckoutOrderState(): AfterCheckoutOrderState
    {
        $value = strval($this->getConfigValue(PluginConfigurationValueNames::ORDER_STATUS_AFTER_CHECKOUT));

        return new AfterCheckoutOrderState($value);
    }

    public function getCustomErrorMessage(): ?string
    {
        $errorMessage = $this->getConfigValue(PluginConfigurationValueNames::ERROR_MESSAGE);
        /** @phpstan-ignore-next-line */
        if (empty($errorMessage)) {
            return null;
        }

        return strval($errorMessage);
    }

    public function getMaximumOrderAmount(): float
    {
        // input field has type 'text'
        /** @var string */
        $configuredValue = $this->getConfigValue(PluginConfigurationValueNames::MAXIMUM_ORDER_AMOUNT);

        // convert to int, intval removes leading zeros and ignores trailing non-digit characters
        /** @var int */
        $intMaximumOrderAmount = intval($configuredValue);

        // convert to float
        /** @var float */
        $floatMaximumOrderAmount = floatval($intMaximumOrderAmount);

        if ($floatMaximumOrderAmount < 0.0) {
            return 0.0;
        }

        return $floatMaximumOrderAmount;
    }

    /**
     * @return mixed
     */
    private function getConfigValue(string $field)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE
        );
    }
}
