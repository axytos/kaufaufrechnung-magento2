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
        } else {
            return strval($errorMessage);
        }
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
