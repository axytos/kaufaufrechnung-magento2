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
